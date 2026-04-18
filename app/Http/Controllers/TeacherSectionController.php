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

        // Check if section already has a section head (only for class teacher assignments)
        $existingClassTeacher = TeacherSection::where('section_id', $data['section_id'])
            ->where('is_class_teacher', true)
            ->first();

        if (!empty($data['is_class_teacher']) && $existingClassTeacher) {
            return response()->json([
                'success' => false,
                'message' => 'Section already has a section head. Please remove them first.',
            ], 422);
        }

        // Check if this teacher already has class teacher assignment for this section
        $existingClassTeacherForTeacher = TeacherSection::where('teacher_id', $data['teacher_id'])
            ->where('section_id', $data['section_id'])
            ->where('is_class_teacher', true)
            ->first();

        // For subject assignments (not class teacher), always create new - don't update existing
        $isSubjectAssignment = empty($data['is_class_teacher']);
        
        DB::beginTransaction();
        try {
            // If assigning as class teacher and already has class teacher role, update
            if (!empty($data['is_class_teacher']) && $existingClassTeacherForTeacher) {
                $existingClassTeacherForTeacher->update([
                    'subject_id' => $data['subject_id'] ?? null,
                    'is_class_teacher' => true,
                ]);
                $assignment = $existingClassTeacherForTeacher;
            } elseif (!empty($data['is_class_teacher']) && !$existingClassTeacherForTeacher) {
                // New class teacher assignment
                $assignment = TeacherSection::create([
                    'teacher_id' => $data['teacher_id'],
                    'section_id' => $data['section_id'],
                    'subject_id' => null,
                    'is_class_teacher' => true,
                ]);
            } else {
                // Subject assignment - always create new, never update existing
                $assignment = TeacherSection::create([
                    'teacher_id' => $data['teacher_id'],
                    'section_id' => $data['section_id'],
                    'subject_id' => $data['subject_id'] ?? null,
                    'is_class_teacher' => false,
                ]);
            }

            // Update section's class_teacher field if this is a class teacher assignment
            if (!empty($data['is_class_teacher'])) {
                $section->update(['class_teacher' => $teacher->first_name . ' ' . $teacher->last_name]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("TeacherSection creation failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create assignment',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => !empty($data['is_class_teacher']) 
                ? 'Teacher assigned as section head'
                : 'Teacher assigned to section successfully',
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

    public function assignSectionHead(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $teacher = Teacher::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($data['teacher_id']);

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher not found'], 404);
        }

        $section = Section::find($data['section_id']);
        if (!$section) {
            return response()->json(['success' => false, 'message' => 'Section not found'], 404);
        }

        // Check if section already has a section head
        $existingClassTeacher = TeacherSection::where('section_id', $data['section_id'])
            ->where('is_class_teacher', true)
            ->first();

        if ($existingClassTeacher) {
            return response()->json([
                'success' => false,
                'message' => 'Section already has a section head. Please remove them first.',
            ], 422);
        }

        // Check if this teacher is already class teacher for this section
        $existingForTeacher = TeacherSection::where('teacher_id', $data['teacher_id'])
            ->where('section_id', $data['section_id'])
            ->where('is_class_teacher', true)
            ->first();

        DB::beginTransaction();
        try {
            if ($existingForTeacher) {
                // Already class teacher, just ensure class_teacher field is set
                $section->update(['class_teacher' => $teacher->first_name . ' ' . $teacher->last_name]);
                $assignment = $existingForTeacher;
            } else {
                // Create new class teacher assignment
                $assignment = TeacherSection::create([
                    'teacher_id' => $data['teacher_id'],
                    'section_id' => $data['section_id'],
                    'subject_id' => null,
                    'is_class_teacher' => true,
                ]);
                $section->update(['class_teacher' => $teacher->first_name . ' ' . $teacher->last_name]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Teacher assigned as section head',
                'data' => new TeacherSectionResource($assignment->load(['teacher', 'section.grade', 'subject'])),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to assign section head'], 500);
        }
    }

    public function assignSubject(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $teacher = Teacher::when(!$user->isSuperAdmin(), function ($query) use ($user) {
            return $query->where('institute_id', $user->institute_id);
        })->find($data['teacher_id']);

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher not found'], 404);
        }

        $section = Section::find($data['section_id']);
        if (!$section) {
            return response()->json(['success' => false, 'message' => 'Section not found'], 404);
        }

        // Check if this teacher already has a subject assignment for this section
        $existingSubjectAssignment = TeacherSection::where('teacher_id', $data['teacher_id'])
            ->where('section_id', $data['section_id'])
            ->where('is_class_teacher', false)
            ->first();

        if ($existingSubjectAssignment) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher is already assigned to teach a subject in this section',
            ], 422);
        }

        // Check if section has a section head (from another teacher)
        $existingClassTeacher = TeacherSection::where('section_id', $data['section_id'])
            ->where('is_class_teacher', true)
            ->first();

        $differentTeacher = $existingClassTeacher && $existingClassTeacher->teacher_id != $data['teacher_id'];

        DB::beginTransaction();
        try {
            $assignment = TeacherSection::create([
                'teacher_id' => $data['teacher_id'],
                'section_id' => $data['section_id'],
                'subject_id' => $data['subject_id'],
                'is_class_teacher' => false,
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subject assigned to teacher',
                'data' => new TeacherSectionResource($assignment->load(['teacher', 'section.grade', 'subject'])),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to assign subject'], 500);
        }
    }
}
