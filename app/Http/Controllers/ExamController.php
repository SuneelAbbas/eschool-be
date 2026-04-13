<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExamRequest;
use App\Http\Resources\ExamResource;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Exam::query()
            ->where('institute_id', $request->user()->institute_id)
            ->with(['examType', 'grade', 'section']);

        if ($request->has('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        if ($request->has('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->has('exam_type_id')) {
            $query->where('exam_type_id', $request->exam_type_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $exams = $query->orderBy('start_date', 'desc')->paginate($request->get('per_page', 15));

        return ExamResource::collection($exams);
    }

    public function store(ExamRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['institute_id'] = $request->user()->institute_id;

        DB::beginTransaction();

        try {
            $exam = Exam::create($data);

            $gradeSubjects = GradeSubject::where('grade_id', $data['grade_id'])->get();

            if (!empty($data['subjects'])) {
                foreach ($data['subjects'] as $subjectId) {
                    $gradeSubject = $gradeSubjects->firstWhere('subject_id', $subjectId);
                    $maxMarks = $gradeSubject?->max_marks ?? $data['total_marks'] ?? 100;
                    ExamSubject::create([
                        'exam_id' => $exam->id,
                        'subject_id' => $subjectId,
                        'max_marks' => $maxMarks,
                        'passing_marks' => round($maxMarks * 0.4),
                    ]);
                }
            } elseif ($gradeSubjects->isNotEmpty()) {
                foreach ($gradeSubjects as $gs) {
                    $maxMarks = $gs->max_marks ?? $data['total_marks'] ?? 100;
                    ExamSubject::create([
                        'exam_id' => $exam->id,
                        'subject_id' => $gs->subject_id,
                        'max_marks' => $maxMarks,
                        'passing_marks' => round($maxMarks * 0.4),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new ExamResource($exam->load(['examType', 'grade', 'section', 'examSubjects.subject'])),
                'message' => 'Exam created successfully.',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create exam. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Exam $exam): JsonResponse
    {
        if ($exam->institute_id !== request()->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $exam->load(['examType', 'grade', 'section', 'examSubjects.subject']);

        return response()->json([
            'success' => true,
            'data' => new ExamResource($exam),
        ]);
    }

    public function update(ExamRequest $request, Exam $exam): JsonResponse
    {
        if ($exam->institute_id !== $request->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            $data = $request->validated();
            $exam->update($data);

            $gradeSubjects = GradeSubject::where('grade_id', $data['grade_id'] ?? $exam->grade_id)->get();

            if ($request->has('subjects')) {
                $exam->examSubjects()->delete();

                foreach ($request->subjects as $subjectId) {
                    $gradeSubject = $gradeSubjects->firstWhere('subject_id', $subjectId);
                    $maxMarks = $gradeSubject?->max_marks ?? ($request->total_marks ?? 100);
                    ExamSubject::create([
                        'exam_id' => $exam->id,
                        'subject_id' => $subjectId,
                        'max_marks' => $maxMarks,
                        'passing_marks' => round($maxMarks * 0.4),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new ExamResource($exam->load(['examType', 'grade', 'section', 'examSubjects.subject'])),
                'message' => 'Exam updated successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Exam $exam): JsonResponse
    {
        if ($exam->institute_id !== request()->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($exam->examResults()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete exam with recorded results.',
            ], 422);
        }

        $exam->examSubjects()->delete();
        $exam->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exam deleted successfully.',
        ]);
    }

    public function students(Exam $exam): JsonResponse
    {
        $students = Student::where('section_id', $exam->section_id)
            ->where('grade_id', $exam->grade_id)
            ->where('status', 'active')
            ->with(['user'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $students,
        ]);
    }

    public function addSubjects(Request $request, Exam $exam): JsonResponse
    {
        $request->validate([
            'subjects' => 'required|array',
            'subjects.*' => 'exists:subjects,id',
        ]);

        $existingIds = $exam->examSubjects()->pluck('subject_id')->toArray();

        foreach ($request->subjects as $subjectId) {
            if (!in_array($subjectId, $existingIds)) {
                ExamSubject::create([
                    'exam_id' => $exam->id,
                    'subject_id' => $subjectId,
                    'max_marks' => $exam->total_marks,
                    'passing_marks' => round($exam->total_marks * 0.4),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $exam->load('examSubjects'),
            'message' => 'Subjects added successfully.',
        ]);
    }
}
