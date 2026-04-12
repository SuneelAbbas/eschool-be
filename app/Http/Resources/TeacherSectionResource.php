<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'teacher_id' => $this->teacher_id,
            'section_id' => $this->section_id,
            'subject_id' => $this->subject_id,
            'is_class_teacher' => $this->is_class_teacher,
            'teacher' => $this->whenLoaded('teacher', function () {
                return [
                    'id' => $this->teacher->id,
                    'first_name' => $this->teacher->first_name,
                    'last_name' => $this->teacher->last_name,
                    'full_name' => $this->teacher->first_name . ' ' . $this->teacher->last_name,
                    'email' => $this->teacher->email,
                    'cnic_number' => $this->teacher->cnic_number,
                ];
            }),
            'section' => $this->whenLoaded('section', function () {
                return [
                    'id' => $this->section->id,
                    'name' => $this->section->name,
                    'room_no' => $this->section->room_no,
                    'grade' => $this->section->grade ? [
                        'id' => $this->section->grade->id,
                        'name' => $this->section->grade->name,
                    ] : null,
                ];
            }),
            'subject' => $this->whenLoaded('subject', function () {
                return $this->subject ? [
                    'id' => $this->subject->id,
                    'name' => $this->subject->name,
                    'code' => $this->subject->code,
                ] : null;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
