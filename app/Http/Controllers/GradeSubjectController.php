<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeSubjectRequest;
use App\Http\Resources\GradeSubjectResource;
use App\Models\GradeSubject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GradeSubjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = GradeSubject::with(['grade', 'subject'])
            ->whereHas('grade', function ($q) use ($request) {
                $q->where('institute_id', $request->user()->institute_id);
            });

        if ($request->has('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->has('is_compulsory')) {
            $query->where('is_compulsory', $request->boolean('is_compulsory'));
        }

        $gradeSubjects = $query->orderBy('grade_id')
            ->orderBy('subject_id')
            ->paginate($request->get('per_page', 50));

        return GradeSubjectResource::collection($gradeSubjects);
    }

    public function store(GradeSubjectRequest $request): JsonResponse
    {
        $data = $request->validated();

        $existing = GradeSubject::where('grade_id', $data['grade_id'])
            ->where('subject_id', $data['subject_id'])
            ->whereHas('grade', function ($q) use ($request) {
                $q->where('institute_id', $request->user()->institute_id);
            })
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This subject is already linked to this grade.',
            ], 422);
        }

        $gradeSubject = GradeSubject::create($data);

        return response()->json([
            'success' => true,
            'data' => new GradeSubjectResource($gradeSubject->load(['grade', 'subject'])),
            'message' => 'Subject linked to grade successfully.',
        ], 201);
    }

    public function show(GradeSubject $grade_subject): JsonResponse
    {
        if ($grade_subject->grade->institute_id !== request()->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new GradeSubjectResource($grade_subject->load(['grade', 'subject'])),
        ]);
    }

    public function update(GradeSubjectRequest $request, GradeSubject $grade_subject): JsonResponse
    {
        if ($grade_subject->grade->institute_id !== $request->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $grade_subject->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => new GradeSubjectResource($grade_subject->load(['grade', 'subject'])),
            'message' => 'Grade subject updated successfully.',
        ]);
    }

    public function destroy(GradeSubject $grade_subject): JsonResponse
    {
        if ($grade_subject->grade->institute_id !== request()->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $grade_subject->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject unlinked from grade successfully.',
        ]);
    }

    public function getByGrade(Request $request, $gradeId): JsonResponse
    {
        $gradeSubjects = GradeSubject::with(['grade', 'subject'])
            ->where('grade_id', $gradeId)
            ->orderBy('subject_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $gradeSubjects,
        ]);
    }
}
