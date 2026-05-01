<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeFeeRequest;
use App\Http\Resources\GradeFeeResource;
use App\Models\GradeFee;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\FeeType;
use App\Models\FeePayment;
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
        
        // If academic_year not provided, use institute's current_academic_year
        if (empty($data['academic_year'])) {
            $grade = \App\Models\Grade::find($data['grade_id']);
            if ($grade && $grade->institute) {
                $data['academic_year'] = $grade->institute->current_academic_year ?? $this->calculateAcademicYear();
            } else {
                $data['academic_year'] = $this->calculateAcademicYear();
            }
        }
        
        $gradeFee = GradeFee::create($data);
        $gradeFee->load(['grade', 'feeType']);

        return response()->json([
            'success' => true,
            'message' => 'Fee assigned to grade successfully',
            'data' => new GradeFeeResource($gradeFee),
        ], 201);
    }

    /**
     * Calculate current academic year based on current date
     * Assumes academic year runs from April to March
     */
    private function calculateAcademicYear(): string
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        
        if ($currentMonth >= 4) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
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

        $hasStudentFees = \App\Models\StudentFee::where('inherited_from_grade_fee_id', $id)->exists();

        if ($hasStudentFees) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete: fee has been assigned to students. Clear student fees first.',
            ], 422);
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
                // If academic_year not provided, get it from institute's current_academic_year
                if (empty($feeData['academic_year'])) {
                    $grade = \App\Models\Grade::find($feeData['grade_id']);
                    if ($grade && $grade->institute) {
                        $feeData['academic_year'] = $grade->institute->current_academic_year ?? $this->calculateAcademicYear();
                    } else {
                        $feeData['academic_year'] = $this->calculateAcademicYear();
                    }
                }
                
                $gradeFee = GradeFee::create([
                    'grade_id' => $feeData['grade_id'],
                    'fee_type_id' => $feeData['fee_type_id'],
                    'academic_year' => $feeData['academic_year'],
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

        // Auto-assign to existing students in the same grade
        if (!empty($created)) {
            $autoAssigned = $this->autoAssignToStudents($created);
        }

        return response()->json([
            'success' => count($errors) === 0,
            'message' => count($created) . ' grade fee(s) created',
            'data' => GradeFeeResource::collection($created),
            'created_count' => count($created),
            'auto_assigned_count' => $autoAssigned ?? 0,
            'errors' => $errors,
        ]);
    }

    /**
     * Auto-assign newly created grade fees to existing students
     */
    private function autoAssignToStudents(array $gradeFees): int
    {
        $assignedCount = 0;
        $currentMonth = date('n'); // 1-12

        // Group grade fees by grade
        $groupedByGrade = collect($gradeFees)->groupBy('grade_id');

        foreach ($groupedByGrade as $gradeId => $fees) {
            $academicYear = $fees->first()->academic_year;

            // Get students in this grade
            $sectionIds = \App\Models\Section::where('grade_id', $gradeId)->pluck('id')->toArray();
            if (empty($sectionIds)) continue;

            $students = Student::whereIn('section_id', $sectionIds)
                ->where('status', 'active')
                ->get();

            if ($students->isEmpty()) continue;

            foreach ($students as $student) {
                foreach ($fees as $gradeFee) {
                    $feeType = $gradeFee->feeType;
                    if (!$feeType) continue;

                    // Determine target month(s)
                    $targetMonths = [null];
                    if ($feeType->type === 'monthly') {
                        $targetMonths = [(string)$currentMonth];
                    }

                    foreach ($targetMonths as $month) {
                        // For one-time fees, check lifetime payment
                        if ($feeType->type === 'one_time') {
                            if ($this->checkLifetimePayment($student->id, $feeType->id)) {
                                continue;
                            }
                        }

                        // Check if already exists
                        $existingQuery = StudentFee::where('student_id', $student->id)
                            ->where('fee_type_id', $feeType->id)
                            ->where('academic_year', $academicYear);
                        
                        if ($month !== null) {
                            $existingQuery->where('month', $month);
                        } else {
                            $existingQuery->whereNull('month');
                        }

                        if ($existingQuery->exists()) {
                            continue;
                        }

                        // Always assign full fee (100%) - no mid-month discount
                        $proratePercentage = 100;

                        $amount = $gradeFee->amount;

                        try {
                            StudentFee::create([
                                'student_id' => $student->id,
                                'fee_type_id' => $feeType->id,
                                'academic_year' => $academicYear,
                                'month' => $month,
                                'amount' => $amount,
                                'is_custom' => false,
                                'is_active' => true,
                                'is_inherited' => true,
                                'inherited_from_grade_fee_id' => $gradeFee->id,
                                'prorate_percentage' => $proratePercentage,
                                'status' => 'pending',
                                'effective_from' => $gradeFee->effective_from,
                                'effective_to' => $gradeFee->effective_to,
                            ]);
                            $assignedCount++;
                        } catch (\Exception $e) {
                            // Skip duplicate or error
                        }
                    }
                }
            }
        }

        return $assignedCount;
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

        $idsWithStudentFees = \App\Models\StudentFee::whereIn('inherited_from_grade_fee_id', $ids)
            ->distinct()
            ->pluck('inherited_from_grade_fee_id')
            ->toArray();

        if (!empty($idsWithStudentFees)) {
            $gradeFees = GradeFee::whereIn('id', $idsWithStudentFees)->get()->map(fn($gf) => $gf->feeType?->name ?? 'Unknown');
            
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete: following fees have been assigned to students. Clear student fees first: ' . $gradeFees->implode(', '),
                'blocked_ids' => $idsWithStudentFees,
            ], 422);
        }

        $deleted = GradeFee::destroy($ids);

        return response()->json([
            'success' => true,
            'message' => $deleted . ' grade fee(s) deleted',
            'deleted_count' => $deleted,
        ]);
    }

    /**
     * Assign grade fees to existing students in a grade
     */
    public function assignToStudents(Request $request, int $gradeId): JsonResponse
    {
        $request->validate([
            'academic_year' => 'required|string',
            'grade_fee_ids' => 'nullable|array',
            'apply_to_existing' => 'boolean',
            'months' => 'nullable|array',
        ]);

        $academicYear = $request->input('academic_year');
        $gradeFeeIds = $request->input('grade_fee_ids', []);
        $applyToExisting = $request->input('apply_to_existing', true);
        $months = $request->input('months', []);

        if (!$applyToExisting) {
            return response()->json([
                'success' => true,
                'message' => 'No changes made',
                'data' => [
                    'assigned_count' => 0,
                    'skipped_count' => 0,
                ]
            ]);
        }

        // Get students in this grade
        $sectionIds = \App\Models\Section::where('grade_id', $gradeId)->pluck('id')->toArray();
        
        if (empty($sectionIds)) {
            return response()->json([
                'success' => true,
                'message' => 'No sections found for this grade',
                'data' => [
                    'assigned_count' => 0,
                    'skipped_count' => 0,
                ]
            ]);
        }

        $students = Student::whereIn('section_id', $sectionIds)
            ->where('status', 'active')
            ->get();

        if ($students->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No students found in this grade',
                'data' => [
                    'assigned_count' => 0,
                    'skipped_count' => 0,
                ]
            ]);
        }

        // Get grade fees
        $gradeFeesQuery = GradeFee::where('grade_id', $gradeId)
            ->where('academic_year', $academicYear)
            ->with('feeType');

        if (!empty($gradeFeeIds)) {
            $gradeFeesQuery->whereIn('id', $gradeFeeIds);
        }

        $gradeFees = $gradeFeesQuery->get();

        if ($gradeFees->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No grade fees found',
                'data' => [
                    'assigned_count' => 0,
                    'skipped_count' => 0,
                ]
            ]);
        }

        $assignedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($students as $student) {
            foreach ($gradeFees as $gradeFee) {
                $feeType = $gradeFee->feeType;

                if (!$feeType) {
                    continue;
                }

                // Branching logic based on fee type
                $targetMonths = [null]; // Default for one_time and annual
                if ($feeType->type === 'monthly') {
                    $targetMonths = !empty($months) ? $months : [date('F')]; // Default to current month if none provided
                }

                foreach ($targetMonths as $month) {
                    // For one-time fees, check lifetime payment
                    if ($feeType->type === 'one_time') {
                        $alreadyPaid = $this->checkLifetimePayment($student->id, $feeType->id);
                        if ($alreadyPaid) {
                            $skippedCount++;
                            continue;
                        }
                    }

                    // Check if already assigned for this academic year (and month if applicable)
                    $existingFeeQuery = StudentFee::where('student_id', $student->id)
                        ->where('fee_type_id', $feeType->id)
                        ->where('academic_year', $academicYear);
                    
                    if ($month !== null) {
                        $existingFeeQuery->where('month', $month);
                    } else {
                        $existingFeeQuery->whereNull('month');
                    }

                    if ($existingFeeQuery->exists()) {
                        $skippedCount++;
                        continue;
                    }

                    try {
                        StudentFee::create([
                            'student_id' => $student->id,
                            'fee_type_id' => $feeType->id,
                            'academic_year' => $academicYear,
                            'month' => $month,
                            'amount' => $gradeFee->amount,
                            'is_custom' => false,
                            'is_active' => true,
                            'is_inherited' => true,
                            'inherited_from_grade_fee_id' => $gradeFee->id,
                            'prorate_percentage' => 100,
                            'status' => 'pending',
                            'effective_from' => $gradeFee->effective_from,
                            'effective_to' => $gradeFee->effective_to,
                        ]);
                        $assignedCount++;
                    } catch (\Exception $e) {
                        $errors[] = [
                            'student_id' => $student->id,
                            'fee_type_id' => $feeType->id,
                            'month' => $month,
                            'error' => $e->getMessage(),
                        ];
                    }
                }
            }
        }

        return response()->json([
            'success' => count($errors) === 0,
            'message' => "Assigned: {$assignedCount}, Skipped: {$skippedCount}",
            'data' => [
                'assigned_count' => $assignedCount,
                'skipped_count' => $skippedCount,
                'errors' => $errors,
            ]
        ]);
    }

    /**
     * Check if student has paid this fee type in any previous year (lifetime check)
     */
    private function checkLifetimePayment(int $studentId, int $feeTypeId): bool
    {
        return \App\Models\PaymentRecord::whereHas('feePayment', function ($query) use ($studentId) {
                $query->where('student_id', $studentId);
            })
            ->whereHas('studentFee', function ($query) use ($feeTypeId) {
                $query->where('fee_type_id', $feeTypeId);
            })
            ->exists();
    }

    /**
     * Rollover grade fees from one academic year to another
     * Copies all grade fee assignments from the source year to the target year
     */
    public function rollover(Request $request): JsonResponse
    {
        $request->validate([
            'from_academic_year' => 'required|string',
            'to_academic_year' => 'required|string|different:from_academic_year',
            'grade_ids' => 'nullable|array',
            'grade_ids.*' => 'integer|exists:grades,id',
            'assign_to_students' => 'boolean',
        ]);

        $fromYear = $request->input('from_academic_year');
        $toYear = $request->input('to_academic_year');
        $gradeIds = $request->input('grade_ids', []);
        $assignToStudents = $request->input('assign_to_students', false);

        // Build query for source grade fees
        $sourceFeesQuery = GradeFee::where('academic_year', $fromYear);
        
        if (!empty($gradeIds)) {
            $sourceFeesQuery->whereIn('grade_id', $gradeIds);
        }

        $sourceFees = $sourceFeesQuery->with('feeType')->get();

        if ($sourceFees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "No grade fees found for academic year {$fromYear}",
            ], 404);
        }

        $created = 0;
        $skipped = 0;
        $studentFeesCreated = 0;
        $errors = [];

        foreach ($sourceFees as $sourceFee) {
            try {
                // Check if already exists for target year
                $exists = GradeFee::where('grade_id', $sourceFee->grade_id)
                    ->where('fee_type_id', $sourceFee->fee_type_id)
                    ->where('academic_year', $toYear)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Create new grade fee for target year
                $newGradeFee = GradeFee::create([
                    'grade_id' => $sourceFee->grade_id,
                    'fee_type_id' => $sourceFee->fee_type_id,
                    'academic_year' => $toYear,
                    'amount' => $sourceFee->amount,
                    'effective_from' => $sourceFee->effective_from,
                    'effective_to' => $sourceFee->effective_to,
                ]);

                $created++;

                // Optionally assign to existing students
                if ($assignToStudents && $newGradeFee->feeType) {
                    $studentFeesCreated += $this->assignFeeToStudents(
                        $newGradeFee,
                        $sourceFee->grade_id,
                        $toYear
                    );
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'grade_id' => $sourceFee->grade_id,
                    'fee_type_id' => $sourceFee->fee_type_id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => count($errors) === 0,
            'message' => "Rollover complete: {$created} created, {$skipped} skipped",
            'data' => [
                'grade_fees_created' => $created,
                'grade_fees_skipped' => $skipped,
                'student_fees_created' => $studentFeesCreated,
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Assign a specific grade fee to all students in the grade
     */
    private function assignFeeToStudents(GradeFee $gradeFee, int $gradeId, string $academicYear): int
    {
        $feeType = $gradeFee->feeType;
        if (!$feeType) {
            return 0;
        }

        // Get students in this grade
        $sectionIds = \App\Models\Section::where('grade_id', $gradeId)->pluck('id')->toArray();
        if (empty($sectionIds)) {
            return 0;
        }

        $students = Student::whereIn('section_id', $sectionIds)
            ->where('status', 'active')
            ->get();

        $assignedCount = 0;
        $currentMonth = date('n');

        foreach ($students as $student) {
            $targetMonths = [null];
            if ($feeType->type === 'monthly') {
                $targetMonths = [(string)$currentMonth];
            }

            foreach ($targetMonths as $month) {
                // Check if already assigned
                $existingQuery = StudentFee::where('student_id', $student->id)
                    ->where('fee_type_id', $feeType->id)
                    ->where('academic_year', $academicYear);
                
                if ($month !== null) {
                    $existingQuery->where('month', $month);
                } else {
                    $existingQuery->whereNull('month');
                }

                if ($existingQuery->exists()) {
                    continue;
                }

                // Check lifetime payment for one_time fees
                if ($feeType->type === 'one_time') {
                    if ($this->checkLifetimePayment($student->id, $feeType->id)) {
                        continue;
                    }
                }

                try {
                    StudentFee::create([
                        'student_id' => $student->id,
                        'fee_type_id' => $feeType->id,
                        'academic_year' => $academicYear,
                        'month' => $month,
                        'amount' => $gradeFee->amount,
                        'is_custom' => false,
                        'is_active' => true,
                        'is_inherited' => true,
                        'inherited_from_grade_fee_id' => $gradeFee->id,
                        'prorate_percentage' => 100,
                        'status' => 'pending',
                        'effective_from' => $gradeFee->effective_from,
                        'effective_to' => $gradeFee->effective_to,
                    ]);
                    $assignedCount++;
                } catch (\Exception $e) {
                    // Skip duplicates
                }
            }
        }

        return $assignedCount;
    }

    /**
     * Get students without a specific fee type for a grade
     */
    public function getStudentsWithoutFee(Request $request, int $gradeId): JsonResponse
    {
        $request->validate([
            'fee_type_id' => 'required|integer',
            'academic_year' => 'required|string',
        ]);

        $feeTypeId = $request->input('fee_type_id');
        $academicYear = $request->input('academic_year');

        // Get all students in this grade
        $sectionIds = \App\Models\Section::where('grade_id', $gradeId)->pluck('id')->toArray();
        
        if (empty($sectionIds)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_students' => 0,
                    'students_without_fee' => 0,
                    'students_with_fee' => 0,
                    'will_be_created' => 0,
                    'will_be_skipped' => 0,
                ],
            ]);
        }

        $studentsInGrade = Student::whereIn('section_id', $sectionIds)
            ->where('status', 'active')
            ->get();

        $totalStudents = $studentsInGrade->count();

        // Get students who already have this fee
        $studentsWithFee = StudentFee::where('fee_type_id', $feeTypeId)
            ->where('academic_year', $academicYear)
            ->pluck('student_id')
            ->toArray();

        $studentsWithoutFee = $studentsInGrade->filter(function ($student) use ($studentsWithFee) {
            return !in_array($student->id, $studentsWithFee);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total_students' => $totalStudents,
                'students_without_fee' => $studentsWithoutFee->count(),
                'students_with_fee' => $totalStudents - $studentsWithoutFee->count(),
                'will_be_created' => $studentsWithoutFee->count(),
                'will_be_skipped' => $totalStudents - $studentsWithoutFee->count(),
            ],
        ]);
    }
}
