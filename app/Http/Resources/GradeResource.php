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
            'has_fees_assigned' => $this->feeSchedules()->where('is_active', true)->exists(),
            'sections_count' => $this->sections()->count(),
            'students_count' => $this->sections()->withCount('students')->get()->sum('students_count'),
            'fee_schedules' => $this->whenLoaded('feeSchedules', function () {
                return \App\Http\Resources\FeeScheduleResource::collection($this->feeSchedules);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
