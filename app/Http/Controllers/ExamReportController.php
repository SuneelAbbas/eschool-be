<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ReportCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamReportController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
        ]);

        $exam = Exam::with('examType')->findOrFail($request->exam_id);

        $totalStudents = ExamResult::where('exam_id', $exam->id)
            ->distinct('student_id')
            ->count('student_id');

        $totalSubjects = ExamResult::where('exam_id', $exam->id)
            ->distinct('subject_id')
            ->count('subject_id');

        $results = ExamResult::where('exam_id', $exam->id)->get();

        $passCount = $results->where('grade', '!=', 'F')->count();
        $failCount = $results->where('grade', 'F')->count();

        $avgPercentage = $results->count() > 0 ? round($results->avg('percentage'), 2) : 0;
        $highestPercentage = $results->count() > 0 ? round($results->max('percentage'), 2) : 0;
        $lowestPercentage = $results->count() > 0 ? round($results->min('percentage'), 2) : 0;

        $gradeDistribution = $results->groupBy('grade')
            ->map->count()
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'exam' => [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'type' => $exam->examType->name,
                    'status' => $exam->status,
                ],
                'total_students' => $totalStudents,
                'total_subjects' => $totalSubjects,
                'total_results' => $results->count(),
                'pass_count' => $passCount,
                'fail_count' => $failCount,
                'pass_percentage' => $totalStudents > 0 ? round(($passCount / $totalStudents) * 100, 2) : 0,
                'average_percentage' => $avgPercentage,
                'highest_percentage' => $highestPercentage,
                'lowest_percentage' => $lowestPercentage,
                'grade_distribution' => $gradeDistribution,
            ],
        ]);
    }

    public function gradeAnalysis(Request $request): JsonResponse
    {
        $instituteId = $request->user()->institute_id;

        $gradeStats = DB::table('exams')
            ->join('exam_results', 'exams.id', '=', 'exam_results.exam_id')
            ->join('grades', 'exams.grade_id', '=', 'grades.id')
            ->where('exams.institute_id', $instituteId)
            ->where('exams.status', 'completed')
            ->select(
                'grades.id as grade_id',
                'grades.name as grade_name',
                DB::raw('COUNT(DISTINCT exam_results.student_id) as total_students'),
                DB::raw('AVG(exam_results.percentage) as average_percentage'),
                DB::raw('MAX(exam_results.percentage) as highest_percentage'),
                DB::raw('MIN(exam_results.percentage) as lowest_percentage'),
                DB::raw('COUNT(CASE WHEN exam_results.grade != "F" THEN 1 END) as pass_count'),
                DB::raw('COUNT(CASE WHEN exam_results.grade = "F" THEN 1 END) as fail_count')
            )
            ->groupBy('grades.id', 'grades.name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $gradeStats,
        ]);
    }

    public function subjectAnalysis(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
        ]);

        $exam = Exam::findOrFail($request->exam_id);

        $subjectStats = DB::table('exam_results')
            ->join('subjects', 'exam_results.subject_id', '=', 'subjects.id')
            ->where('exam_results.exam_id', $exam->id)
            ->select(
                'subjects.id as subject_id',
                'subjects.name as subject_name',
                DB::raw('COUNT(exam_results.id) as total_entries'),
                DB::raw('AVG(exam_results.percentage) as average_percentage'),
                DB::raw('MAX(exam_results.percentage) as highest_percentage'),
                DB::raw('MIN(exam_results.percentage) as lowest_percentage'),
                DB::raw('COUNT(CASE WHEN exam_results.grade != "F" THEN 1 END) as pass_count'),
                DB::raw('COUNT(CASE WHEN exam_results.grade = "F" THEN 1 END) as fail_count')
            )
            ->groupBy('subjects.id', 'subjects.name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subjectStats,
        ]);
    }

    public function studentComparison(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $results = ExamResult::where('student_id', $request->student_id)
            ->with(['exam.examType', 'subject'])
            ->orderBy('exam_results.created_at', 'desc')
            ->get();

        $comparison = $results->groupBy('exam_id')->map(function ($examResults) {
            $total = $examResults->count();
            $passed = $examResults->where('grade', '!=', 'F')->count();
            $avgPercentage = $examResults->avg('percentage');

            return [
                'exam_id' => $examResults->first()->exam_id,
                'exam_title' => $examResults->first()->exam->title ?? 'N/A',
                'exam_type' => $examResults->first()->exam->examType->name ?? 'N/A',
                'total_subjects' => $total,
                'passed_subjects' => $passed,
                'failed_subjects' => $total - $passed,
                'average_percentage' => round($avgPercentage, 2),
                'overall_grade' => ExamResult::calculateGrade($avgPercentage),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }
}
