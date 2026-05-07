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
        $withFeeSummary = $request->input('with_fee_summary', false);
        $academicYear = $request->input('academic_year');
        $feeStatus = $request->input('fee_status');
        $month = $request->input('month');

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

        // Add fee summary if requested (optimized with SQL aggregation)
        $feeSummaryData = null;
                if ($withFeeSummary) {
            $studentIds = collect($students->items())->pluck('id')->toArray();
            
            if (!empty($studentIds)) {
                $feeQuery = StudentFee::whereIn('student_id', $studentIds);
                if ($academicYear) {
                    $feeQuery->where('academic_year', $academicYear);
                }
                // Filter by month name
                if ($month && strtolower($month) !== 'all') {
                    // Calculate last day of the month being viewed
                    $monthNumber = date('n', strtotime($month));
                    $year = (int) explode('-', $academicYear)[0]; // Extract year from academic year
                    $lastDayOfMonth = date('Y-m-d', mktime(0, 0, 0, $monthNumber + 1, 0, $year));
                    
                    $feeQuery->where(function ($q) use ($month, $lastDayOfMonth) {
                        // Monthly fees for this month
                        $q->where('month', $month)
                           // One-time fees that were effective on or before this month
                           ->orWhere(function ($q2) use ($lastDayOfMonth) {
                               $q2->where(function ($q3) {
                                   $q3->where('month', '')
                                       ->orWhereNull('month');
                               })->where('effective_from', '<=', $lastDayOfMonth);
                           });
                    });
                }
                if ($month && strtolower($month) !== 'all') {
                    // Handle both number (2) and string (February) formats
                    $monthNumber = is_numeric($month) ? (int)$month : (int)date('n', strtotime($month));
                    $feeQuery->where(function ($q) use ($month, $monthNumber) {
                        $q->where('month', $month)
                           ->orWhere('month', (string)$monthNumber);
                    });
                }

                // Get total owed per student - simple query
                $owedPerStudent = $feeQuery
                    ->select('student_id', DB::raw('SUM(amount) as total_owed'))
                    ->groupBy('student_id')
                    ->pluck('total_owed', 'student_id')
                    ->toArray();

                // Get paid via student_fee_payments table - join approach
                $paidPerStudent = DB::table('student_fees as sf')
                    ->join('student_fee_payments as sfp', 'sf.id', '=', 'sfp.student_fee_id')
                    ->whereIn('sf.student_id', $studentIds)
                    ->where(function ($q) use ($academicYear, $month) {
                        if ($academicYear) {
                            $q->where('sf.academic_year', $academicYear);
                        }
                        if ($month && strtolower($month) !== 'all') {
                            $q->where('sf.month', $month);
                        }
                    })
                    ->groupBy('sf.student_id')
                    ->select('sf.student_id', DB::raw('SUM(sfp.amount_applied) as total_paid'))
                    ->pluck('total_paid', 'sf.student_id')
                    ->toArray();

                // Get oldest pending for defaulter status
                $oldestPending = StudentFee::whereIn('student_id', $studentIds)
                    ->where('status', 'pending')
                    ->when($academicYear, fn($q) => $q->where('academic_year', $academicYear))
                    ->when($month && strtolower($month) !== 'all', fn($q) => $q->where('month', $month))
                    ->select('student_id', 'created_at')
                    ->get()
                    ->groupBy('student_id')
                    ->map(fn($records) => $records->sortBy('created_at')->first());

                // Get last payment ID per student
                $lastPaymentIds = DB::table('fee_payments')
                    ->whereIn('student_id', $studentIds)
                    ->when($academicYear, fn($q) => $q->where('academic_year', $academicYear))
                    ->when($month && strtolower($month) !== 'all', fn($q) => $q->where('month', $month))
                    ->orderBy('payment_date', 'desc')
                    ->select('student_id', 'id')
                    ->get()
                    ->groupBy('student_id')
                    ->map(fn($records) => $records->first()?->id)
                    ->toArray();

                $feeSummaryData = [];
                foreach ($studentIds as $studentId) {
                    $totalOwed = floatval($owedPerStudent[$studentId] ?? 0);
                    $totalPaidAmt = floatval($paidPerStudent[$studentId] ?? 0);
                    $balance = $totalOwed - $totalPaidAmt;

                    $feeStatusCalc = 'clear';
                    if ($balance > 0) {
                        $feeStatusCalc = 'pending';
                        $oldest = $oldestPending->get($studentId);
                        if ($oldest && $oldest->created_at) {
                            $daysOverdue = now()->diffInDays(\Carbon\Carbon::parse($oldest->created_at));
                            if ($daysOverdue > 30) {
                                $feeStatusCalc = 'defaulter';
                            }
                        }
                    }

                    $feeSummaryData[$studentId] = [
                        'pending' => $balance,
                        'paid' => $totalPaidAmt,
                        'balance' => $balance,
                        'status' => $feeStatusCalc,
                        'last_payment_id' => $lastPaymentIds[$studentId] ?? null,
                    ];
                }
            }
        }

        $studentData = StudentResource::collection($students->items())->resolve();
        
        // Merge fee summary into response
        if ($withFeeSummary && $feeSummaryData) {
            $studentData = array_map(function ($student) use ($feeSummaryData) {
                $studentId = $student['id'];
                $student['fee_summary'] = $feeSummaryData[$studentId] ?? [
                    'pending' => 0,
                    'paid' => 0,
                    'balance' => 0,
                    'status' => 'clear',
                ];
                return $student;
            }, $studentData);
        }

        return response()->json([
            'success' => true,
            'data' => $studentData,
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
            ],
        ]);
    }

    /**
     * V2: Lightweight student list - returns only essential fields
     */
    public function indexV2(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $sectionId = $request->input('section_id');
        $gradeId = $request->input('grade_id');
        $status = $request->input('status');

        $query = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->select('id', 'first_name', 'last_name', 'registration_number', 'roll_no', 'section_id', 'status', 'gender');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%");
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

        if ($status) {
            $query->where('status', $status);
        }

        $students = $query->with('section:id,name,grade_id')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $students->items(),
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

        $data['admission_date'] = $data['registration_date'];

        $student = Student::create($data);

        // Auto-assign grade fees to new student
        $assignedFees = $this->assignGradeFeesToStudent($student);

        $feeCount = count($assignedFees);
        $message = $feeCount > 0 
            ? "Student created successfully. {$feeCount} fee(s) have been assigned (one-time, annual, and monthly)."
            : 'Student created successfully. No fees were assigned - please configure grade fees first.';

        $response = [
            'success' => true,
            'message' => $message,
            'data' => new StudentResource($student),
            'fees_assigned' => $assignedFees,
        ];

        return response()->json($response, 201);
    }

    /**
     * Auto-assign grade fees to a newly enrolled student using FeeSchedule
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

        // Get all active fee schedules for this grade
        $schedules = \App\Models\FeeSchedule::where('institute_id', $student->institute_id)
            ->where('grade_id', $gradeId)
            ->where('is_active', true)
            ->get();

        if ($schedules->isEmpty()) {
            return [];
        }

        $assignedFees = [];

        foreach ($schedules as $schedule) {
            // Check if schedule applies to this student's fee category
            if ($schedule->fee_category_id && $schedule->fee_category_id != $student->fee_category_id) {
                continue; // Skip - doesn't apply to this student
            }

            // Generate student fees for the entire academic year
            $fees = $schedule->generateStudentFees($student->id, $admissionDate, $academicYear);

            foreach ($fees as $feeData) {
                // Check if already exists
                $exists = \App\Models\StudentFee::where('student_id', $student->id)
                    ->where('fee_type_id', $feeData['fee_type_id'])
                    ->where('month', $feeData['month'])
                    ->where('academic_year', $feeData['academic_year'])
                    ->exists();

                if (!$exists) {
                    \App\Models\StudentFee::create($feeData);
                    
                    $feeType = \App\Models\FeeType::find($feeData['fee_type_id']);
                    $assignedFees[] = [
                        'fee_type' => $feeType?->name ?? 'Unknown',
                        'amount' => $feeData['amount'],
                        'month' => $feeData['month'],
                        'frequency' => $schedule->frequency,
                    ];
                }
            }
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
     * Edit form prefill - returns only core student fields (no fees/payments/attendance)
     */
    public function edit(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->select(
            'id', 'first_name', 'last_name', 'email', 'registration_date',
            'registration_number', 'roll_no', 'gender', 'mobile_number',
            'parents_name', 'parents_mobile_number', 'date_of_birth',
            'blood_group', 'address', 'institute_id', 'section_id',
            'admission_date', 'fee_category_id', 'status', 'created_at', 'updated_at'
        )->with(['section:id,name,grade_id', 'feeCategory:id,name,code'])->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'email' => $student->email,
                'registration_date' => $student->registration_date,
                'registration_number' => $student->registration_number,
                'roll_no' => $student->roll_no,
                'gender' => $student->gender,
                'mobile_number' => $student->mobile_number,
                'parents_name' => $student->parents_name,
                'parents_mobile_number' => $student->parents_mobile_number,
                'date_of_birth' => $student->date_of_birth,
                'blood_group' => $student->blood_group,
                'address' => $student->address,
                'section_id' => $student->section_id,
                'admission_date' => $student->admission_date,
                'fee_category_id' => $student->fee_category_id,
                'status' => $student->status,
                'section' => $student->section,
                'fee_category' => $student->feeCategory,
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        // Accept month filter (name or number)
        $monthInput = $request->input('month', 'all');
        $month = is_numeric($monthInput) 
            ? date('F', mktime(0, 0, 0, (int)$monthInput, 1))
            : $monthInput;
        
        $year = $request->input('year', now()->format('Y'));
        $academicYear = $request->input('academic_year', $this->getAcademicYear(now()->toDateString()));

        // Load student
        $student = Student::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with(['section.grade', 'feeCategory'])->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        // Get fees with balance
        $feesQuery = StudentFee::with(['feeType', 'feeSchedule', 'paymentRecords'])
            ->where('student_id', $id)
            ->where('academic_year', $academicYear);
        
        // Filter by month if not 'all'
        if (strtolower($month) !== 'all') {
            $feesQuery->where(function ($q) use ($month) {
                $q->where('month', $month)
                   ->orWhere(function ($q2) {
                       $q2->where('month', '')
                           ->orWhereNull('month');
                   });
            });
        }

        $fees = $feesQuery->orderBy('created_at', 'desc')->get();

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
                    'type' => $fee->feeType?->type,
                ],
                'fee_schedule_id' => $fee->fee_schedule_id,
                'academic_year' => $fee->academic_year,
                'month' => $fee->month,
                'amount' => $feeAmount,
                'paid' => $paidAmount,
                'balance' => $feeAmount - $paidAmount,
                'status' => $fee->status,
                'effective_from' => $fee->effective_from?->format('Y-m-d'),
            ];
        });

        // Get payments
        $payments = \App\Models\FeePayment::with(['receiver'])
            ->where('student_id', $id)
            ->orderBy('payment_date', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'receipt_number' => $payment->receipt_number,
                    'amount' => (float) $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                    'bank_reference' => $payment->bank_reference,
                    'receiver' => $payment->receiver ? [
                        'first_name' => $payment->receiver->first_name,
                        'last_name' => $payment->receiver->last_name,
                    ] : null,
                ];
            });

        // Attendance summary
        try {
            $monthNum = (int) date('n', strtotime($month));
            $yearInt = (int) $year;
            $fromDate = $yearInt . '-' . str_pad($monthNum, 2, '0', STR_PAD_LEFT) . '-01';
            $toDate = \Carbon\Carbon::create($yearInt, $monthNum)->endOfMonth()->format('Y-m-d');

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
                    'month' => $month,
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

    /**
     * @deprecated Use GET /students/{id} with query params instead
     */
    public function dashboardData(Request $request, int $id): JsonResponse
    {
        return $this->show($request, $id);
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
        unset($data['institute_id'], $data['registration_date']);

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

        // Update fee_category_id if provided
        if ($request->has('fee_category_id')) {
            $student->update(['fee_category_id' => $request->fee_category_id]);
        }

        $student->update(['status' => 'active']);

        $this->applyEnrollmentFees($student);

        return response()->json([
            'success' => true,
            'message' => 'Student enrolled successfully. All applicable fees (one-time, annual, and monthly) have been applied.',
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

    private function applyEnrollmentFees(Student $student): void
    {
        if (!$student->section_id) {
            return;
        }

        $gradeId = $student->section->grade_id;
        $academicYear = $this->getAcademicYear(now()->toDateString());
        $enrollmentDate = $student->admission_date ?? now()->toDateString();

        // Get all active fee schedules for this grade
        $schedules = \App\Models\FeeSchedule::where('institute_id', $student->institute_id)
            ->where('grade_id', $gradeId)
            ->where('is_active', true)
            ->get();

        foreach ($schedules as $schedule) {
            // Check if schedule applies to this student's fee category
            if ($schedule->fee_category_id && $schedule->fee_category_id != $student->fee_category_id) {
                continue; // Skip - doesn't apply to this student
            }

            // Generate student fees for the entire academic year
            $fees = $schedule->generateStudentFees($student->id, $enrollmentDate, $academicYear);

            foreach ($fees as $feeData) {
                // Check if already exists
                $exists = \App\Models\StudentFee::where('student_id', $student->id)
                    ->where('fee_type_id', $feeData['fee_type_id'])
                    ->where('month', $feeData['month'])
                    ->where('academic_year', $feeData['academic_year'])
                    ->exists();

                if (!$exists) {
                    \App\Models\StudentFee::create($feeData);
                }
            }
        }
    }
}
