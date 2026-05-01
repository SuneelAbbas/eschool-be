<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'institute_id' => $this->institute_id,
            'has_fees_assigned' => (int) ($this->grade_fees_count ?? 0) > 0,
            'sections_count' => $this->sections()->count(),
            'students_count' => $this->sections()->withCount('students')->get()->sum('students_count'),
            'grade_fees' => $this->whenLoaded('gradeFees', function () {
                return GradeFeeResource::collection($this->gradeFees);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
