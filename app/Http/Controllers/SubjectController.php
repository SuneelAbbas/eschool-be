<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');

        $query = Subject::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $subjects = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => SubjectResource::collection($subjects->items()),
            'meta' => [
                'current_page' => $subjects->currentPage(),
                'last_page' => $subjects->lastPage(),
                'per_page' => $subjects->perPage(),
                'total' => $subjects->total(),
            ],
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $baseQuery = Subject::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        });

        $total = (clone $baseQuery)->count();
        $withCode = (clone $baseQuery)->whereNotNull('code')->where('code', '!=', '')->count();
        $withDescription = (clone $baseQuery)->whereNotNull('description')->where('description', '!=', '')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'with_code' => $withCode,
                'with_description' => $withDescription,
            ],
        ]);
    }

    public function store(SubjectRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (!$user->isSuperAdmin()) {
            $data['institute_id'] = $user->institute_id;
        }

        $subject = Subject::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Subject created successfully',
            'data' => new SubjectResource($subject),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $subject = Subject::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new SubjectResource($subject),
        ]);
    }

    public function update(SubjectRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $subject = Subject::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found',
            ], 404);
        }

        $data = $request->validated();
        unset($data['institute_id']);

        $subject->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Subject updated successfully',
            'data' => new SubjectResource($subject),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $subject = Subject::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found',
            ], 404);
        }

        if ($subject->teacherSections()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete subject that is assigned to teachers',
            ], 422);
        }

        $subject->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject deleted successfully',
        ]);
    }
}
