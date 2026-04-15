<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\GradeFee;
use App\Models\StudentFee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function store(StudentRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if (!$user->isSuperAdmin()) {
            $data['institute_id'] = $user->institute_id;
        }

        $student = Student::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data' => new StudentResource($student),
        ], 201);
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
            ->first();

        if (!$gradeFee) {
            return response()->json([
                'success' => false,
                'message' => 'Grade fee not found for the specified fee type',
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
                ->where('is_active', true)
                ->first();

            if ($existing) {
                $skipped++;
                continue;
            }

            StudentFee::create([
                'student_id' => $student->id,
                'fee_type_id' => $feeTypeId,
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
            'message' => "Fees assigned to {$created} students. {$skipped} already had this fee.",
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

        $oneTimeGradeFees = GradeFee::where('grade_id', $gradeId)
            ->whereHas('feeType', function ($query) {
                $query->where('type', 'one_time');
            })
            ->get();

        foreach ($oneTimeGradeFees as $gradeFee) {
            $existing = StudentFee::where('student_id', $student->id)
                ->where('fee_type_id', $gradeFee->fee_type_id)
                ->first();

            if (!$existing) {
                StudentFee::create([
                    'student_id' => $student->id,
                    'fee_type_id' => $gradeFee->fee_type_id,
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
