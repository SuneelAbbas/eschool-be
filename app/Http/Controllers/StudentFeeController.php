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

    /**
     * Clear all student fees for a grade (bulk delete)
     */
    public function clearGradeStudentFees(Request $request): JsonResponse
    {
        $request->validate([
            'grade_id' => 'required|integer',
            'academic_year' => 'required|string',
            'month' => 'nullable|string',
        ]);

        $gradeId = $request->input('grade_id');
        $academicYear = $request->input('academic_year');
        $month = $request->input('month');

        $sectionIds = \App\Models\Section::where('grade_id', $gradeId)->pluck('id')->toArray();

        if (empty($sectionIds)) {
            return response()->json([
                'success' => true,
                'message' => 'No sections found for this grade',
                'data' => [
                    'deleted_count' => 0,
                ]
            ]);
        }

        $studentIds = \App\Models\Student::whereIn('section_id', $sectionIds)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();

        if (empty($studentIds)) {
            return response()->json([
                'success' => true,
                'message' => 'No students found in this grade',
                'data' => [
                    'deleted_count' => 0,
                ]
            ]);
        }

        $query = StudentFee::whereIn('student_id', $studentIds)
            ->where('academic_year', $academicYear);

        if ($month) {
            $query->where('month', $month);
        }

        $deleted = $query->delete();

        return response()->json([
            'success' => true,
            'message' => "Cleared {$deleted} student fee(s) for this grade",
            'data' => [
                'deleted_count' => $deleted,
            ]
        ]);
    }

    /**
     * Get all fees for a specific student with balance calculation
     */
    public function getStudentFees(Request $request, int $studentId): JsonResponse
    {
        $academicYear = $request->input('academic_year');

        $query = StudentFee::with(['feeType', 'paymentRecords'])
            ->where('student_id', $studentId);

        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }

        $fees = $query->orderBy('created_at', 'desc')->get();

        // Calculate totals
        $totalOwed = 0;
        $totalPaid = 0;

        $feesWithBalance = $fees->map(function ($fee) use (&$totalOwed, &$totalPaid) {
            $feeAmount = (float) $fee->amount;
            $paidAmount = (float) $fee->paymentRecords->sum('amount_applied');
            
            $totalOwed += $feeAmount;
            $totalPaid += $paidAmount;

            return [
                'id' => $fee->id,
                'fee_type_id' => $fee->fee_type_id,
                'fee_type_name' => $fee->feeType?->name,
                'fee_type_code' => $fee->feeType?->code,
                'fee_type_type' => $fee->feeType?->type,
                'academic_year' => $fee->academic_year,
                'month' => $fee->month,
                'amount' => $feeAmount,
                'paid' => $paidAmount,
                'balance' => $feeAmount - $paidAmount,
                'status' => $fee->status,
                'is_inherited' => $fee->is_inherited,
                'is_custom' => $fee->is_custom,
                'prorate_percentage' => $fee->prorate_percentage,
                'effective_from' => $fee->effective_from,
                'effective_to' => $fee->effective_to,
                'created_at' => $fee->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'fees' => $feesWithBalance,
                'summary' => [
                    'total_owed' => $totalOwed,
                    'total_paid' => $totalPaid,
                    'balance' => $totalOwed - $totalPaid,
                    'fees_count' => $fees->count(),
                ],
            ],
        ]);
    }

    /**
     * Override fee amount for a specific student fee
     */
    public function overrideAmount(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $studentFee = StudentFee::find($id);

        if (!$studentFee) {
            return response()->json([
                'success' => false,
                'message' => 'Student fee not found',
            ], 404);
        }

        $studentFee->update([
            'amount' => $request->input('amount'),
            'is_custom' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fee amount overridden successfully',
            'data' => new StudentFeeResource($studentFee->fresh(['feeType', 'paymentRecords'])),
        ]);
    }

    /**
     * Assign a fee type to a student manually
     */
    public function assignToStudent(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'academic_year' => 'required|string',
            'amount' => 'nullable|numeric',
            'month' => 'nullable|string',
        ]);

        $studentId = $request->input('student_id');
        $feeTypeId = $request->input('fee_type_id');
        $academicYear = $request->input('academic_year');
        $amount = $request->input('amount');
        $month = $request->input('month');

        // Check if already exists
        $existingQuery = StudentFee::where('student_id', $studentId)
            ->where('fee_type_id', $feeTypeId)
            ->where('academic_year', $academicYear);

        if ($month) {
            $existingQuery->where('month', $month);
        } else {
            $existingQuery->whereNull('month');
        }

        if ($existingQuery->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Fee already assigned to this student for this academic year/month',
            ], 422);
        }

        // Get fee type for default amount
        $feeType = \App\Models\FeeType::find($feeTypeId);
        if (!$feeType) {
            return response()->json([
                'success' => false,
                'message' => 'Fee type not found',
            ], 404);
        }

        $studentFee = StudentFee::create([
            'student_id' => $studentId,
            'fee_type_id' => $feeTypeId,
            'academic_year' => $academicYear,
            'month' => $month,
            'amount' => $amount ?? $feeType->amount,
            'is_custom' => $amount !== null,
            'is_active' => true,
            'is_inherited' => false,
            'prorate_percentage' => 100,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fee assigned to student successfully',
            'data' => new StudentFeeResource($studentFee->load(['feeType'])),
        ], 201);
    }
}
