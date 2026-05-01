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

        // Convert month name to number if needed (e.g., "April" → 4)
        $monthNumber = null;
        if ($month) {
            if (is_numeric($month)) {
                $monthNumber = (int) $month;
            } else {
                $monthNumber = (int) date('n', strtotime($month)); // "April" → 4
            }
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
            // Delete any existing PENDING receipt for this student with the same due_date
            // This allows regenerating/overwriting receipts
            PendingReceipt::where('student_id', $student->id)
                ->where('status', 'pending')
                ->where('due_date', $dueDateValue->toDateString())
                ->delete();

            // Get student's pending fees
            $studentFeesQuery = StudentFee::where('student_id', $student->id)
                ->where('status', 'pending');
            
            // Academic year matching - supports "2026" or "2025-2026" format
            if ($academicYear) {
                $studentFeesQuery->where(function ($q) use ($academicYear) {
                    $q->where('academic_year', $academicYear)
                      ->orWhere('academic_year', 'like', $academicYear . '-%')
                      ->orWhere('academic_year', 'like', '%-' . $academicYear);
                });
            }
            
            // Handle month filtering - database stores month as name ("May") or number ("5")
            // If no month specified, get ALL pending fees (regardless of month)
            if ($month) {
                // Try both formats: original input and converted number
                $studentFeesQuery->where(function ($q) use ($month, $monthNumber) {
                    $q->where('month', $month)  // Match "May"
                       ->orWhere('month', (string)$monthNumber);  // Also match "5"
                });
            }
            
            $studentFees = $studentFeesQuery->with('feeType')->get();

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
            'message' => "Generated {$generated} pending receipts ({$skipped} skipped - no pending fees)",
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

    /**
     * Preview fee slip for a student WITHOUT generating a receipt
     * Useful for viewing what fees a student owes before/without generating
     */
    public function previewFeeSlip(Request $request, int $studentId): JsonResponse
    {
        $user = $request->user();
        $month = $request->input('month');
        $academicYear = $request->input('academic_year');

        // Verify student belongs to user's institute
        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with('section.grade', 'institute')->find($studentId);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        // Get pending fees
        $feesQuery = StudentFee::where('student_id', $studentId)
            ->where('status', 'pending')
            ->with('feeType');

        if ($academicYear) {
            $feesQuery->where('academic_year', $academicYear);
        }

        if ($month) {
            $monthNumber = is_numeric($month) ? $month : date('n', strtotime($month));
            $feesQuery->where('month', (string)$monthNumber);
        }

        $fees = $feesQuery->get();

        if ($fees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No pending fees found for this student',
            ], 404);
        }

        // Build fee breakdown and total
        $totalAmount = 0;
        $breakdown = [];

        foreach ($fees as $fee) {
            $amount = (float) $fee->amount;
            $totalAmount += $amount;
            $breakdown[] = [
                'fee_type' => $fee->feeType?->name ?? 'Unknown Fee',
                'amount' => $amount,
                'month' => $fee->month,
                'academic_year' => $fee->academic_year,
            ];
        }

        // Generate a preview transaction ID (not saved to DB)
        $previewTransactionId = 'PREVIEW-' . now()->format('Ymd') . '-' . str_pad($studentId, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'success' => true,
            'data' => [
                'preview' => true,
                'transaction_id' => $previewTransactionId,
                'due_date' => PendingReceipt::calculateDueDate()->toDateString(),
                'amount' => $totalAmount,
                'fee_breakdown' => $breakdown,
                'student' => [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'registration_number' => $student->registration_number,
                    'grade' => $student->section->grade->name ?? 'N/A',
                    'section' => $student->section->name ?? 'N/A',
                    'father_name' => $student->parents_name ?? 'N/A',
                ],
                'institute' => [
                    'name' => $student->institute->name,
                    'logo' => $student->institute->logo,
                    'address' => $student->institute->address,
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