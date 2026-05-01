<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentFee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FeeSlipController extends Controller
{
    /**
     * Preview fee slips for ALL students in a grade (bulk preview)
     * Does NOT save to database - just a preview
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $gradeId = $request->input('grade_id');
        $month = $request->input('month'); // Only accepts month name: "May", "February", etc.
        $academicYear = $request->input('academic_year', $user->institute?->current_academic_year ?? $this->calculateAcademicYear());
        
        if (!$gradeId) {
            return response()->json([
                'success' => false,
                'message' => 'grade_id is required',
            ], 422);
        }

        // Get students in the grade
        $students = Student::whereHas('section', function ($q) use ($gradeId) {
            $q->where('grade_id', $gradeId);
        })->when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with(['section.grade', 'institute'])->get();

        if ($students->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No students found for this grade',
            ], 404);
        }

        $feeSlips = [];
        $totalAmount = 0;
        $generatedCount = 0;
        $skippedCount = 0;

        foreach ($students as $student) {
            // Get pending fees for this student
            $feesQuery = StudentFee::where('student_id', $student->id)
                ->where('status', 'pending')
                ->with('feeType');

            if ($academicYear) {
                $feesQuery->where(function ($q) use ($academicYear) {
                    $q->where('academic_year', $academicYear)
                      ->orWhere('academic_year', 'like', $academicYear . '-%')
                      ->orWhere('academic_year', 'like', '%-' . $academicYear);
                });
            }

            // Filter by month name, but ALWAYS include one-time fees (empty month)
            if ($month && strtolower($month) !== 'all') {
                $feesQuery->where(function ($q) use ($month) {
                    $q->where('month', $month)           // Monthly fees for this month
                       ->orWhere('month', '')              // One-time fees (empty)
                       ->orWhereNull('month');          // One-time fees (null)
                });
            }

            $fees = $feesQuery->get();

            if ($fees->isEmpty()) {
                $skippedCount++;
                continue;
            }

            // Build fee breakdown and total for this student
            $studentTotal = 0;
            $breakdown = [];

            foreach ($fees as $fee) {
                $amount = (float) $fee->amount;
                $studentTotal += $amount;
                $breakdown[] = [
                    'fee_type' => $fee->feeType?->name ?? 'Unknown Fee',
                    'fee_type_code' => $fee->feeType?->code ?? '',
                    'amount' => $amount,
                    'month' => $fee->month,
                    'academic_year' => $fee->academic_year,
                ];
            }

            $totalAmount += $studentTotal;
            $generatedCount++;

            // Generate a temporary transaction ID (not saved)
            $transactionId = 'FS-' . Carbon::now()->format('Ymd') . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);

            $feeSlips[] = [
                'preview' => true,
                'transaction_id' => $transactionId,
                'due_date' => Carbon::now()->addMonth()->day(7)->toDateString(),
                'amount' => $studentTotal,
                'fee_breakdown' => $breakdown,
                'student' => [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'registration_number' => $student->registration_number,
                    'grade' => $student->section?->grade?->name ?? 'N/A',
                    'section' => $student->section?->name ?? 'N/A',
                    'father_name' => $student->parents_name ?? 'N/A',
                ],
                'institute' => [
                    'name' => $student->institute?->name ?? '',
                    'logo' => $student->institute?->logo ?? '',
                    'address' => $student->institute?->address ?? '',
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'fee_slips' => $feeSlips,
                'summary' => [
                    'total_students' => $students->count(),
                    'generated' => $generatedCount,
                    'skipped' => $skippedCount,
                    'total_amount' => $totalAmount,
                ],
                'grade' => $students->first()?->section?->grade?->name ?? '',
                'month' => $month ?? 'All',
                'academic_year' => $academicYear,
            ],
        ]);
    }

    /**
     * Generate/Preview a fee slip for a student
     * This is a "pre-payment challan" showing pending fees
     */
    public function show(Request $request, int $studentId): JsonResponse
    {
        $user = $request->user();
        $month = $request->input('month'); // Only accepts month name: "May", "February", etc.
        $academicYear = $request->input('academic_year', $user->institute?->current_academic_year ?? $this->calculateAcademicYear());
        $dueDate = $request->input('due_date', Carbon::now()->addMonth()->day(7)->toDateString());

        // Verify student belongs to user's institute
        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with(['section.grade', 'institute'])->find($studentId);

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
            $feesQuery->where(function ($q) use ($academicYear) {
                $q->where('academic_year', $academicYear)
                  ->orWhere('academic_year', 'like', $academicYear . '-%')
                  ->orWhere('academic_year', 'like', '%-' . $academicYear);
            });
        }

        // Filter by month name, but ALWAYS include one-time fees (empty month)
        if ($month && strtolower($month) !== 'all') {
            $feesQuery->where(function ($q) use ($month) {
                $q->where('month', $month)           // Monthly fees for this month
                   ->orWhere('month', '')              // One-time fees (empty)
                   ->orWhereNull('month');          // One-time fees (null)
            });
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
                'fee_type_code' => $fee->feeType?->code ?? '',
                'amount' => $amount,
                'month' => $fee->month,
                'academic_year' => $fee->academic_year,
            ];
        }

        // Generate a temporary transaction ID (not saved to DB)
        $transactionId = 'FS-' . Carbon::now()->format('Ymd') . '-' . str_pad($studentId, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'success' => true,
            'data' => [
                'preview' => true,
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
                    'father_name' => $student->parents_name ?? 'N/A',
                ],
                'institute' => [
                    'name' => $student->institute?->name ?? '',
                    'logo' => $student->institute?->logo ?? '',
                    'address' => $student->institute?->address ?? '',
                ],
            ],
        ]);
    }

    /**
     * Calculate current academic year (April to March)
     */
    private function calculateAcademicYear(): string
    {
        $currentYear = date('Y');
        $currentMonth = date('n');

        if ($currentMonth >= 4) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }
}
