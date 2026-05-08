<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\PendingReceipt;
use App\Models\Attendance;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $instituteId = $user->isSuperAdmin() ? null : $user->institute_id;
        
        $today = Carbon::today()->toDateString();
        
        // Students
        $studentsQuery = Student::when($instituteId, fn($q) => $q->where('institute_id', $instituteId));
        $studentsTotal = (clone $studentsQuery)->count();
        
        $studentsByGrade = \App\Models\Section::when($instituteId, fn($q) => $q->where('institute_id', $instituteId))
            ->with('grade')
            ->get()
            ->groupBy('grade_id')
            ->map(fn($sections, $gradeId) => [
                'grade' => $sections->first()->grade?->name,
                'count' => $sections->sum(fn($s) => $s->students()->count())
            ])
            ->filter()
            ->values();

        // Teachers
        $teachersQuery = Teacher::when($instituteId, fn($q) => $q->where('institute_id', $instituteId));
        $teachersTotal = (clone $teachersQuery)->count();
        $teachersMale = (clone $teachersQuery)->where('gender', 'male')->count();
        $teachersFemale = (clone $teachersQuery)->where('gender', 'female')->count();

        // Subjects
        $subjectsTotal = \App\Models\Subject::when($instituteId, fn($q) => $q->where('institute_id', $instituteId))->count();

        // Staff (non-teacher users)
        $staffTotal = User::when($instituteId, fn($q) => $q->where('institute_id', $instituteId))
            ->where('id', '!=', $user->id)
            ->count();

        // Fee Stats
        $currentMonthNum = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Academic year: July-Dec = current year, Jan-Jun = previous year
        $academicYearStart = $currentMonthNum >= 7 ? $currentYear : $currentYear - 1;
        $academicYear = "{$academicYearStart}-" . ($academicYearStart + 1);
        
        $feeQuery = PendingReceipt::whereHas('student', function ($q) use ($instituteId) {
            if ($instituteId) {
                $q->where('institute_id', $instituteId);
            }
        });
        
        // Current month name
        $currentMonth = Carbon::now()->format('F');
        
        $feesThisMonth = (clone $feeQuery)
            ->where('month', $currentMonth)
            ->where('academic_year', $academicYear)
            ->get();
        
        $feesCollectedThisMonth = $feesThisMonth->where('status', 'paid')->sum('amount');
        $feesPendingThisMonth = $feesThisMonth->where('status', 'pending')->sum('amount');
        
        $feesOverdue = (clone $feeQuery)
            ->where('status', 'pending')
            ->where('due_date', '<', $today)
            ->sum('amount');
        
        $feesCollectedThisYear = (clone $feeQuery)
            ->where('status', 'paid')
            ->where('academic_year', $academicYear)
            ->sum('amount');

        // Fee Defaulters (students with pending/overdue fees)
        $defaulterReceipts = (clone $feeQuery)->where('status', 'pending')->get();
        $defaulterStudents = $defaulterReceipts->groupBy('student_id')
            ->map(function ($receipts, $studentId) use ($defaulterReceipts, $today) {
                $student = \App\Models\Student::find($studentId);
                $totalDue = $receipts->sum('amount');
                $overdueCount = $receipts->where('due_date', '<', $today)->count();
                return [
                    'student_id' => $studentId,
                    'student_name' => $student ? $student->first_name . ' ' . $student->last_name : 'Unknown',
                    'registration_number' => $student?->registration_number,
                    'total_due' => (float) $totalDue,
                    'overdue_slips' => $overdueCount,
                ];
            })
            ->sortByDesc('total_due')
            ->take(10)
            ->values();

        $feeDefaultersCount = $defaulterStudents->count();
        $feeDefaultersTotal = $defaulterStudents->sum('total_due');

        // Attendance Today
        $attendanceToday = Attendance::where('date', $today);
        if ($instituteId) {
            $attendanceToday->whereHas('student', fn($q) => $q->where('institute_id', $instituteId));
        }
        
        $attendancePresent = (clone $attendanceToday)->where('status', 'present')->count();
        $attendanceAbsent = (clone $attendanceToday)->where('status', 'absent')->count();
        $attendanceLate = (clone $attendanceToday)->where('status', 'late')->count();
        $attendanceTotal = $attendancePresent + $attendanceAbsent + $attendanceLate;
        $attendancePercentage = $attendanceTotal > 0 
            ? round(($attendancePresent / $attendanceTotal) * 100, 1) 
            : 0;

        // Exams
        $examsQuery = Exam::when($instituteId, fn($q) => $q->where('institute_id', $instituteId));
        $examsUpcoming = (clone $examsQuery)
            ->where('start_date', '>=', $today)
            ->count();
        $examsCompleted = (clone $examsQuery)
            ->where('end_date', '<', $today)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'students' => [
                    'total' => $studentsTotal,
                    'by_grade' => $studentsByGrade,
                ],
                'teachers' => [
                    'total' => $teachersTotal,
                    'male' => $teachersMale,
                    'female' => $teachersFemale,
                ],
                'subjects' => [
                    'total' => $subjectsTotal,
                ],
                'staff' => [
                    'total' => $staffTotal,
                ],
                'fees' => [
                    'collected_this_month' => (float) $feesCollectedThisMonth,
                    'pending_this_month' => (float) $feesPendingThisMonth,
                    'overdue' => (float) $feesOverdue,
                    'collected_this_year' => (float) $feesCollectedThisYear,
                ],
                'fee_defaulters' => [
                    'total_students' => $feeDefaultersCount,
                    'total_amount' => (float) $feeDefaultersTotal,
                    'top_defaulters' => $defaulterStudents,
                ],
                'attendance' => [
                    'date' => $today,
                    'present' => $attendancePresent,
                    'absent' => $attendanceAbsent,
                    'late' => $attendanceLate,
                    'present_percentage' => $attendancePercentage,
                ],
                'exams' => [
                    'upcoming' => $examsUpcoming,
                    'completed' => $examsCompleted,
                ],
            ],
        ]);
    }
}