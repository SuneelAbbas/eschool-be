<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentDiscountRequest;
use App\Http\Resources\StudentDiscountResource;
use App\Models\StudentDiscount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentDiscountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $request->input('student_id');

        $query = StudentDiscount::with(['student', 'discount']);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        if (!$user->isSuperAdmin()) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        }

        $studentDiscounts = $query->get();

        return response()->json([
            'success' => true,
            'data' => StudentDiscountResource::collection($studentDiscounts),
        ]);
    }

    public function store(StudentDiscountRequest $request): JsonResponse
    {
        $data = $request->validated();
        $studentDiscount = StudentDiscount::create($data);
        $studentDiscount->load(['student', 'discount']);

        return response()->json([
            'success' => true,
            'message' => 'Discount assigned to student successfully',
            'data' => new StudentDiscountResource($studentDiscount),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $studentDiscount = StudentDiscount::with(['student', 'discount'])->find($id);

        if (!$studentDiscount) {
            return response()->json([
                'success' => false,
                'message' => 'Student discount not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new StudentDiscountResource($studentDiscount),
        ]);
    }

    public function update(StudentDiscountRequest $request, int $id): JsonResponse
    {
        $studentDiscount = StudentDiscount::find($id);

        if (!$studentDiscount) {
            return response()->json([
                'success' => false,
                'message' => 'Student discount not found',
            ], 404);
        }

        $studentDiscount->update($request->validated());
        $studentDiscount->load(['student', 'discount']);

        return response()->json([
            'success' => true,
            'message' => 'Student discount updated successfully',
            'data' => new StudentDiscountResource($studentDiscount),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $studentDiscount = StudentDiscount::find($id);

        if (!$studentDiscount) {
            return response()->json([
                'success' => false,
                'message' => 'Student discount not found',
            ], 404);
        }

        $studentDiscount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Discount removed from student successfully',
        ]);
    }
}
