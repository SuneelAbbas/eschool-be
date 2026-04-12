<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student' => $this->whenLoaded('student', function () {
                return [
                    'id' => $this->student->id,
                    'first_name' => $this->student->first_name,
                    'last_name' => $this->student->last_name,
                    'roll_no' => $this->student->roll_no,
                    'section_id' => $this->student->section_id,
                ];
            }),
            'section_id' => $this->section_id,
            'section' => $this->whenLoaded('section', function () {
                return [
                    'id' => $this->section->id,
                    'name' => $this->section->name,
                    'grade_id' => $this->section->grade_id,
                    'grade' => $this->section->grade ? [
                        'id' => $this->section->grade->id,
                        'name' => $this->section->grade->name,
                    ] : null,
                ];
            }),
            'date' => $this->date->format('Y-m-d'),
            'status' => $this->status,
            'remarks' => $this->remarks,
            'institute_id' => $this->institute_id,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
