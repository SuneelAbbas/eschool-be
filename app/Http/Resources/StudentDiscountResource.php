<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentDiscountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'discount_id' => $this->discount_id,
            'effective_from' => $this->effective_from,
            'effective_to' => $this->effective_to,
            'approved_by' => $this->approved_by,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'student' => new StudentResource($this->whenLoaded('student')),
            'discount' => new DiscountResource($this->whenLoaded('discount')),
        ];
    }
}
