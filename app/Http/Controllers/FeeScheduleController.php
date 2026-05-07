<?php

namespace App\Http\Controllers;

use App\Models\FeeSchedule;
use App\Models\FeeType;
use App\Models\Grade;
use App\Models\FeeCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FeeScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $schedules = FeeSchedule::where('institute_id', $user->institute_id)
            ->with(['grade', 'feeType', 'feeCategory'])
            ->when($request->has('grade_id'), function ($q) use ($request) {
                return $q->where('grade_id', $request->grade_id);
            })
            ->when($request->has('fee_type_id'), function ($q) use ($request) {
                return $q->where('fee_type_id', $request->fee_type_id);
            })
            ->when($request->has('fee_category_id'), function ($q) use ($request) {
                return $q->where('fee_category_id', $request->fee_category_id);
            })
            ->when($request->has('frequency'), function ($q) use ($request) {
                return $q->where('frequency', $request->frequency);
            })
            ->when($request->has('is_active'), function ($q) use ($request) {
                return $q->where('is_active', $request->boolean('is_active'));
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => \App\Http\Resources\FeeScheduleResource::collection($schedules),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'fee_type_id' => 'required|exists:fee_types,id',
            'fee_category_id' => 'nullable|exists:fee_categories,id',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,quarterly,annual,one_time',
            'applicable_from' => 'required|date',
            'applicable_to' => 'nullable|date|after:applicable_from',
        ]);

        // Verify grade belongs to user's institute
        $grade = Grade::where('institute_id', $user->institute_id)
            ->findOrFail($validated['grade_id']);

        $validated['institute_id'] = $user->institute_id;

        $schedule = FeeSchedule::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fee schedule created successfully',
            'data' => $schedule->load(['grade', 'feeType', 'feeCategory']),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = request()->user();
        
        $schedule = FeeSchedule::where('institute_id', $user->institute_id)
            ->with(['grade', 'feeType', 'feeCategory'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new \App\Http\Resources\FeeScheduleResource($schedule),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $schedule = FeeSchedule::where('institute_id', $user->institute_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'grade_id' => 'sometimes|exists:grades,id',
            'fee_type_id' => 'sometimes|exists:fee_types,id',
            'fee_category_id' => 'nullable|exists:fee_categories,id',
            'amount' => 'sometimes|numeric|min:0',
            'frequency' => 'sometimes|in:monthly,quarterly,annual,one_time',
            'applicable_from' => 'sometimes|date',
            'applicable_to' => 'nullable|date|after:applicable_from',
            'is_active' => 'sometimes|boolean',
        ]);

        $schedule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fee schedule updated successfully',
            'data' => $schedule->fresh()->load(['grade', 'feeType', 'feeCategory']),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = request()->user();
        
        $schedule = FeeSchedule::where('institute_id', $user->institute_id)
            ->findOrFail($id);

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fee schedule deleted successfully',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:fee_schedules,id',
        ]);

        $deleted = FeeSchedule::where('institute_id', $user->institute_id)
            ->whereIn('id', $validated['ids'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} fee schedule(s) deleted successfully",
            'data' => ['deleted' => $deleted],
        ]);
    }

    /**
     * Generate student fees for all students in a grade based on schedules
     */
    public function generateStudentFees(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'academic_year' => 'required|string|size:9', // "2025-2026"
        ]);

        $grade = Grade::where('institute_id', $user->institute_id)
            ->findOrFail($validated['grade_id']);

        // Calculate date range for academic year (June to June)
        $yearParts = explode('-', $validated['academic_year']);
        $startDate = $yearParts[0] . '-07-01';  // June start
        $endDate = $yearParts[1] . '-06-30';    // June end

        // Get active schedules that fall within this academic year
        $schedules = FeeSchedule::where('institute_id', $user->institute_id)
            ->where('grade_id', $validated['grade_id'])
            ->where('is_active', true)
            ->where('applicable_from', '>=', $startDate)
            ->where('applicable_from', '<=', $endDate)
            ->with('feeType')
            ->get();

        if ($schedules->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active fee schedules found for this grade',
            ], 404);
        }

        // Get students in this grade
        $students = \App\Models\Student::whereHas('section', function ($q) use ($validated) {
            $q->where('grade_id', $validated['grade_id']);
        })->get();

        $generatedCount = 0;
        $skippedCount = 0;

        foreach ($students as $student) {
            $enrollmentDate = $student->admission_date ?? $student->created_at;
            
            foreach ($schedules as $schedule) {
                // Check if student matches fee category (if schedule has specific category)
                if ($schedule->fee_category_id && $schedule->fee_category_id != $student->fee_category_id) {
                    continue; // Skip - doesn't apply to this student
                }

                // Generate fee instances
                $fees = $schedule->generateStudentFees($student->id, $enrollmentDate, $validated['academic_year']);

                foreach ($fees as $feeData) {
                    // Check if already exists
                    $exists = \App\Models\StudentFee::where('student_id', $student->id)
                        ->where('fee_type_id', $feeData['fee_type_id'])
                        ->where('month', $feeData['month'])
                        ->where('academic_year', $feeData['academic_year'])
                        ->exists();

                    if (!$exists) {
                        try {
                            \App\Models\StudentFee::create($feeData);
                            $generatedCount++;
                        } catch (\Illuminate\Database\QueryException $e) {
                            // Skip if duplicate - another process may have created it
                            if ($e->getCode() == 23000) {
                                continue; // Skip this fee
                            }
                            throw $e; // Re-throw other errors
                        }
                    }
                }
            }
            $skippedCount++;
        }

// Build appropriate message
        if ($students->isEmpty()) {
            $message = 'No active students found in this grade. Please add students and assign them to sections first.';
            $success = false;
        } elseif ($schedules->isEmpty()) {
            $message = 'No active fee schedules found for this grade. Please set up fee structure first using /fee-structure/grade/{id}/save';
            $success = false;
        } elseif ($generatedCount === 0 && $skippedCount > 0) {
            $message = 'Student fees already exist. No new fees were generated.';
            $success = true;
        } else {
            $message = "Student fees generated successfully. Created {$generatedCount} fee records for {$skippedCount} students.";
            $success = true;
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => [
                'generated' => $generatedCount,
                'students_processed' => $skippedCount,
            ],
        ]);
    }

    /**
     * Save all fee schedules for a grade at once (bulk)
     */
    public function saveGradeFees(Request $request, int $gradeId): JsonResponse
    {
        $user = $request->user();

        // Validate grade belongs to user
        $grade = Grade::where('institute_id', $user->institute_id)
            ->findOrFail($gradeId);

        $validated = $request->validate([
            'academic_year' => 'required|string|size:9',
            'fees' => 'required|array|min:1',
            'fees.*.fee_type_id' => 'required|integer|exists:fee_types,id',
            'fees.*.amount' => 'required|numeric|min:0',
            'fees.*.frequency' => 'required|in:monthly,quarterly,annual,one_time',
            'fees.*.fee_category_id' => 'nullable|integer|exists:fee_categories,id',
            'fees.*.applicable_from' => 'nullable|date',
            'fees.*.applicable_to' => 'nullable|date',
            'fees.*.month' => 'nullable|string',
            'fees.*.is_active' => 'nullable|boolean',
        ]);

        // Parse academic year to get start/end dates
        $yearParts = explode('-', $validated['academic_year']);
        $startDate = $yearParts[0] . '-07-01'; // April start
        $endDate = $yearParts[1] . '-06-30';   // March end

        $created = 0;
        $updated = 0;
        $schedules = [];

        foreach ($validated['fees'] as $feeData) {
            // Check if schedule already exists for this grade + fee_type + date range
            $existing = FeeSchedule::where('grade_id', $gradeId)
                ->where('fee_type_id', $feeData['fee_type_id'])
                ->where('applicable_from', '>=', $startDate)
                ->where('applicable_from', '<=', $endDate)
                ->first();

            $scheduleData = [
                'institute_id' => $user->institute_id,
                'grade_id' => $gradeId,
                'fee_type_id' => $feeData['fee_type_id'],
                'fee_category_id' => $feeData['fee_category_id'] ?? null,
                'amount' => $feeData['amount'],
                'frequency' => $feeData['frequency'],
                'applicable_from' => $feeData['applicable_from'] ?? $startDate,
                'applicable_to' => $feeData['applicable_to'] ?? $endDate,
                'is_active' => $feeData['is_active'] ?? true,
            ];

            // Handle month for one-time/annual fees
            if (in_array($feeData['frequency'], ['one_time', 'annual']) && !empty($feeData['month'])) {
                $scheduleData['applicable_from'] = $yearParts[0] . '-' . $this->getMonthNumber($feeData['month']) . '-01';
                $scheduleData['applicable_to'] = $scheduleData['applicable_from'];
            }

            if ($existing) {
                $existing->update($scheduleData);
                $updated++;
                $schedules[] = $existing->fresh(['grade', 'feeType', 'feeCategory']);
            } else {
                $schedule = FeeSchedule::create($scheduleData);
                $created++;
                $schedules[] = $schedule->load(['grade', 'feeType', 'feeCategory']);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Fee structure saved for Grade {$grade->name}. Created: {$created}, Updated: {$updated}",
            'data' => [
                'grade_id' => $gradeId,
                'grade_name' => $grade->name,
                'academic_year' => $validated['academic_year'],
                'total_fees' => count($validated['fees']),
                'created' => $created,
                'updated' => $updated,
                'schedules' => \App\Http\Resources\FeeScheduleResource::collection(collect($schedules)),
            ],
        ]);
    }

    /**
     * Get fee structure for all grades with pagination and filters
     */
    public function getAllGradesFees(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 10);
        $academicYear = $request->input('academic_year');
        $hasFees = $request->input('has_fees'); // true = only grades with fees

        // Get all grades for this institute
        $gradesQuery = Grade::where('institute_id', $user->institute_id);

        // Get grades with their fee counts
        $grades = $gradesQuery->get();
        
        $gradeIds = $grades->pluck('id')->toArray();
        
        // Get fee schedule counts per grade
        $feeCountsQuery = FeeSchedule::whereIn('grade_id', $gradeIds)
            ->when($academicYear, function ($q) use ($academicYear) {
                $yearParts = explode('-', $academicYear);
                $startDate = $yearParts[0] . '-07-01';
                $endDate = $yearParts[1] . '-06-30';
                $q->where('applicable_from', '>=', $startDate)
                  ->where('applicable_from', '<=', $endDate);
            })
            ->groupBy('grade_id')
            ->select('grade_id', DB::raw('COUNT(*) as total_fees'));

        $feeCounts = $feeCountsQuery->pluck('total_fees', 'grade_id')->toArray();

        // Filter grades based on has_fees
        if ($hasFees === 'true') {
            $gradeIds = array_keys(array_filter($feeCounts, fn($count) => $count > 0));
            $grades = $grades->whereIn('id', $gradeIds);
        }

        // Paginate
        $total = $grades->count();
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginatedGrades = $grades->slice($offset, $perPage)->values();

        $gradeData = $paginatedGrades->map(function ($grade) use ($feeCounts) {
            return [
                'grade_id' => $grade->id,
                'grade_name' => $grade->name,
                'numeric_value' => $grade->numeric_value,
                'total_fees' => $feeCounts[$grade->id] ?? 0,
                'has_fees' => ($feeCounts[$grade->id] ?? 0) > 0,
            ];
        });

        // Sort by numeric value
        $gradeData = $gradeData->sortBy('numeric_value')->values();

        return response()->json([
            'success' => true,
            'data' => $gradeData,
            'meta' => [
                'current_page' => (int) $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Get fee structure for a grade
     */
    public function getGradeFees(Request $request, int $gradeId): JsonResponse
    {
        $user = $request->user();

        $grade = Grade::where('institute_id', $user->institute_id)
            ->findOrFail($gradeId);

        $academicYear = $request->input('academic_year');
        
        $schedules = FeeSchedule::where('grade_id', $gradeId)
            ->with(['feeType', 'feeCategory'])
            ->when($academicYear, function ($q) use ($academicYear) {
                $yearParts = explode('-', $academicYear);
                $startDate = $yearParts[0] . '-07-01';
                $endDate = $yearParts[1] . '-06-30';
                $q->where('applicable_from', '>=', $startDate)
                  ->where('applicable_from', '<=', $endDate);
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'grade_id' => $gradeId,
                'grade_name' => $grade->name,
                'academic_year' => $academicYear,
                'total_fees' => $schedules->count(),
                'schedules' => \App\Http\Resources\FeeScheduleResource::collection($schedules),
            ],
        ]);
    }

    /**
     * Delete all fee schedules for a grade
     */
    public function deleteGradeFees(Request $request, int $gradeId): JsonResponse
    {
        $user = $request->user();

        $grade = Grade::where('institute_id', $user->institute_id)
            ->findOrFail($gradeId);

        $academicYear = $request->input('academic_year');
        $deleteAll = $request->input('all', false);
        
        $query = FeeSchedule::where('grade_id', $gradeId);
        
        // If academic_year is provided and delete_all is not true, filter by year
        if ($academicYear && !$deleteAll) {
            $yearParts = explode('-', $academicYear);
            $startDate = $yearParts[0] . '-07-01';
            $endDate = $yearParts[1] . '-06-30';
            $query->where('applicable_from', '>=', $startDate)
                  ->where('applicable_from', '<=', $endDate);
        }

        $deleted = $query->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} fee schedule(s) deleted for Grade {$grade->name}",
            'data' => ['deleted' => $deleted],
        ]);
    }

    /**
     * Get month number from name
     */
    private function getMonthNumber(string $month): string
    {
        $months = [
            'January' => '01', 'February' => '02', 'March' => '03', 'April' => '04',
            'May' => '05', 'June' => '06', 'July' => '07', 'August' => '08',
            'September' => '09', 'October' => '10', 'November' => '11', 'December' => '12',
        ];
        return $months[ucfirst($month)] ?? '01';
    }
}
