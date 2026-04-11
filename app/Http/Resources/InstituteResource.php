<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstituteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $this->logo,
            'address' => $this->address,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'type' => $this->type,
            'city' => $this->city,
            'no_of_students' => $this->no_of_students,
            'description' => $this->description,
            'status' => $this->status,
            'plan_id' => $this->plan_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
