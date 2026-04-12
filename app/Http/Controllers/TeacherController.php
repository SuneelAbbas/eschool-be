<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $teachers = Teacher::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->get();

        return response()->json([
            'success' => true,
            'data' => TeacherResource::collection($teachers),
        ]);
    }

    public function store(TeacherRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (!$user->isSuperAdmin()) {
            $data['institute_id'] = $user->institute_id;
        }

        $teacher = Teacher::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Teacher created successfully',
            'data' => new TeacherResource($teacher),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $teacher = Teacher::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new TeacherResource($teacher),
        ]);
    }

    public function update(TeacherRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $teacher = Teacher::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found',
            ], 404);
        }

        $data = $request->validated();
        unset($data['institute_id']);

        $teacher->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Teacher updated successfully',
            'data' => new TeacherResource($teacher),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $teacher = Teacher::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found',
            ], 404);
        }

        $teacher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Teacher deleted successfully',
        ]);
    }
}
