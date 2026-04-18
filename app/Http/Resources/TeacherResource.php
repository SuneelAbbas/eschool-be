<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $classTeacherSection = null;
        if ($this->relationLoaded('teacherSections')) {
            $ct = $this->teacherSections->where('is_class_teacher', true)->first();
            if ($ct && $ct->section) {
                $classTeacherSection = [
                    'id' => $ct->section->id,
                    'name' => $ct->section->name,
                    'grade' => $ct->section->grade ? [
                        'id' => $ct->section->grade->id,
                        'name' => $ct->section->grade->name,
                    ] : null,
                ];
            }
        }

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'email' => $this->email,
            'cnic_number' => $this->cnic_number,
            'gender' => $this->gender,
            'mobile_number' => $this->mobile_number,
            'subject' => $this->subject,
            'join_date' => $this->join_date,
            'date_of_birth' => $this->date_of_birth,
            'blood_group' => $this->blood_group,
            'address' => $this->address,
            'academic_qualification' => $this->academic_qualification,
            'institute_id' => $this->institute_id,
            'sections' => $this->whenLoaded('sections'),
            'section_head' => $classTeacherSection,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
