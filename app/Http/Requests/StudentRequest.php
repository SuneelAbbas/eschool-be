<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'registration_date' => ['nullable', 'date'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'roll_no' => ['nullable', 'string', 'max:50'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'parents_name' => ['nullable', 'string', 'max:255'],
            'parents_mobile_number' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string'],
            'upload' => ['nullable', 'string', 'max:255'],
            'institute_id' => ['nullable', 'integer', 'exists:institutes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'section_id.exists' => 'Selected section does not exist',
        ];
    }
}
