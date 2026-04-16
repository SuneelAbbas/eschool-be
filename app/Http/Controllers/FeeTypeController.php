<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeeTypeRequest;
use App\Http\Resources\FeeTypeResource;
use App\Models\FeeType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');

        $query = FeeType::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $feeTypes = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => FeeTypeResource::collection($feeTypes->items()),
            'meta' => [
                'current_page' => $feeTypes->currentPage(),
                'last_page' => $feeTypes->lastPage(),
                'per_page' => $feeTypes->perPage(),
                'total' => $feeTypes->total(),
            ],
        ]);
    }

    public function store(FeeTypeRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (!$user->isSuperAdmin()) {
            $data['institute_id'] = $user->institute_id;
        }

        $feeType = FeeType::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Fee type created successfully',
            'data' => new FeeTypeResource($feeType),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $feeType = FeeType::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$feeType) {
            return response()->json([
                'success' => false,
                'message' => 'Fee type not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new FeeTypeResource($feeType),
        ]);
    }

    public function update(FeeTypeRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $feeType = FeeType::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$feeType) {
            return response()->json([
                'success' => false,
                'message' => 'Fee type not found',
            ], 404);
        }

        $data = $request->validated();
        unset($data['institute_id']);

        $feeType->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Fee type updated successfully',
            'data' => new FeeTypeResource($feeType),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $feeType = FeeType::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$feeType) {
            return response()->json([
                'success' => false,
                'message' => 'Fee type not found',
            ], 404);
        }

        $feeType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fee type deleted successfully',
        ]);
    }
}
