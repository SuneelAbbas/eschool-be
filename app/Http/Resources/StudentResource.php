<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'email' => $this->email,
            'registration_date' => $this->registration_date,
            'registration_number' => $this->registration_number,
            'roll_no' => $this->roll_no,
            'gender' => $this->gender,
            'mobile_number' => $this->mobile_number,
            'parents_name' => $this->parents_name,
            'parents_mobile_number' => $this->parents_mobile_number,
            'date_of_birth' => $this->date_of_birth,
            'blood_group' => $this->blood_group,
            'address' => $this->address,
            'upload' => $this->upload,
            'institute_id' => $this->institute_id,
            'section_id' => $this->section_id,
            'admission_date' => $this->admission_date,
            'section' => new SectionResource($this->whenLoaded('section')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
