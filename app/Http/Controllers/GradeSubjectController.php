<?php

namespace App\Http\Controllers;

use App\Models\GradeSubject;
use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GradeSubjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $instituteId = $user->isSuperAdmin() ? null : $user->institute_id;

        // Get all grades for the institute
        $grades = Grade::where('institute_id', $instituteId)->get();

        // Get all grade-subjects mappings
        $gradeSubjectsQuery = GradeSubject::with(['grade', 'subject'])
            ->whereHas('grade', function ($q) use ($instituteId) {
                if ($instituteId) {
                    $q->where('institute_id', $instituteId);
                }
            });

        $gradeId = $request->grade_id;
        if ($gradeId) {
            $gradeSubjectsQuery->where('grade_id', $gradeId);
        }

        $subjectId = $request->subject_id;
        if ($subjectId) {
            $gradeSubjectsQuery->where('subject_id', $subjectId);
        }

        $gradeSubjects = $gradeSubjectsQuery->get();

        // Get existing grade_ids from mappings
        $mappedGradeIds = $gradeSubjects->pluck('grade_id')->unique()->toArray();

        // Build response: include all grades (with subjects if mapped, empty array if not)
        $responseData = [];

        foreach ($grades as $grade) {
            $gradeMappings = $gradeSubjects->where('grade_id', $grade->id)->map(function ($gs) {
                return [
                    'id' => $gs->id,
                    'grade_id' => $gs->grade_id,
                    'subject_id' => $gs->subject_id,
                    'grade' => $gs->grade ? [
                        'id' => $gs->grade->id,
                        'name' => $gs->grade->name,
                    ] : null,
                    'subject' => $gs->subject ? [
                        'id' => $gs->subject->id,
                        'name' => $gs->subject->name,
                        'code' => $gs->subject->code,
                    ] : null,
                    'created_at' => $gs->created_at,
                    'updated_at' => $gs->updated_at,
                ];
            })->values()->all();

            $responseData[] = [
                'grade_id' => $grade->id,
                'grade' => [
                    'id' => $grade->id,
                    'name' => $grade->name,
                ],
                'subjects' => $gradeMappings,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $responseData,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $instituteId = $user->isSuperAdmin() ? null : $user->institute_id;

        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        // Verify grade belongs to user's institute
        $grade = Grade::where('id', $validated['grade_id'])
            ->when($instituteId, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })
            ->first();

        if (!$grade) {
            return response()->json([
                'success' => false,
                'message' => 'Grade not found',
            ], 404);
        }

        $created = [];
        $skipped = [];

        foreach ($validated['subject_ids'] as $subjectId) {
            $exists = GradeSubject::where('grade_id', $validated['grade_id'])
                ->where('subject_id', $subjectId)
                ->exists();

            if ($exists) {
                $skipped[] = $subjectId;
                continue;
            }

            $gs = GradeSubject::create([
                'grade_id' => $validated['grade_id'],
                'subject_id' => $subjectId,
            ]);

            $subject = Subject::find($subjectId);
            $created[] = [
                'id' => $gs->id,
                'grade_id' => $gs->grade_id,
                'subject_id' => $gs->subject_id,
                'subject' => $subject ? ['id' => $subject->id, 'name' => $subject->name, 'code' => $subject->code] : null,
            ];
        }

        $message = count($created) . ' subject(s) assigned to grade';
        if (count($skipped) > 0) {
            $message .= ', ' . count($skipped) . ' already assigned';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'created' => $created,
                'skipped' => $skipped,
            ],
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $instituteId = $user->isSuperAdmin() ? null : $user->institute_id;

        $gradeSubject = GradeSubject::whereHas('grade', function ($q) use ($instituteId) {
            if ($instituteId) {
                $q->where('institute_id', $instituteId);
            }
        })->find($id);

        if (!$gradeSubject) {
            return response()->json([
                'success' => false,
                'message' => 'Grade subject not found',
            ], 404);
        }

        $subjectName = $gradeSubject->subject?->name ?? 'Subject';
        $gradeName = $gradeSubject->grade?->name ?? 'Grade';

        // Check if any teachers are assigned to this subject in this grade
        $teacherCount = \App\Models\TeacherSection::whereHas('section', function ($q) use ($gradeSubject) {
            $q->where('grade_id', $gradeSubject->grade_id);
        })
        ->where('subject_id', $gradeSubject->subject_id)
        ->where('is_class_teacher', false)
        ->count();

        if ($teacherCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot remove: $teacherCount teacher(s) are assigned to $subjectName in $gradeName. Please unassign teachers first.",
            ], 422);
        }

        $gradeSubject->delete();

        return response()->json([
            'success' => true,
            'message' => "$subjectName removed from $gradeName",
        ]);
    }

    public function clearGrade(Request $request): JsonResponse
    {
        $user = $request->user();
        $instituteId = $user->isSuperAdmin() ? null : $user->institute_id;

        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
        ]);

        $grade = Grade::where('id', $validated['grade_id'])
            ->when($instituteId, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })
            ->first();

        if (!$grade) {
            return response()->json([
                'success' => false,
                'message' => 'Grade not found',
            ], 404);
        }

        $gradeSubjects = GradeSubject::where('grade_id', $validated['grade_id'])->get();
        
        $deleted = [];
        $kept = [];

        foreach ($gradeSubjects as $gs) {
            $teacherCount = \App\Models\TeacherSection::whereHas('section', function ($q) use ($gs) {
                $q->where('grade_id', $gs->grade_id);
            })
            ->where('subject_id', $gs->subject_id)
            ->where('is_class_teacher', false)
            ->count();

            if ($teacherCount > 0) {
                $kept[] = [
                    'id' => $gs->id,
                    'subject_id' => $gs->subject_id,
                    'subject_name' => $gs->subject?->name,
                    'teacher_count' => $teacherCount,
                ];
            } else {
                $deleted[] = $gs->id;
                $gs->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Grade subjects cleared',
            'data' => [
                'deleted_count' => count($deleted),
                'kept_count' => count($kept),
                'kept_subjects' => $kept,
            ],
        ]);
    }

    public function getSubjectsForGrade(Request $request, int $gradeId): JsonResponse
    {
        $user = $request->user();
        $instituteId = $user->isSuperAdmin() ? null : $user->institute_id;

        $grade = Grade::where('id', $gradeId)
            ->when($instituteId, function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            })
            ->first();

        if (!$grade) {
            return response()->json([
                'success' => false,
                'message' => 'Grade not found',
            ], 404);
        }

        $gradeSubjects = GradeSubject::where('grade_id', $gradeId)
            ->with('subject')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $gradeSubjects->map(function ($gs) {
                return [
                    'id' => $gs->subject->id,
                    'name' => $gs->subject->name,
                    'code' => $gs->subject->code,
                ];
            }),
        ]);
    }
}