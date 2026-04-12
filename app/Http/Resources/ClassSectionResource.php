<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'grade_id' => $this->grade_id,
            'section_id' => $this->section_id,
            'class_teacher' => $this->class_teacher,
            'room_no' => $this->room_no,
            'grade' => new GradeResource($this->whenLoaded('grade')),
            'section' => new SectionResource($this->whenLoaded('section')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
