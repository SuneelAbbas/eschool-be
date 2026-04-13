<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExamTypeRequest;
use App\Http\Resources\ExamTypeResource;
use App\Models\ExamType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExamTypeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ExamType::query()->where('institute_id', $request->user()->institute_id);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        $examTypes = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return ExamTypeResource::collection($examTypes);
    }

    public function store(ExamTypeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['institute_id'] = $request->user()->institute_id;

        $examType = ExamType::create($data);

        return response()->json([
            'success' => true,
            'data' => new ExamTypeResource($examType),
            'message' => 'Exam type created successfully.',
        ], 201);
    }

    public function show(ExamType $exam_type): JsonResponse
    {
        if ($exam_type->institute_id !== request()->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new ExamTypeResource($exam_type->load('exams')),
        ]);
    }

    public function update(ExamTypeRequest $request, ExamType $exam_type): JsonResponse
    {
        if ($exam_type->institute_id !== $request->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $exam_type->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => new ExamTypeResource($exam_type),
            'message' => 'Exam type updated successfully.',
        ]);
    }

    public function destroy(ExamType $exam_type): JsonResponse
    {
        if ($exam_type->institute_id !== request()->user()->institute_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($exam_type->exams()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete exam type with associated exams.',
            ], 422);
        }

        $exam_type->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exam type deleted successfully.',
        ]);
    }
}
