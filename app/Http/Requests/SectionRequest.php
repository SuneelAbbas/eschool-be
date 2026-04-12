<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_id' => ['required', 'integer', 'exists:grades,id'],
            'name' => ['required', 'string', 'max:255'],
            'room_no' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'class_teacher' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade_id.required' => 'Grade is required',
            'grade_id.exists' => 'Selected grade does not exist',
            'name.required' => 'Section name is required',
        ];
    }
}
