<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeFeeRequest;
use App\Http\Resources\GradeFeeResource;
use App\Models\GradeFee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeFeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $gradeId = $request->input('grade_id');
        $academicYear = $request->input('academic_year');

        $query = GradeFee::with(['grade', 'feeType']);

        if ($gradeId) {
            $query->where('grade_id', $gradeId);
        }

        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }

        if (!$user->isSuperAdmin()) {
            $query->whereHas('grade', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        }

        $gradeFees = $query->get();

        return response()->json([
            'success' => true,
            'data' => GradeFeeResource::collection($gradeFees),
        ]);
    }

    public function store(GradeFeeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $gradeFee = GradeFee::create($data);
        $gradeFee->load(['grade', 'feeType']);

        return response()->json([
            'success' => true,
            'message' => 'Fee assigned to grade successfully',
            'data' => new GradeFeeResource($gradeFee),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $gradeFee = GradeFee::with(['grade', 'feeType'])->find($id);

        if (!$gradeFee) {
            return response()->json([
                'success' => false,
                'message' => 'Grade fee not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new GradeFeeResource($gradeFee),
        ]);
    }

    public function update(GradeFeeRequest $request, int $id): JsonResponse
    {
        $gradeFee = GradeFee::find($id);

        if (!$gradeFee) {
            return response()->json([
                'success' => false,
                'message' => 'Grade fee not found',
            ], 404);
        }

        $gradeFee->update($request->validated());
        $gradeFee->load(['grade', 'feeType']);

        return response()->json([
            'success' => true,
            'message' => 'Grade fee updated successfully',
            'data' => new GradeFeeResource($gradeFee),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $gradeFee = GradeFee::find($id);

        if (!$gradeFee) {
            return response()->json([
                'success' => false,
                'message' => 'Grade fee not found',
            ], 404);
        }

        $gradeFee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fee removed from grade successfully',
        ]);
    }
}
