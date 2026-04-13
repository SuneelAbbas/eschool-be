<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'amount' => $this->amount,
            'type' => $this->type,
            'due_day' => $this->due_day,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'institute_id' => $this->institute_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
