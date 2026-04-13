<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportCardResource;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ReportCard;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ReportCardController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ReportCard::query()
            ->with(['exam.examType', 'student.user']);

        if ($request->has('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $reportCards = $query->orderBy('generated_at', 'desc')->paginate($request->get('per_page', 15));

        return ReportCardResource::collection($reportCards);
    }

    public function show(ReportCard $report_card): JsonResponse
    {
        if ($report_card->exam->institute_id !== request()->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new ReportCardResource($report_card->load(['exam.examType', 'student.user'])),
        ]);
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'nullable|exists:students,id',
        ]);

        $exam = Exam::with('examSubjects')->findOrFail($request->exam_id);
        $passingPercentage = $this->getPassingPercentage($request->user()->institute_id);

        DB::beginTransaction();

        try {
            if ($request->student_id) {
                $this->generateForStudent($exam, $request->student_id, $passingPercentage);
                $message = 'Report card generated for student.';
            } else {
                $this->generateForSection($exam, $passingPercentage);
                $message = 'Report cards generated for all students in section.';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report cards. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function studentHistory($studentId): JsonResponse
    {
        $reportCards = ReportCard::where('student_id', $studentId)
            ->with(['exam.examType'])
            ->orderBy('generated_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ReportCardResource::collection($reportCards),
        ]);
    }

    public function destroy(ReportCard $report_card): JsonResponse
    {
        if ($report_card->exam->institute_id !== request()->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $report_card->delete();

        return response()->json([
            'success' => true,
            'message' => 'Report card deleted successfully.',
        ]);
    }

    private function generateForStudent(Exam $exam, int $studentId, int $passingPercentage): void
    {
        $results = ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->with('subject')
            ->get();

        if ($results->isEmpty()) {
            throw new \Exception('No exam results found for this student.');
        }

        $totalMaxMarks = $results->sum('max_marks');
        $totalObtainedMarks = $results->sum('marks_obtained');
        $percentage = $totalMaxMarks > 0 ? round(($totalObtainedMarks / $totalMaxMarks) * 100, 2) : 0;
        $overallGrade = ReportCard::calculateOverallGrade($percentage, $passingPercentage);

        $subjectResults = $results->map(function ($result) {
            return [
                'subject_id' => $result->subject_id,
                'subject_name' => $result->subject?->name,
                'max_marks' => $result->max_marks,
                'marks_obtained' => $result->marks_obtained,
                'percentage' => $result->percentage,
                'grade' => $result->grade,
            ];
        })->toArray();

        ReportCard::updateOrCreate(
            [
                'exam_id' => $exam->id,
                'student_id' => $studentId,
            ],
            [
                'total_marks' => $totalMaxMarks,
                'marks_obtained' => $totalObtainedMarks,
                'percentage' => $percentage,
                'grade' => $overallGrade,
                'subject_results' => $subjectResults,
                'generated_at' => now(),
            ]
        );
    }

    private function generateForSection(Exam $exam, int $passingPercentage): void
    {
        $students = Student::where('grade_id', $exam->grade_id)
            ->where('section_id', $exam->section_id)
            ->where('status', 'active')
            ->get();

        foreach ($students as $student) {
            $this->generateForStudent($exam, $student->id, $passingPercentage);
        }

        $this->calculatePositions($exam);
    }

    private function calculatePositions(Exam $exam): void
    {
        $reportCards = ReportCard::where('exam_id', $exam->id)
            ->orderBy('percentage', 'desc')
            ->get();

        $rank = 1;
        foreach ($reportCards as $reportCard) {
            $reportCard->update([
                'grade_position' => $rank,
            ]);

            $sameSectionRank = 1;
            $sectionReportCards = ReportCard::where('exam_id', $exam->id)
                ->where('student_id', $reportCard->student_id)
                ->whereHas('student', function ($q) use ($reportCard) {
                    $q->where('section_id', $reportCard->student->section_id);
                })
                ->orderBy('percentage', 'desc')
                ->get();

            foreach ($sectionReportCards as $sectionCard) {
                if ($sectionCard->id === $reportCard->id) {
                    $reportCard->update(['section_position' => $sameSectionRank]);
                    break;
                }
                $sameSectionRank++;
            }

            $rank++;
        }
    }

    private function getPassingPercentage(int $instituteId): int
    {
        $institute = \App\Models\Institute::find($instituteId);
        return $institute?->passing_percentage ?? 40;
    }
}
