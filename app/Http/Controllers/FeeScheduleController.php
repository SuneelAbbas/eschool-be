<?php

namespace App\Http\Controllers;

use App\Models\FeeSchedule;
use App\Models\FeeType;
use App\Models\Grade;
use App\Models\FeeCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
            'data' => $schedules,
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
            'data' => $schedule,
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

        // Get all active schedules for this grade
        $schedules = FeeSchedule::where('institute_id', $user->institute_id)
            ->where('grade_id', $validated['grade_id'])
            ->where('is_active', true)
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
                        \App\Models\StudentFee::create($feeData);
                        $generatedCount++;
                    }
                }
            }
            $skippedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => 'Student fees generated successfully',
            'data' => [
                'generated' => $generatedCount,
                'students_processed' => $students->count(),
            ],
        ]);
    }
}
