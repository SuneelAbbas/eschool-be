<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $date = $request->input('date', now()->format('Y-m-d'));
        $sectionId = $request->input('section_id');
        $studentId = $request->input('student_id');
        $perPage = $request->input('per_page', 50);

        $query = Attendance::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with(['student', 'section.grade']);

        if ($date) {
            $query->whereDate('date', $date);
        }

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $attendance = $query->orderBy('date', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => AttendanceResource::collection($attendance->items()),
            'meta' => [
                'current_page' => $attendance->currentPage(),
                'last_page' => $attendance->lastPage(),
                'per_page' => $attendance->perPage(),
                'total' => $attendance->total(),
            ],
        ]);
    }

    public function store(AttendanceRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $date = $data['date'];
        $sectionId = $data['section_id'];
        $records = $data['records'] ?? [];

        $instituteId = $user->isSuperAdmin() ? ($data['institute_id'] ?? null) : $user->institute_id;

        if (!empty($records)) {
            $created = [];
            DB::beginTransaction();
            try {
                foreach ($records as $record) {
                    $existing = Attendance::where('student_id', $record['student_id'])
                        ->whereDate('date', $date)
                        ->first();

                    if ($existing) {
                        $existing->update([
                            'status' => $record['status'],
                            'remarks' => $record['remarks'] ?? null,
                            'updated_at' => now(),
                        ]);
                        $created[] = $existing;
                    } else {
                        $created[] = Attendance::create([
                            'student_id' => $record['student_id'],
                            'section_id' => $sectionId,
                            'date' => $date,
                            'status' => $record['status'],
                            'remarks' => $record['remarks'] ?? null,
                            'institute_id' => $instituteId,
                            'created_by' => $user->id,
                        ]);
                    }
                }
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Attendance marked successfully',
                    'data' => AttendanceResource::collection($created),
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to mark attendance',
                    'errors' => ['exception' => [$e->getMessage()]],
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No attendance records provided',
        ], 400);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $attendance = Attendance::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->with(['student', 'section.grade'])->find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new AttendanceResource($attendance),
        ]);
    }

    public function update(AttendanceRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $attendance = Attendance::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found',
            ], 404);
        }

        $data = $request->validated();
        unset($data['institute_id']);
        unset($data['section_id']);
        unset($data['date']);

        $attendance->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully',
            'data' => new AttendanceResource($attendance->fresh(['student', 'section.grade'])),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $attendance = Attendance::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found',
            ], 404);
        }

        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance deleted successfully',
        ]);
    }

    public function report(Request $request): JsonResponse
    {
        $user = $request->user();
        $sectionId = $request->input('section_id');
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));

        $query = Attendance::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        });

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        $query->whereBetween('date', [$fromDate, $toDate]);

        $stats = $query->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $total = $stats->sum();

        $report = [
            'present' => $stats->get('present', 0),
            'absent' => $stats->get('absent', 0),
            'late' => $stats->get('late', 0),
            'excused' => $stats->get('excused', 0),
            'total' => $total,
            'present_percentage' => $total > 0 ? round(($stats->get('present', 0) / $total) * 100, 1) : 0,
            'absent_percentage' => $total > 0 ? round(($stats->get('absent', 0) / $total) * 100, 1) : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
            'filters' => [
                'section_id' => $sectionId,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
        ]);
    }

    public function sectionAttendance(Request $request): JsonResponse
    {
        $user = $request->user();
        $sectionId = $request->input('section_id');
        $date = $request->input('date', now()->format('Y-m-d'));

        if (!$sectionId) {
            return response()->json([
                'success' => false,
                'message' => 'Section ID is required',
            ], 400);
        }

        $instituteId = $user->isSuperAdmin() ? ($request->input('institute_id') ?? null) : $user->institute_id;

        $students = Student::where('section_id', $sectionId)
            ->when($instituteId, function ($query) use ($instituteId) {
                return $query->where('institute_id', $instituteId);
            })
            ->with(['attendance' => function ($query) use ($date) {
                $query->whereDate('date', $date);
            }])
            ->orderBy('roll_no')
            ->get();

        $result = $students->map(function ($student) {
            $attendance = $student->attendance->first();
            return [
                'student_id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'roll_no' => $student->roll_no,
                'attendance' => $attendance ? [
                    'id' => $attendance->id,
                    'status' => $attendance->status,
                    'remarks' => $attendance->remarks,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
            'date' => $date,
            'section_id' => $sectionId,
        ]);
    }
}
