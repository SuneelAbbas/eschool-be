<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $instituteId = $this->user()?->institute_id;

        $rules = [
            'teacher_id' => [
                'required',
                'integer',
                'exists:teachers,id',
                Rule::exists('teachers', 'id')->where(function ($query) use ($instituteId) {
                    if ($instituteId) {
                        $query->where('institute_id', $instituteId);
                    }
                }),
            ],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'is_class_teacher' => ['nullable', 'boolean'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['teacher_id'] = ['nullable', 'integer'];
            $rules['section_id'] = ['nullable', 'integer'];
            $rules['subject_id'] = ['nullable', 'integer'];
            $rules['is_class_teacher'] = ['nullable', 'boolean'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'teacher_id.required' => 'Please select a teacher',
            'teacher_id.exists' => 'Selected teacher does not exist or belongs to another institute',
            'section_id.required' => 'Please select a section',
            'section_id.exists' => 'Selected section does not exist',
            'subject_id.exists' => 'Selected subject does not exist',
        ];
    }
}
