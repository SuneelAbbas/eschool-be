<?php

namespace App\Http\Controllers;

use App\Models\FeeType;
use App\Http\Resources\FeeTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FeeTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $feeTypes = FeeType::where('institute_id', $user->institute_id)
            ->when($request->has('type'), function ($q) use ($request) {
                return $q->where('type', $request->type);
            })
            ->when($request->has('is_active'), function ($q) use ($request) {
                return $q->where('is_active', $request->boolean('is_active'));
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => FeeTypeResource::collection($feeTypes),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'type' => 'required|in:monthly,one_time',
            'due_day' => 'nullable|integer|min:1|max:28',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['institute_id'] = $user->institute_id;

        $feeType = FeeType::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fee type created successfully',
            'data' => new FeeTypeResource($feeType),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = request()->user();
        
        $feeType = FeeType::where('institute_id', $user->institute_id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new FeeTypeResource($feeType),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $feeType = FeeType::where('institute_id', $user->institute_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50',
            'type' => 'sometimes|in:monthly,one_time',
            'due_day' => 'nullable|integer|min:1|max:28',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $feeType->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fee type updated successfully',
            'data' => new FeeTypeResource($feeType->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = request()->user();
        
        $feeType = FeeType::where('institute_id', $user->institute_id)
            ->findOrFail($id);

        $feeType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fee type deleted successfully',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:fee_types,id',
        ]);

        $deleted = FeeType::where('institute_id', $user->institute_id)
            ->whereIn('id', $validated['ids'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} fee type(s) deleted successfully",
            'data' => ['deleted' => $deleted],
        ]);
    }
}
