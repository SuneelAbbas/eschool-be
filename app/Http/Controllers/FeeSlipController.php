<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentFee;
use App\Models\FeeSchedule;
use App\Models\PendingReceipt;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class FeeSlipController extends Controller
{
    /**
     * Generate fee slips for ALL students in a grade (bulk)
     * Creates PendingReceipt records with transaction_id
     */
    public function generateBulk(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'month' => 'required|string', // "May", "June", or "All"
            'academic_year' => 'required|string|size:9',
            'due_date' => 'nullable|date',
        ]);

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

        $generated = 0;
        $skipped = 0;
        $slips = [];

        foreach ($students as $student) {
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

        return response()->json([
            'success' => true,
            'message' => "Generated {$generated} fee slips, skipped {$skipped}",
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
        $feesQuery = StudentFee::where('student_id', $student->id)
            ->where('status', 'pending')
            ->where('academic_year', $academicYear)
            ->with('feeType');

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

        // Calculate due date
        if (!$dueDate) {
            $dueDate = Carbon::now()->addMonth()->day(7)->toDateString();
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
            'fee_breakdown' => $breakdown,
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
}
