<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherSectionRequest;
use App\Http\Resources\TeacherSectionResource;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\TeacherSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherSectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $instituteId = $user->isSuperAdmin() ? null : $user->institute_id;

        $query = TeacherSection::with(['teacher', 'section.grade', 'subject']);

        if ($instituteId) {
            $query->whereHas('teacher', function ($q) use ($instituteId) {
                $q->where('institute_id', $instituteId);
            });
        }

        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->has('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $assignments = $query->get();

        return response()->json([
            'success' => true,
            'data' => TeacherSectionResource::collection($assignments),
        ]);
    }

    public function store(TeacherSectionRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $teacher = Teacher::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($data['teacher_id']);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found',
            ], 404);
        }

        $section = Section::find($data['section_id']);
        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }

        $existing = TeacherSection::where('teacher_id', $data['teacher_id'])
            ->where('section_id', $data['section_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher is already assigned to this section',
            ], 422);
        }

        DB::beginTransaction();
        try {
            if (!empty($data['is_class_teacher'])) {
                TeacherSection::where('section_id', $data['section_id'])
                    ->where('is_class_teacher', true)
                    ->update(['is_class_teacher' => false]);

                $section->update(['class_teacher' => $teacher->first_name . ' ' . $teacher->last_name]);
            }

            $assignment = TeacherSection::create([
                'teacher_id' => $data['teacher_id'],
                'section_id' => $data['section_id'],
                'subject_id' => $data['subject_id'] ?? null,
                'is_class_teacher' => $data['is_class_teacher'] ?? false,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create assignment',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Teacher assigned to section successfully',
            'data' => new TeacherSectionResource($assignment->load(['teacher', 'section.grade', 'subject'])),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $assignment = TeacherSection::with(['teacher', 'section.grade', 'subject'])
            ->when(!$user->isSuperAdmin(), function ($query) use ($user) {
                $query->whereHas('teacher', function ($q) use ($user) {
                    $q->where('institute_id', $user->institute_id);
                });
            })
            ->find($id);

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new TeacherSectionResource($assignment),
        ]);
    }

    public function update(TeacherSectionRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $assignment = TeacherSection::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            $query->whereHas('teacher', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        })->find($id);

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found',
            ], 404);
        }

        if (!empty($data['subject_id']) || array_key_exists('subject_id', $data)) {
            $assignment->subject_id = $data['subject_id'] ?? null;
        }

        if (array_key_exists('is_class_teacher', $data)) {
            DB::beginTransaction();
            try {
                if ($data['is_class_teacher']) {
                    TeacherSection::where('section_id', $assignment->section_id)
                        ->where('id', '!=', $assignment->id)
                        ->where('is_class_teacher', true)
                        ->update(['is_class_teacher' => false]);

                    $teacher = $assignment->teacher;
                    Section::where('id', $assignment->section_id)
                        ->update(['class_teacher' => $teacher->first_name . ' ' . $teacher->last_name]);
                } else {
                    Section::where('id', $assignment->section_id)
                        ->where('class_teacher', $assignment->teacher->first_name . ' ' . $assignment->teacher->last_name)
                        ->update(['class_teacher' => null]);
                }

                $assignment->is_class_teacher = $data['is_class_teacher'];
                $assignment->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update assignment',
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Assignment updated successfully',
            'data' => new TeacherSectionResource($assignment->load(['teacher', 'section.grade', 'subject'])),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $assignment = TeacherSection::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            $query->whereHas('teacher', function ($q) use ($user) {
                $q->where('institute_id', $user->institute_id);
            });
        })->find($id);

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found',
            ], 404);
        }

        $wasClassTeacher = $assignment->is_class_teacher;
        $sectionId = $assignment->section_id;
        $teacherName = $assignment->teacher->first_name . ' ' . $assignment->teacher->last_name;

        $assignment->delete();

        if ($wasClassTeacher) {
            Section::where('id', $sectionId)
                ->where('class_teacher', $teacherName)
                ->update(['class_teacher' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Assignment removed successfully',
        ]);
    }
}
