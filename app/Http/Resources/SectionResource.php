<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'room_no' => $this->room_no,
            'capacity' => $this->capacity,
            'class_teacher' => $this->class_teacher,
            'grade_id' => $this->grade_id,
            'grade' => new GradeResource($this->whenLoaded('grade')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
