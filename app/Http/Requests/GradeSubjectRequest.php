<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'grade_id' => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
            'is_compulsory' => 'nullable|boolean',
            'max_marks' => 'nullable|integer|min:1|max:1000',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'grade_id.required' => 'Grade is required.',
            'grade_id.exists' => 'Selected grade does not exist.',
            'subject_id.required' => 'Subject is required.',
            'subject_id.exists' => 'Selected subject does not exist.',
        ];
    }
}
