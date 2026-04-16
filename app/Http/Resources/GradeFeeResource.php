<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeFeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'grade_id' => $this->grade_id,
            'fee_type_id' => $this->fee_type_id,
            'academic_year' => $this->academic_year,
            'amount' => $this->amount,
            'effective_from' => $this->effective_from,
            'effective_to' => $this->effective_to,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'grade' => new GradeResource($this->whenLoaded('grade')),
            'fee_type' => new FeeTypeResource($this->whenLoaded('feeType')),
        ];
    }
}
