<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_id' => ['required', 'integer', 'exists:grades,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'class_teacher' => ['nullable', 'string', 'max:255'],
            'room_no' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade_id.required' => 'Grade is required',
            'grade_id.exists' => 'Selected grade does not exist',
            'section_id.required' => 'Section is required',
            'section_id.exists' => 'Selected section does not exist',
        ];
    }
}
