<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $students = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with('section')->get();

        return response()->json([
            'success' => true,
            'data' => StudentResource::collection($students),
        ]);
    }

    public function store(StudentRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (!$user->isSuperAdmin()) {
            $data['institute_id'] = $user->institute_id;
        }

        $student = Student::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data' => new StudentResource($student),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with('section')->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new StudentResource($student),
        ]);
    }

    public function update(StudentRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        $data = $request->validated();
        unset($data['institute_id']);

        $student->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data' => new StudentResource($student),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully',
        ]);
    }
}
