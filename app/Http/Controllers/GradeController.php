<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeRequest;
use App\Http\Resources\GradeResource;
use App\Models\Grade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $academicYear = $request->input('academic_year');

        $gradesQuery = Grade::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with('sections');

        if ($academicYear) {
            $gradesQuery->withCount(['gradeFees as grade_fees_count' => function ($query) use ($academicYear) {
                $query->where('academic_year', $academicYear);
            }]);
        } else {
            $gradesQuery->withCount('gradeFees as grade_fees_count');
        }

        $grades = $gradesQuery->get();

        if ($academicYear) {
            $grades->each(function ($grade) use ($academicYear) {
                $grade->load(['gradeFees' => function ($query) use ($academicYear) {
                    $query->where('academic_year', $academicYear);
                }]);
            });
        }

        return response()->json([
            'success' => true,
            'data' => GradeResource::collection($grades),
        ]);
    }

    public function store(GradeRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (!$user->isSuperAdmin()) {
            $data['institute_id'] = $user->institute_id;
        }

        $grade = Grade::create($data);
        $grade->loadCount('gradeFees as grade_fees_count');

        return response()->json([
            'success' => true,
            'message' => 'Grade created successfully',
            'data' => new GradeResource($grade),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $academicYear = $request->input('academic_year');

        $gradeQuery = Grade::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with('sections');

        if ($academicYear) {
            $gradeQuery->withCount(['gradeFees as grade_fees_count' => function ($query) use ($academicYear) {
                $query->where('academic_year', $academicYear);
            }])->with(['gradeFees' => function ($query) use ($academicYear) {
                $query->where('academic_year', $academicYear);
            }]);
        } else {
            $gradeQuery->withCount('gradeFees as grade_fees_count')->with('gradeFees');
        }

        $grade = $gradeQuery->find($id);

        if (!$grade) {
            return response()->json([
                'success' => false,
                'message' => 'Grade not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new GradeResource($grade),
        ]);
    }

    public function update(GradeRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $grade = Grade::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$grade) {
            return response()->json([
                'success' => false,
                'message' => 'Grade not found',
            ], 404);
        }

        $data = $request->validated();
        unset($data['institute_id']);

        $grade->update($data);
        $grade->loadCount('gradeFees as grade_fees_count');

        return response()->json([
            'success' => true,
            'message' => 'Grade updated successfully',
            'data' => new GradeResource($grade),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $grade = Grade::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$grade) {
            return response()->json([
                'success' => false,
                'message' => 'Grade not found',
            ], 404);
        }

        $grade->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grade deleted successfully',
        ]);
    }
}
