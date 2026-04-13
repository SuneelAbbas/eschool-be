<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiscountRequest;
use App\Http\Resources\DiscountResource;
use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $discounts = Discount::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => DiscountResource::collection($discounts),
        ]);
    }

    public function store(DiscountRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (!$user->isSuperAdmin()) {
            $data['institute_id'] = $user->institute_id;
        }

        $discount = Discount::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Discount created successfully',
            'data' => new DiscountResource($discount),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $discount = Discount::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Discount not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new DiscountResource($discount),
        ]);
    }

    public function update(DiscountRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $discount = Discount::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Discount not found',
            ], 404);
        }

        $data = $request->validated();
        unset($data['institute_id']);

        $discount->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Discount updated successfully',
            'data' => new DiscountResource($discount),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $discount = Discount::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$discount) {
            return response()->json([
                'success' => false,
                'message' => 'Discount not found',
            ], 404);
        }

        $discount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Discount deleted successfully',
        ]);
    }
}
