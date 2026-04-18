<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\GradeFee;
use App\Models\StudentFee;
use App\Models\FeeType;
use App\Models\FeePayment;
use App\Models\PaymentRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function generateRegNumber(Request $request): JsonResponse
    {
        $user = $request->user();
        $gradeId = $request->input('grade_id');
        
        $regNumber = Student::generateRegistrationNumber(
            $user->institute_id,
            $gradeId ? (int)$gradeId : null
        );

        return response()->json([
            'success' => true,
            'data' => [
                'registration_number' => $regNumber,
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $sectionId = $request->input('section_id');
        $gradeId = $request->input('grade_id');
        $gender = $request->input('gender');
        $status = $request->input('status');

        $query = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with('section');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('roll_no', 'like', "%{$search}%");
            });
        }

if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        if ($gradeId) {
            $query->whereHas('section', function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            });
        }

        if ($gender) {
            $query->where('gender', $gender);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $students = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => StudentResource::collection($students->items()),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
            ],
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $baseQuery = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        });

        $total = (clone $baseQuery)->count();
        $male = (clone $baseQuery)->where('gender', 'male')->count();
        $female = (clone $baseQuery)->where('gender', 'female')->count();
        $active = (clone $baseQuery)->where('status', 'active')->count();
        $inactive = (clone $baseQuery)->where('status', 'inactive')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'male' => $male,
                'female' => $female,
                'active' => $active,
                'inactive' => $inactive,
            ],
        ]);
    }

    public function store(StudentRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (!$user->isSuperAdmin()) {
            $data['institute_id'] = $user->institute_id;
        }

        $student = Student::create($data);

        // Auto-assign grade fees to new student
        $assignedFees = $this->assignGradeFeesToStudent($student);

        $response = [
            'success' => true,
            'message' => 'Student created successfully',
            'data' => new StudentResource($student),
            'fees_assigned' => $assignedFees,
        ];

        return response()->json($response, 201);
    }

    /**
     * Auto-assign grade fees to a newly enrolled student
     */
    private function assignGradeFeesToStudent(Student $student): array
    {
        if (!$student->section_id) {
            return [];
        }

        // Get student's section to find grade
        $section = $student->section;
        if (!$section) {
            return [];
        }

        $gradeId = $section->grade_id;
        $admissionDate = $student->admission_date ?? now()->toDateString();
        $academicYear = $this->getAcademicYear($admissionDate);

        // Get all grade fees for this grade and academic year
        $gradeFees = GradeFee::where('grade_id', $gradeId)
            ->where('academic_year', $academicYear)
            ->with('feeType')
            ->get();

        if ($gradeFees->isEmpty()) {
            return [];
        }

        // Calculate prorate percentage based on admission date
        $proratePercentage = $this->calculateProratePercentage($admissionDate);

        $assignedFees = [];

        foreach ($gradeFees as $gradeFee) {
            $feeType = $gradeFee->feeType;

            // Skip if fee type doesn't exist
            if (!$feeType) {
                continue;
            }

            // Check if student already has this fee (for one-time fees - lifetime check)
            if ($feeType->type === 'one_time') {
                $alreadyPaid = $this->checkLifetimePayment($student->id, $feeType->id);
                if ($alreadyPaid) {
                    continue; // Skip if already paid in any year
                }
            }

            // Check if student already has this fee assigned for this academic year
            $existingFee = StudentFee::where('student_id', $student->id)
                ->where('fee_type_id', $feeType->id)
                ->where('academic_year', $academicYear)
                ->first();

            if ($existingFee) {
                continue; // Skip if already assigned
            }

            // Calculate amount based on prorate percentage
            $amount = $gradeFee->amount * ($proratePercentage / 100);

            // Create student fee record
            $studentFee = StudentFee::create([
                'student_id' => $student->id,
                'fee_type_id' => $feeType->id,
                'academic_year' => $academicYear,
                'amount' => $amount,
                'is_custom' => false,
                'is_active' => true,
                'is_inherited' => true,
                'inherited_from_grade_fee_id' => $gradeFee->id,
                'prorate_percentage' => $proratePercentage,
                'status' => 'pending',
                'effective_from' => $admissionDate,
            ]);

            $assignedFees[] = [
                'fee_type' => $feeType->name,
                'amount' => $amount,
                'prorate_percentage' => $proratePercentage,
                'reason' => $proratePercentage < 100 ? 'prorated' : 'full',
            ];
        }

        return $assignedFees;
    }

    /**
     * Get academic year based on admission date
     * Academic year runs from June to May (e.g., 2025-2026)
     */
    private function getAcademicYear(string $date): string
    {
        $dateObj = \Carbon\Carbon::parse($date);
        $month = $dateObj->month;
        $year = $dateObj->year;

        // If June or after, academic year is current-year-next
        // If before June, academic year is previous-year-current
        if ($month >= 6) {
            return "{$year}-" . ($year + 1);
        } else {
            return ($year - 1) . "-{$year}";
        }
    }

    /**
     * Calculate prorate percentage based on admission day
     * Before 15th = 100%, 15th or after = 50%
     */
    private function calculateProratePercentage(string $date): float
    {
        $day = (int) date('j', strtotime($date));
        return $day < 15 ? 100.00 : 50.00;
    }

    /**
     * Check if student has paid this fee type in any previous year (lifetime check)
     */
    private function checkLifetimePayment(int $studentId, int $feeTypeId): bool
    {
        // Check payment records that include this fee type
        // Since fee_payments don't directly link to fee_type_id,
        // we need to check student_fees that are linked to payments
        return PaymentRecord::whereHas('feePayment', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->whereHas('studentFee', function ($query) use ($feeTypeId) {
                $query->where('fee_type_id', $feeTypeId);
            })
            ->exists();
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with('section')->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new StudentResource($student),
        ]);
    }

    /**
     * Get all dashboard data for a student in single call
     */
    public function dashboardData(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $month = $request->input('month', now()->format('n'));
        $year = $request->input('year', now()->format('Y'));
        
        // Academic year runs June to June (e.g., Apr 2026 is in 2025-2026 session)
        $currentMonth = now()->month;
        if ($currentMonth >= 6) {
            $defaultAcademicYear = now()->year . '-' . (now()->year + 1);
        } else {
            $defaultAcademicYear = (now()->year - 1) . '-' . now()->year;
        }
        $academicYear = $request->input('academic_year', $defaultAcademicYear);

        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with('section.grade')->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        // Get fees with balance
        $fees = StudentFee::with(['feeType', 'paymentRecords'])
            ->where('student_id', $id)
            ->where('academic_year', $academicYear)
            ->orderBy('created_at', 'desc')
            ->get();

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
                'fee_type' => [
                    'name' => $fee->feeType?->name,
                    'code' => $fee->feeType?->code,
                ],
                'academic_year' => $fee->academic_year,
                'month' => $fee->month,
                'amount' => $feeAmount,
                'paid' => $paidAmount,
                'balance' => $feeAmount - $paidAmount,
                'status' => $fee->status,
            ];
        });

        // Get payments
        $payments = FeePayment::with(['receiver'])
            ->where('student_id', $id)
            ->orderBy('payment_date', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'receipt_number' => $payment->receipt_number,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                    'receiver' => $payment->receiver ? [
                        'first_name' => $payment->receiver->first_name,
                        'last_name' => $payment->receiver->last_name,
                    ] : null,
                ];
            });

        try {
            // Get attendance summary - simplified direct approach
            $monthInt = (int) $month;
            $yearInt = (int) $year;
            $fromDate = $yearInt . '-' . str_pad($monthInt, 2, '0', STR_PAD_LEFT) . '-01';
            $toDate = \Carbon\Carbon::createFromDate($yearInt, $monthInt)->endOfMonth()->format('Y-m-d');
            
            $records = \DB::table('attendance')
                ->where('student_id', $id)
                ->whereBetween('date', [$fromDate, $toDate])
                ->get();
            
            $present = 0; $absent = 0; $late = 0; $excused = 0;
            foreach ($records as $r) {
                if ($r->status === 'present') $present++;
                elseif ($r->status === 'absent') $absent++;
                elseif ($r->status === 'late') $late++;
                elseif ($r->status === 'excused') $excused++;
            }
            $total = $records->count();
        } catch (\Exception $e) {
            \Log::error("Attendance error: " . $e->getMessage());
            $present = 0; $absent = 0; $late = 0; $excused = 0; $total = 0;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'student' => new StudentResource($student),
                'fees' => [
                    'fees' => $feesWithBalance,
                    'summary' => [
                        'total_owed' => $totalOwed,
                        'total_paid' => $totalPaid,
                        'balance' => $totalOwed - $totalPaid,
                    ],
                ],
                'payments' => $payments,
                'attendance' => [
                    'present' => $present,
                    'absent' => $absent,
                    'late' => $late,
                    'excused' => $excused,
                    'total_days' => $total,
                    'present_percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
                ],
            ],
        ]);
    }

    public function update(StudentRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        $data = $request->validated();
        unset($data['institute_id']);

        $student->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data' => new StudentResource($student),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully',
        ]);
    }

    public function enroll(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        $student->update(['status' => 'active']);

        $this->applyOneTimeFees($student);

        return response()->json([
            'success' => true,
            'message' => 'Student enrolled successfully. One-time fees have been applied.',
            'data' => new StudentResource($student->fresh()),
        ]);
    }

    public function assignFeesToAllStudents(Request $request): JsonResponse
    {
        $request->validate([
            'grade_id' => 'required|integer|exists:grades,id',
            'fee_type_id' => 'required|integer|exists:fee_types,id',
            'academic_year' => 'nullable|string',
        ]);

        $user = $request->user();
        $gradeId = (int) $request->grade_id;
        $feeTypeId = (int) $request->fee_type_id;
        $academicYear = $request->academic_year ?? now()->year;

        $gradeFee = GradeFee::where('grade_id', $gradeId)
            ->where('fee_type_id', $feeTypeId)
            ->where('academic_year', $academicYear)
            ->first();

        if (!$gradeFee) {
            return response()->json([
                'success' => false,
                'message' => "Grade fee not found for academic year {$academicYear}",
            ], 404);
        }

        $students = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })
            ->where('status', 'active')
            ->whereHas('section', function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            })
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($students as $student) {
            $existing = StudentFee::where('student_id', $student->id)
                ->where('fee_type_id', $feeTypeId)
                ->where('academic_year', $academicYear)
                ->where('is_active', true)
                ->first();

            if ($existing) {
                $skipped++;
                continue;
            }

            StudentFee::create([
                'student_id' => $student->id,
                'fee_type_id' => $feeTypeId,
                'academic_year' => $academicYear,
                'amount' => $gradeFee->amount,
                'is_custom' => false,
                'is_active' => true,
                'effective_from' => $gradeFee->effective_from,
                'effective_to' => $gradeFee->effective_to,
            ]);

            $created++;
        }

        return response()->json([
            'success' => true,
            'message' => "Fees assigned to {$created} students for academic year {$academicYear}. {$skipped} already had this fee.",
            'data' => [
                'created' => $created,
                'skipped' => $skipped,
                'total' => $students->count(),
            ],
        ]);
    }

    private function applyOneTimeFees(Student $student): void
    {
        if (!$student->section_id) {
            return;
        }

        $gradeId = $student->section->grade_id;
        $academicYear = now()->year;

        $oneTimeGradeFees = GradeFee::where('grade_id', $gradeId)
            ->where('academic_year', $academicYear)
            ->whereHas('feeType', function ($query) {
                $query->where('type', 'one_time');
            })
            ->get();

        foreach ($oneTimeGradeFees as $gradeFee) {
            $existing = StudentFee::where('student_id', $student->id)
                ->where('fee_type_id', $gradeFee->fee_type_id)
                ->where('academic_year', $academicYear)
                ->first();

            if (!$existing) {
                StudentFee::create([
                    'student_id' => $student->id,
                    'fee_type_id' => $gradeFee->fee_type_id,
                    'academic_year' => $academicYear,
                    'amount' => $gradeFee->amount,
                    'is_custom' => false,
                    'is_active' => true,
                    'effective_from' => $gradeFee->effective_from,
                    'effective_to' => $gradeFee->effective_to,
                ]);
            }
        }
    }
}
