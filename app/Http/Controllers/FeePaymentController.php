<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeePaymentRequest;
use App\Http\Resources\FeePaymentResource;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\GradeFee;
use App\Models\StudentFee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FeePaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $request->input('student_id');

        $query = FeePayment::with(['student', 'receiver']);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        if (!$user->isSuperAdmin()) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => FeePaymentResource::collection($payments),
        ]);
    }

    public function store(FeePaymentRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $data['received_by'] = $user->id;

        if (empty($data['receipt_number'])) {
            $data['receipt_number'] = 'RCP-' . strtoupper(Str::random(8));
        }

        $payment = FeePayment::create($data);
        $payment->load(['student', 'receiver']);

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'data' => new FeePaymentResource($payment),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $payment = FeePayment::with(['student', 'receiver', 'paymentRecords.studentFee.feeType'])->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new FeePaymentResource($payment),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $payment = FeePayment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully',
        ]);
    }

    public function defaulters(Request $request): JsonResponse
    {
        $user = $request->user();
        $month = $request->input('month', Carbon::now()->format('m'));
        $academicYear = $request->input('academic_year', Carbon::now()->year);
        $gradeId = $request->input('grade_id');

        $query = Student::with(['section.grade', 'fees.feeType', 'discounts.discount'])
            ->where('institute_id', $user->institute_id)
            ->whereNotNull('section_id');

        if ($gradeId) {
            $query->whereHas('section', function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            });
        }

        $students = $query->get();
        $defaulters = [];
        $totalDue = 0;

        foreach ($students as $student) {
            $totalFees = 0;
            $totalPaid = 0;
            $dueFees = [];

            foreach ($student->fees as $studentFee) {
                if (!$studentFee->is_active) continue;
                
                $feeAmount = $studentFee->amount;
                $paidAmount = FeePayment::where('student_id', $student->id)
                    ->where('month', $month)
                    ->where('academic_year', $academicYear)
                    ->sum('amount');

                if ($paidAmount < $feeAmount) {
                    $dueAmount = $feeAmount - $paidAmount;
                    $totalDue += $dueAmount;
                    $dueFees[] = [
                        'fee_type' => $studentFee->feeType->name ?? 'Fee',
                        'amount' => $dueAmount,
                        'due_months' => [$month],
                    ];
                }
            }

            if (count($dueFees) > 0) {
                $defaulters[] = [
                    'student_id' => $student->id,
                    'student_name' => $student->first_name . ' ' . $student->last_name,
                    'registration_number' => $student->registration_number ?? 'N/A',
                    'section_name' => $student->section->grade->name . ' - ' . $student->section->name,
                    'total_due' => array_sum(array_column($dueFees, 'amount')),
                    'due_fees' => $dueFees,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $defaulters,
            'summary' => [
                'total_students' => $students->count(),
                'total_defaulters' => count($defaulters),
                'total_due_amount' => $totalDue,
            ],
        ]);
    }
}
