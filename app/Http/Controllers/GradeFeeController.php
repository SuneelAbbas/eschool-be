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

    public function storeBatch(Request $request): JsonResponse
    {
        $user = $request->user();
        $gradeFees = $request->input('grade_fees', []);
        
        if (empty($gradeFees)) {
            return response()->json([
                'success' => false,
                'message' => 'No grade fees provided',
            ], 422);
        }

        $created = [];
        $errors = [];
        
        foreach ($gradeFees as $index => $feeData) {
            try {
                $gradeFee = GradeFee::create([
                    'grade_id' => $feeData['grade_id'],
                    'fee_type_id' => $feeData['fee_type_id'],
                    'academic_year' => $feeData['academic_year'] ?? null,
                    'amount' => $feeData['amount'],
                    'effective_from' => $feeData['effective_from'],
                    'effective_to' => $feeData['effective_to'] ?? null,
                ]);
                $gradeFee->load(['grade', 'feeType']);
                $created[] = $gradeFee;
            } catch (\Exception $e) {
                $errors[] = ['index' => $index, 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'success' => count($errors) === 0,
            'message' => count($created) . ' grade fee(s) created',
            'data' => GradeFeeResource::collection($created),
            'created_count' => count($created),
            'errors' => $errors,
        ]);
    }

    public function updateBatch(Request $request): JsonResponse
    {
        $gradeFeesData = $request->input('grade_fees', []);
        
        if (empty($gradeFeesData)) {
            return response()->json([
                'success' => false,
                'message' => 'No grade fees provided',
            ], 422);
        }

        $updated = [];
        $errors = [];
        
        foreach ($gradeFeesData as $index => $feeData) {
            try {
                $gradeFee = GradeFee::find($feeData['id']);
                
                if ($gradeFee) {
                    $gradeFee->update([
                        'amount' => $feeData['amount'],
                        'effective_from' => $feeData['effective_from'],
                        'effective_to' => $feeData['effective_to'] ?? null,
                    ]);
                    $gradeFee->load(['grade', 'feeType']);
                    $updated[] = $gradeFee;
                } else {
                    $errors[] = ['index' => $index, 'error' => 'Grade fee not found'];
                }
            } catch (\Exception $e) {
                $errors[] = ['index' => $index, 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'success' => count($errors) === 0,
            'message' => count($updated) . ' grade fee(s) updated',
            'data' => GradeFeeResource::collection($updated),
            'updated_count' => count($updated),
            'errors' => $errors,
        ]);
    }

    public function destroyBatch(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No IDs provided',
            ], 422);
        }

        $deleted = GradeFee::destroy($ids);

        return response()->json([
            'success' => true,
            'message' => $deleted . ' grade fee(s) deleted',
            'deleted_count' => $deleted,
        ]);
    }
}
