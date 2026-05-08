<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentFee;
use App\Models\FeeSchedule;
use App\Models\PendingReceipt;
use App\Models\Grade;
use App\Http\Resources\PendingReceiptResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class FeeSlipController extends Controller
{
    /**
     * Generate fee slips for ALL students in a grade (bulk)
     * Creates PendingReceipt records with transaction_id
     */
    public function generatebulk(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'month' => 'required|string',
            'academic_year' => 'required|string|size:9',
            'due_date' => 'nullable|date',
            'force_regenerate' => 'nullable|boolean',
        ]);

        $forceRegenerate = $request->input('force_regenerate', true);

        // Verify grade belongs to user's institute
        $grade = Grade::where('institute_id', $user->institute_id)
            ->findOrFail($validated['grade_id']);

        // Get students in grade
        $students = Student::whereHas('section', function ($q) use ($validated) {
            $q->where('grade_id', $validated['grade_id']);
        })->where('status', 'active')->get();

        if ($students->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active students found in this grade',
            ], 404);
        }

        // Check if student fees exist for this grade/month/year
        $studentIds = $students->pluck('id');
        $existingFeesCount = StudentFee::whereIn('student_id', $studentIds)
            ->where('academic_year', $validated['academic_year'])
            ->where('month', $validated['month'])
            ->count();

        // If no fees exist OR force regenerate is true, create them from schedules
        if ($existingFeesCount == 0 || $forceRegenerate) {
            // Delete existing fees for this grade/month/year
            if ($forceRegenerate && $existingFeesCount > 0) {
                StudentFee::whereIn('student_id', $studentIds)
                    ->where('academic_year', $validated['academic_year'])
                    ->delete();
                
                // Delete existing pending slips for this grade/month
                PendingReceipt::whereHas('student', function ($q) use ($validated) {
                    $q->whereHas('section', function ($q2) use ($validated) {
                        $q2->where('grade_id', $validated['grade_id']);
                    });
                })->where('status', 'pending')
                  ->delete();
            }

            // Create fees from current fee schedules
            $yearParts = explode('-', $validated['academic_year']);
            $startDate = $yearParts[0] . '-07-01';
            $endDate = $yearParts[1] . '-06-30';

            $schedules = FeeSchedule::where('institute_id', $user->institute_id)
                ->where('grade_id', $validated['grade_id'])
                ->where('is_active', true)
                ->where('applicable_from', '>=', $startDate)
                ->where('applicable_from', '<=', $endDate)
                ->get();

            if ($schedules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No fee schedules found. Please set up fee structure first.',
                ], 404);
            }

            // Create fees ONLY for the requested month (not entire year)
            $requestedMonth = $validated['month'];
            $feesCreated = 0;
            
            foreach ($students as $student) {
                foreach ($schedules as $schedule) {
                    // Check fee category
                    if ($schedule->fee_category_id && $schedule->fee_category_id != $student->fee_category_id) {
                        continue;
                    }

                    // Only create fee for one-time fees OR the requested month for monthly
                    if ($schedule->frequency === 'one_time') {
                        $month = '';
                    } else {
                        $month = $requestedMonth;
                    }

                    // Check if exists
                    $exists = StudentFee::where('student_id', $student->id)
                        ->where('fee_type_id', $schedule->fee_type_id)
                        ->where('month', $month)
                        ->where('academic_year', $validated['academic_year'])
                        ->exists();

                    if (!$exists) {
                        try {
                            StudentFee::create([
                                'student_id' => $student->id,
                                'fee_type_id' => $schedule->fee_type_id,
                                'fee_schedule_id' => $schedule->id,
                                'amount' => $schedule->amount,
                                'month' => $month,
                                'academic_year' => $validated['academic_year'],
                                'effective_from' => $student->admission_date ?? $student->created_at,
                                'status' => 'pending',
                            ]);
                            $feesCreated++;
                        } catch (\Illuminate\Database\QueryException $e) {
                            if ($e->getCode() == 23000) {
                                continue;
                            }
                            throw $e;
                        }
                    }
                }
            }

            $regenMessage = $existingFeesCount > 0 
                ? "Updated fee structure and regenerated. " 
                : "Created {$feesCreated} student fees. ";
        }

        // Now generate slips from the fees
        $generated = 0;
        $skipped = 0;
        $slips = [];

        foreach ($students as $student) {
            // Skip if a receipt already exists for this month/year (paid or pending)
            $existingReceipt = PendingReceipt::where('student_id', $student->id)
                ->where('month', $validated['month'])
                ->where('academic_year', $validated['academic_year'])
                ->first();

            if ($existingReceipt) {
                $skipped++;
                continue;
            }

            $result = $this->generateSlipForStudent(
                $student,
                $validated['month'],
                $validated['academic_year'],
                $validated['due_date'] ?? null
            );

            if ($result) {
                $slips[] = $result;
                $generated++;
            } else {
                $skipped++;
            }
        }

        $message = $regenMessage ?? '';
        $message .= "Generated {$generated} fee slips, skipped {$skipped}";

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'slips' => $slips,
                'summary' => [
                    'total_students' => $students->count(),
                    'generated' => $generated,
                    'skipped' => $skipped,
                ],
            ],
        ]);
    }

    /**
     * Generate fee slip for single student
     */
    public function generateSingle(Request $request, int $studentId): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'month' => 'required|string',
            'academic_year' => 'required|string|size:9',
            'due_date' => 'nullable|date',
        ]);

        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with('section.grade', 'feeCategory')->findOrFail($studentId);

        $result = $this->generateSlipForStudent(
            $student,
            $validated['month'],
            $validated['academic_year'],
            $validated['due_date'] ?? null
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'No pending fees found for this student',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fee slip generated successfully',
            'data' => $result,
        ]);
    }

    /**
     * Core logic: Generate fee slip for a student
     */
    private function generateSlipForStudent(Student $student, string $month, string $academicYear, ?string $dueDate): ?array
    {
        // Get pending fees for student
        $feesQuery = StudentFee::with(['feeType', 'feeSchedule'])
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->where('academic_year', $academicYear);

        // Filter by month
        if (strtolower($month) !== 'all') {
            $feesQuery->where(function ($q) use ($month) {
                $q->where('month', $month)           // Monthly fees for this month
                   ->orWhere(function ($q2) {       // One-time fees (empty month)
                       $q2->where('month', '')
                           ->orWhereNull('month');
                   });
            });
        }

        $fees = $feesQuery->get();

        if ($fees->isEmpty()) {
            return null; // No pending fees
        }

        // Build fee breakdown
        $breakdown = [];
        $totalAmount = 0;

        foreach ($fees as $fee) {
            $amount = (float) $fee->amount;
            $totalAmount += $amount;
            $breakdown[] = [
                'student_fee_id' => $fee->id,
                'fee_type' => $fee->feeType?->name ?? 'Unknown',
                'fee_type_code' => $fee->feeType?->code ?? '',
                'amount' => $amount,
                'month' => $fee->month,
                'academic_year' => $fee->academic_year,
            ];
        }

        // Calculate due date (default: 7th of current month)
        if (!$dueDate) {
            $dueDate = Carbon::now()->day(7)->toDateString();
        }

        // Generate transaction_id
        $transactionId = PendingReceipt::generateTransactionId();

        // Create PendingReceipt
        $receipt = PendingReceipt::create([
            'institute_id' => $student->institute_id,
            'student_id' => $student->id,
            'transaction_id' => $transactionId,
            'academic_year' => $academicYear,
            'month' => $month,
            'due_date' => $dueDate,
            'amount' => $totalAmount,
            'fee_breakdown' => json_encode($breakdown),
            'status' => 'pending',
        ]);

        return [
            'preview' => false,
            'transaction_id' => $transactionId,
            'due_date' => $dueDate,
            'amount' => $totalAmount,
            'fee_breakdown' => $breakdown,
            'student' => [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'registration_number' => $student->registration_number,
                'grade' => $student->section?->grade?->name ?? 'N/A',
                'section' => $student->section?->name ?? 'N/A',
            ],
            'pending_receipt_id' => $receipt->id,
        ];
    }

    /**
     * View all generated fee slips (pending receipts)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $perPage = $request->input('per_page', 15);
        $gradeId = $request->input('grade_id');
        $sectionId = $request->input('section_id');
        $month = $request->input('month');
        $academicYear = $request->input('academic_year');
        $status = $request->input('status');
        $transactionId = $request->input('transaction_id');
        $studentName = $request->input('student_name');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = \App\Models\PendingReceipt::whereHas('student', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            })->with(['student.section.grade']);

        if ($gradeId) {
            $query->whereHas('student.section', function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            });
        }

        if ($sectionId) {
            $query->whereHas('student.section', function ($q) use ($sectionId) {
                $q->where('id', $sectionId);
            });
        }

        if ($month) {
            $query->where('month', $month);
        }

        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($transactionId) {
            $query->where('transaction_id', 'like', "%{$transactionId}%");
        }

        if ($studentName) {
            $query->whereHas('student', function ($q) use ($studentName) {
                $q->where('first_name', 'like', "%{$studentName}%")
                  ->orWhere('last_name', 'like', "%{$studentName}%");
            });
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $paginate = $request->input('paginate', true);

        if ($paginate === false || $paginate === 'false') {
            $receipts = $query->orderBy('created_at', 'desc')->get();
            return response()->json([
                'success' => true,
                'data' => PendingReceiptResource::collection($receipts),
                'meta' => [
                    'total' => $receipts->count(),
                ],
            ]);
        }

        $receipts = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => PendingReceiptResource::collection($receipts),
            'meta' => [
                'current_page' => $receipts->currentPage(),
                'last_page' => $receipts->lastPage(),
                'per_page' => $receipts->perPage(),
                'total' => $receipts->total(),
            ],
        ]);
    }

    /**
     * View a specific fee slip
     */
    public function view(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $receipt = \App\Models\PendingReceipt::whereHas('student', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            })->with(['student.section.grade'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new PendingReceiptResource($receipt),
        ]);
    }

    /**
     * View multiple fee slips with full details
     */
    public function bulkView(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No IDs provided',
            ], 422);
        }

        $receipts = \App\Models\PendingReceipt::whereHas('student', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            })->with(['student.section.grade'])
            ->whereIn('id', $ids)
            ->get();

        return response()->json([
            'success' => true,
            'data' => PendingReceiptResource::collection($receipts),
            'meta' => ['total' => $receipts->count()],
        ]);
    }

    /**
     * Record payment for a fee slip
     */
    public function recordPayment(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,card,upi',
            'bank_reference' => 'nullable|string|max:255',
        ]);

        $receipt = \App\Models\PendingReceipt::whereHas('student', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            })->with(['student.section.grade'])->findOrFail($id);

        if ($receipt->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This receipt is already paid',
            ], 400);
        }

        if ((float) $validated['amount'] !== (float) $receipt->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Amount mismatch. Expected: ' . $receipt->amount,
            ], 422);
        }

        // Update PendingReceipt
        $receipt->update([
            'status' => 'paid',
            'paid_at' => $validated['payment_date'],
            'paid_by' => $user->id,
            'payment_method' => $validated['payment_method'],
            'bank_reference' => $validated['bank_reference'] ?? null,
        ]);

        // Also mark related StudentFee records as paid
        $breakdown = is_string($receipt->fee_breakdown) 
            ? json_decode($receipt->fee_breakdown, true) 
            : $receipt->fee_breakdown;
        
        if (!empty($breakdown)) {
            $feeIds = collect($breakdown)->pluck('student_fee_id')->filter();
            \App\Models\StudentFee::whereIn('id', $feeIds)->update(['status' => 'paid']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'data' => new PendingReceiptResource($receipt->fresh()),
        ]);
    }

    /**
     * Delete a fee slip so it can be regenerated
     */
    public function delete(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $receipt = \App\Models\PendingReceipt::whereHas('student', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            })->findOrFail($id);

        if ($receipt->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a paid receipt. Please reverse payment first.',
            ], 400);
        }

        $receipt->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fee slip deleted. You can now regenerate new slips.',
        ]);
    }

    /**
     * Bulk delete fee slips (to regenerate)
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:pending_receipts,id',
        ]);

        // Get slips that belong to user's institute and are not paid
        $idsToDelete = \App\Models\PendingReceipt::whereHas('student', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            })->whereIn('id', $validated['ids'])
            ->where('status', '!=', 'paid')
            ->pluck('id')
            ->toArray();

        $paidCount = count($validated['ids']) - count($idsToDelete);
        
        $deleted = \App\Models\PendingReceipt::whereIn('id', $idsToDelete)->delete();

        $message = "{$deleted} fee slip(s) deleted.";
        if ($paidCount > 0) {
            $message .= " {$paidCount} paid slips were not deleted.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => ['deleted' => $deleted, 'skipped' => $paidCount],
        ]);
    }

    /**
     * Delete all fee slips for a grade/month (to regenerate all)
     */
    public function deleteAll(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $gradeId = $request->input('grade_id');

        $query = \App\Models\PendingReceipt::whereHas('student', function ($q) use ($user) {
            $q->where('institute_id', $user->institute_id);
        })->where('status', '!=', 'paid');

        if ($gradeId) {
            $query->whereHas('student.section', function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            });
        }

        $deleted = $query->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} fee slip(s) deleted for this grade/month.",
            'data' => ['deleted' => $deleted],
        ]);
    }
}
