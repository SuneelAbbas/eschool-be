<?php

namespace App\Http\Controllers;

use App\Models\PendingReceipt;
use App\Models\StudentFee;
use App\Models\GradeFee;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PendingReceiptController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $user = $request->user();
        $gradeId = $request->input('grade_id');
        $month = $request->input('month');
        $academicYear = $request->input('academic_year');
        $dueDate = $request->input('due_date');
        $extendedDate = $request->input('extended_due_date');

        if (!$gradeId) {
            return response()->json([
                'success' => false,
                'message' => 'Grade is required',
            ], 422);
        }

        // Get students for the grade
        $students = Student::whereHas('section', function ($q) use ($gradeId) {
            $q->where('grade_id', $gradeId);
        })->where('institute_id', $user->institute_id)->get();

        if ($students->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No students found for this grade',
            ], 422);
        }

        // Calculate due date
        $dueDateValue = $extendedDate 
            ? Carbon::parse($extendedDate) 
            : ($dueDate ? Carbon::parse($dueDate) : PendingReceipt::calculateDueDate());

        $generated = 0;
        $skipped = 0;

        foreach ($students as $student) {
            // Check if pending receipt already exists for this month/year
            $existingPending = PendingReceipt::where('student_id', $student->id)
                ->where('status', 'pending')
                ->when($month, fn($q) => $q->whereRaw('MONTH(due_date) = ?', [date('m')]))
                ->when($academicYear, fn($q) => $q->whereRaw('YEAR(due_date) = ?', [substr($academicYear, 0, 4)]))
                ->exists();

            if ($existingPending) {
                $skipped++;
                continue;
            }

            // Get student's pending fees
            $studentFees = StudentFee::where('student_id', $student->id)
                ->where('status', 'pending')
                ->when($academicYear, fn($q) => $q->where('academic_year', $academicYear))
                ->when($month, fn($q) => $q->where('month', $month))
                ->with('feeType')
                ->get();

            if ($studentFees->isEmpty()) {
                $skipped++;
                continue;
            }

            // Calculate total amount and build breakdown
            $totalAmount = 0;
            $breakdown = [];

            foreach ($studentFees as $sf) {
                $feeName = $sf->feeType?->name ?? 'Unknown Fee';
                $amount = (float) $sf->amount;
                $totalAmount += $amount;
                $breakdown[] = [
                    'fee_type' => $feeName,
                    'amount' => $amount,
                    'month' => $sf->month,
                ];
            }

            // Create pending receipt
            PendingReceipt::create([
                'student_id' => $student->id,
                'transaction_id' => PendingReceipt::generateTransactionId(),
                'amount' => $totalAmount,
                'due_date' => $dueDateValue->toDateString(),
                'status' => 'pending',
                'fee_breakdown' => json_encode($breakdown),
            ]);

            $generated++;
        }

        return response()->json([
            'success' => true,
            'message' => "Generated {$generated} pending receipts ({$skipped} skipped - already exist)",
            'data' => [
                'generated' => $generated,
                'skipped' => $skipped,
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $gradeId = $request->input('grade_id');
        $month = $request->input('month');
        $status = $request->input('status', 'pending');
        $search = $request->input('search');

        $query = PendingReceipt::with(['student', 'student.section.grade', 'paidByUser'])
            ->when($gradeId, fn($q) => $q->whereHas('student.section.grade', fn($q) => $q->where('id', $gradeId)))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where('transaction_id', 'like', "%{$search}%")
                ->orWhereHas('student', fn($q) => $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%")));

        $pendingReceipts = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $pendingReceipts->items(),
            'meta' => [
                'current_page' => $pendingReceipts->currentPage(),
                'last_page' => $pendingReceipts->lastPage(),
                'per_page' => $pendingReceipts->perPage(),
                'total' => $pendingReceipts->total(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $user = request()->user();
        
        $pendingReceipt = PendingReceipt::with(['student', 'student.section.grade', 'paidByUser'])
            ->find((int) $id);

        if (!$pendingReceipt) {
            return response()->json([
                'success' => false,
                'message' => 'Pending receipt not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pendingReceipt->id,
                'transaction_id' => $pendingReceipt->transaction_id,
                'due_date' => $pendingReceipt->due_date,
                'amount' => (float) $pendingReceipt->amount,
                'status' => $pendingReceipt->status,
                'fee_breakdown' => json_decode($pendingReceipt->fee_breakdown),
                'paid_at' => $pendingReceipt->paid_at,
                'student' => [
                    'id' => $pendingReceipt->student->id,
                    'name' => $pendingReceipt->student->first_name . ' ' . $pendingReceipt->student->last_name,
                    'registration_number' => $pendingReceipt->student->registration_number,
                    'grade' => $pendingReceipt->student->section->grade->name ?? 'N/A',
                    'section' => $pendingReceipt->student->section->name ?? 'N/A',
                    'father_name' => $pendingReceipt->student->parents_name ?? 'N/A',
                ],
                'institute' => [
                    'name' => $pendingReceipt->student->institute->name,
                    'logo' => $pendingReceipt->student->institute->logo,
                    'address' => $pendingReceipt->student->institute->address,
                ],
            ],
        ]);
    }

public function recordPayment(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $pendingReceipt = PendingReceipt::with('student')->find((int) $id);

        if (!$pendingReceipt) {
            return response()->json([
                'success' => false,
                'message' => 'Pending receipt not found',
            ], 404);
        }

        if ($pendingReceipt->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This receipt is already paid',
            ], 422);
        }

        // Verify transaction ID if provided
        $transactionId = $request->input('transaction_id');
        if ($transactionId && $transactionId !== $pendingReceipt->transaction_id) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction ID mismatch',
            ], 422);
        }

        $paymentMethod = $request->input('payment_method', 'cash');
        $bankReference = $request->input('bank_reference');

        $pendingReceipt->markAsPaid($user, [
            'payment_method' => $paymentMethod,
            'bank_reference' => $bankReference,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'data' => [
                'receipt_id' => $pendingReceipt->id,
                'transaction_id' => $pendingReceipt->transaction_id,
                'paid_at' => $pendingReceipt->fresh()->paid_at,
            ],
        ]);
    }

    public function searchByTransaction(Request $request): JsonResponse
    {
        $user = $request->user();
        $transactionId = $request->input('transaction_id');

        if (!$transactionId) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction ID is required',
            ], 422);
        }

        $pendingReceipt = PendingReceipt::with(['student', 'student.section.grade'])
            ->where('transaction_id', 'like', "%{$transactionId}%")
            ->where('status', 'pending')
            ->whereHas('student', fn($q) => $q->where('institute_id', $user->institute_id))
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pendingReceipt,
        ]);
    }

    public function getReceiptsForPrint(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $gradeId = $request->input('grade_id');
            $status = $request->input('status', 'pending');

            $receipts = PendingReceipt::with(['student', 'student.section.grade', 'paidByUser'])
                ->where('status', $status)
                ->when($gradeId, fn($q) => $q->whereHas('student.section', fn($q) => $q->where('grade_id', $gradeId)))
                ->orderBy('transaction_id')
                ->get();

            $formattedReceipts = $receipts->map(function ($receipt) {
                return [
                    'id' => $receipt->id,
                    'transaction_id' => $receipt->transaction_id,
                    'due_date' => $receipt->due_date,
                    'amount' => (float) $receipt->amount,
                    'status' => $receipt->status,
                    'fee_breakdown' => json_decode($receipt->fee_breakdown),
                    'student' => [
                        'id' => $receipt->student->id,
                        'name' => $receipt->student->first_name . ' ' . $receipt->student->last_name,
                        'registration_number' => $receipt->student->registration_number,
                        'grade' => $receipt->student->section->grade->name ?? 'N/A',
                        'section' => $receipt->student->section->name ?? 'N/A',
                        'father_name' => $receipt->student->parents_name ?? 'N/A',
                    ],
                    'institute' => [
                        'name' => $receipt->student->institute->name,
                        'logo' => $receipt->student->institute->logo,
                        'address' => $receipt->student->institute->address,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedReceipts,
                'meta' => [
                    'total' => $receipts->count(),
                    'status' => $status,
                ],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('getReceiptsForPrint error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}