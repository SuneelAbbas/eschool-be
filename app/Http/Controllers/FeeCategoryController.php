<?php

namespace App\Http\Controllers;

use App\Models\FeeCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FeeCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $categories = FeeCategory::where('institute_id', $user->institute_id)
            ->when($request->has('is_active'), function ($q) use ($request) {
                return $q->where('is_active', $request->boolean('is_active'));
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['institute_id'] = $user->institute_id;

        $category = FeeCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fee category created successfully',
            'data' => $category,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = request()->user();
        
        $category = FeeCategory::where('institute_id', $user->institute_id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $category = FeeCategory::where('institute_id', $user->institute_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:10',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fee category updated successfully',
            'data' => $category->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = request()->user();
        
        $category = FeeCategory::where('institute_id', $user->institute_id)
            ->findOrFail($id);

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fee category deleted successfully',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:fee_categories,id',
        ]);

        $deleted = FeeCategory::where('institute_id', $user->institute_id)
            ->whereIn('id', $validated['ids'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} fee category(ies) deleted successfully",
            'data' => ['deleted' => $deleted],
        ]);
    }
}
