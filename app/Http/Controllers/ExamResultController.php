<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkExamResultRequest;
use App\Http\Requests\ExamResultRequest;
use App\Http\Resources\ExamResultResource;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSubject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ExamResultController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ExamResult::query()
            ->with(['exam', 'student.user', 'subject']);

        if ($request->has('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $results = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 50));

        return ExamResultResource::collection($results);
    }

    public function store(ExamResultRequest $request): JsonResponse
    {
        $data = $request->validated();

        $percentage = ExamResult::calculatePercentage($data['marks_obtained'], $data['max_marks']);
        $passingPercentage = $this->getPassingPercentage($request->user()->institute_id);
        $grade = ExamResult::calculateGrade($percentage, $passingPercentage);

        $data['percentage'] = $percentage;
        $data['grade'] = $grade;

        $existing = ExamResult::where('exam_id', $data['exam_id'])
            ->where('student_id', $data['student_id'])
            ->where('subject_id', $data['subject_id'])
            ->first();

        if ($existing) {
            $existing->update($data);
            $result = $existing;
            $message = 'Exam result updated successfully.';
        } else {
            $result = ExamResult::create($data);
            $message = 'Exam result created successfully.';
        }

        return response()->json([
            'success' => true,
            'data' => new ExamResultResource($result->load(['exam', 'student.user', 'subject'])),
            'message' => $message,
        ], 201);
    }

    public function bulkStore(BulkExamResultRequest $request): JsonResponse
    {
        $data = $request->validated();
        $results = $data['results'];
        $passingPercentage = $this->getPassingPercentage($request->user()->institute_id);

        DB::beginTransaction();

        try {
            $created = [];
            $updated = [];

            foreach ($results as $resultData) {
                $percentage = ExamResult::calculatePercentage(
                    $resultData['marks_obtained'],
                    $resultData['max_marks']
                );
                $grade = ExamResult::calculateGrade($percentage, $passingPercentage);

                $dataToSave = array_merge($resultData, [
                    'exam_id' => $data['exam_id'],
                    'percentage' => $percentage,
                    'grade' => $grade,
                ]);

                $existing = ExamResult::where('exam_id', $data['exam_id'])
                    ->where('student_id', $resultData['student_id'])
                    ->where('subject_id', $resultData['subject_id'])
                    ->first();

                if ($existing) {
                    $existing->update($dataToSave);
                    $updated[] = $existing->id;
                } else {
                    $result = ExamResult::create($dataToSave);
                    $created[] = $result->id;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'created_count' => count($created),
                    'updated_count' => count($updated),
                ],
                'message' => sprintf(
                    'Processed %d results (%d created, %d updated).',
                    count($created) + count($updated),
                    count($created),
                    count($updated)
                ),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process results. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(ExamResult $exam_result): JsonResponse
    {
        if ($exam_result->exam->institute_id !== request()->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new ExamResultResource($exam_result->load(['exam', 'student.user', 'subject'])),
        ]);
    }

    public function update(ExamResultRequest $request, ExamResult $exam_result): JsonResponse
    {
        if ($exam_result->exam->institute_id !== $request->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validated();
        $percentage = ExamResult::calculatePercentage($data['marks_obtained'], $data['max_marks']);
        $passingPercentage = $this->getPassingPercentage($request->user()->institute_id);
        $grade = ExamResult::calculateGrade($percentage, $passingPercentage);

        $data['percentage'] = $percentage;
        $data['grade'] = $grade;

        $exam_result->update($data);

        return response()->json([
            'success' => true,
            'data' => new ExamResultResource($exam_result->load(['exam', 'student.user', 'subject'])),
            'message' => 'Exam result updated successfully.',
        ]);
    }

    public function destroy(ExamResult $exam_result): JsonResponse
    {
        if ($exam_result->exam->institute_id !== request()->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $exam_result->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exam result deleted successfully.',
        ]);
    }

    public function byExam(Exam $exam): AnonymousResourceCollection
    {
        $results = ExamResult::where('exam_id', $exam->id)
            ->with(['student.user', 'subject'])
            ->orderBy('student_id')
            ->orderBy('subject_id')
            ->get();

        return ExamResultResource::collection($results);
    }

    public function byStudent(Request $request, $studentId): AnonymousResourceCollection
    {
        $query = ExamResult::where('student_id', $studentId)
            ->with(['exam.examType', 'subject']);

        if ($request->has('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        $results = $query->orderBy('created_at', 'desc')->get();

        return ExamResultResource::collection($results);
    }

    private function getPassingPercentage(int $instituteId): int
    {
        $institute = \App\Models\Institute::find($instituteId);
        return $institute?->passing_percentage ?? 40;
    }
}
