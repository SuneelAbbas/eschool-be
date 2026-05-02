<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeeScheduleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'institute_id' => $this->institute_id,
            'grade_id' => $this->grade_id,
            'fee_type_id' => $this->fee_type_id,
            'fee_category_id' => $this->fee_category_id,
            'amount' => $this->amount,
            'frequency' => $this->frequency,
            'applicable_from' => $this->applicable_from?->format('Y-m-d'),
            'applicable_to' => $this->applicable_to?->format('Y-m-d'),
            'is_active' => $this->is_active,
            'grade' => new \App\Http\Resources\GradeResource($this->whenLoaded('grade')),
            'fee_type' => new FeeTypeResource($this->whenLoaded('feeType')),
            'fee_category' => new FeeCategoryResource($this->whenLoaded('feeCategory')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
