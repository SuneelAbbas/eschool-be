<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentFeeRequest;
use App\Http\Resources\StudentFeeResource;
use App\Models\StudentFee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentFeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $request->input('student_id');
        $academicYear = $request->input('academic_year');

        $query = StudentFee::with(['student', 'feeType']);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }

        if (!$user->isSuperAdmin()) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        }

        $studentFees = $query->get();

        return response()->json([
            'success' => true,
            'data' => StudentFeeResource::collection($studentFees),
        ]);
    }

    public function store(StudentFeeRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        if (!isset($data['academic_year'])) {
            $data['academic_year'] = (string) now()->year;
        }
        
        $studentFee = StudentFee::create($data);
        $studentFee->load(['student', 'feeType']);

        return response()->json([
            'success' => true,
            'message' => 'Custom fee assigned to student successfully',
            'data' => new StudentFeeResource($studentFee),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $studentFee = StudentFee::with(['student', 'feeType'])->find($id);

        if (!$studentFee) {
            return response()->json([
                'success' => false,
                'message' => 'Student fee not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new StudentFeeResource($studentFee),
        ]);
    }

    public function update(StudentFeeRequest $request, int $id): JsonResponse
    {
        $studentFee = StudentFee::find($id);

        if (!$studentFee) {
            return response()->json([
                'success' => false,
                'message' => 'Student fee not found',
            ], 404);
        }

        $studentFee->update($request->validated());
        $studentFee->load(['student', 'feeType']);

        return response()->json([
            'success' => true,
            'message' => 'Student fee updated successfully',
            'data' => new StudentFeeResource($studentFee),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $studentFee = StudentFee::find($id);

        if (!$studentFee) {
            return response()->json([
                'success' => false,
                'message' => 'Student fee not found',
            ], 404);
        }

        $studentFee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student fee removed successfully',
        ]);
    }
}
