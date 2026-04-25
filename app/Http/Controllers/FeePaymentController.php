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
        $data['receipt_number'] = FeePayment::generateReceiptNumber();
        $data['barcode_value'] = $data['receipt_number'];
        $data['bank_reference'] = FeePayment::generateBankReference();

        $payment = FeePayment::create($data);
        $payment->load(['student', 'receiver', 'bankAccount']);

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'data' => new FeePaymentResource($payment),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $payment = FeePayment::with(['student', 'receiver', 'paymentRecords.studentFee.feeType', 'bankAccount'])->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        if (!$user->isSuperAdmin() && $payment->student->institute_id !== $user->institute_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new FeePaymentResource($payment),
        ]);
    }

    public function receipt(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $payment = FeePayment::with([
            'student',
            'student.section.grade',
            'receiver',
            'paymentRecords.studentFee.feeType',
            'bankAccount',
        ])->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        if (!$user->isSuperAdmin() && $payment->student->institute_id !== $user->institute_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $institute = $payment->student->institute;

        $receipt = [
            'receipt_number' => $payment->receipt_number,
            'barcode_value' => $payment->barcode_value,
            'payment_date' => $payment->payment_date->format('d-M-Y'),
            'payment_date_raw' => $payment->payment_date->toDateString(),
            'bank_reference' => $payment->bank_reference,
            'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method)),
            'amount' => (float) $payment->amount,
            'amount_in_words' => $this->numberToWords($payment->amount),
            'student' => [
                'id' => $payment->student->id,
                'name' => $payment->student->first_name . ' ' . $payment->student->last_name,
                'registration_number' => $payment->student->registration_number,
                'grade' => $payment->student->section->grade->name ?? 'N/A',
                'section' => $payment->student->section->name ?? 'N/A',
                'father_name' => $payment->student->parents_name ?? 'N/A',
            ],
            'institute' => [
                'name' => $institute->name,
                'logo' => $institute->logo,
                'address' => $institute->address,
                'phone' => $institute->contact_phone,
                'email' => $institute->contact_email,
            ],
            'bank_account' => $payment->bankAccount ? [
                'bank_name' => $payment->bankAccount->bank_name,
                'account_title' => $payment->bankAccount->account_title,
                'account_number' => $payment->bankAccount->account_number,
                'branch_code' => $payment->bankAccount->branch_code,
                'branch_address' => $payment->bankAccount->branch_address,
            ] : null,
            'fees' => $payment->paymentRecords->map(function ($record) {
                return [
                    'fee_type' => $record->studentFee->feeType->name ?? 'Fee',
                    'amount' => (float) $record->amount,
                ];
            }),
            'received_by' => $payment->receiver ? $payment->receiver->name : 'System',
            'month' => $payment->month,
            'academic_year' => $payment->academic_year,
        ];

        return response()->json([
            'success' => true,
            'data' => $receipt,
        ]);
    }

    private function numberToWords(float $number): string
    {
        $ones = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
            5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen',
            14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
            18 => 'Eighteen', 19 => 'Nineteen'
        ];
        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        ];

        $num = (int) $number;
        $dec = (int) (($number - $num) * 100);

        if ($num == 0) return 'Zero';

        if ($num < 20) {
            $result = $ones[$num];
        } elseif ($num < 100) {
            $result = $tens[floor($num / 10)];
            if ($num % 10) $result .= ' ' . $ones[$num % 10];
        } else {
            $result = $ones[floor($num / 100)] . ' Hundred';
            if ($num % 100) {
                $result .= ' ' . $this->numberToWords($num % 100);
            }
        }

        if ($dec > 0) {
            $result .= ' and ' . $dec . '/100';
        }

        return $result . ' Rupees Only';
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

        $query = Student::with(['section.grade', 'studentFees.feeType', 'studentDiscounts.discount'])
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

            foreach ($student->studentFees as $studentFee) {
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

    public function studentPayments(Request $request, int $studentId): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 20);

        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($studentId);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        $payments = FeePayment::with(['receiver', 'bankAccount', 'paymentRecords.studentFee.feeType'])
            ->where('student_id', $studentId)
            ->orderBy('payment_date', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => FeePaymentResource::collection($payments->items()),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    public function bulkReceipts(Request $request): JsonResponse
    {
        $user = $request->user();
        $gradeId = $request->input('grade_id');
        $month = $request->input('month');
        $academicYear = $request->input('academic_year');

        if (!$gradeId) {
            return response()->json([
                'success' => false,
                'message' => 'Grade ID is required',
            ], 422);
        }

        $query = FeePayment::with([
            'student',
            'student.section.grade',
            'receiver',
            'paymentRecords.studentFee.feeType',
            'bankAccount',
        ]);

        if (!$user->isSuperAdmin()) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        }

        if ($gradeId) {
            $query->whereHas('student.section.grade', function ($q) use ($gradeId) {
                $q->where('id', $gradeId);
            });
        }

        if ($month) {
            $query->where('month', $month);
        }

        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        // Format receipts for each payment
        $receipts = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'receipt_number' => $payment->receipt_number,
                'barcode_value' => $payment->barcode_value,
                'payment_date' => $payment->payment_date->format('d-M-Y'),
                'payment_date_raw' => $payment->payment_date->toDateString(),
                'bank_reference' => $payment->bank_reference,
                'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method)),
                'amount' => (float) $payment->amount,
                'amount_in_words' => $this->numberToWords($payment->amount),
                'student' => [
                    'id' => $payment->student->id,
                    'name' => $payment->student->first_name . ' ' . $payment->student->last_name,
                    'registration_number' => $payment->student->registration_number,
                    'grade' => $payment->student->section->grade->name ?? 'N/A',
                    'section' => $payment->student->section->name ?? 'N/A',
                    'father_name' => $payment->student->parents_name ?? 'N/A',
                ],
                'institute' => [
                    'name' => $payment->student->institute->name,
                    'logo' => $payment->student->institute->logo,
                    'address' => $payment->student->institute->address,
                    'phone' => $payment->student->institute->contact_phone,
                    'email' => $payment->student->institute->contact_email,
                ],
                'bank_account' => $payment->bankAccount ? [
                    'bank_name' => $payment->bankAccount->bank_name,
                    'account_title' => $payment->bankAccount->account_title,
                    'account_number' => $payment->bankAccount->account_number,
                    'branch_code' => $payment->bankAccount->branch_code,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $receipts,
            'meta' => [
                'total' => $payments->count(),
                'grade_id' => $gradeId,
                'month' => $month,
                'academic_year' => $academicYear,
            ],
        ]);
    }
}
