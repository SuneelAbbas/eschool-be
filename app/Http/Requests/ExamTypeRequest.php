<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:exam_types,code',
            'type' => 'required|in:unit_test,terminal,annual,board_prep',
            'max_marks' => 'nullable|integer|min:1|max:1000',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $examTypeId = $this->route('exam_type');
            $rules['code'] = 'nullable|string|max:50|unique:exam_types,code,' . $examTypeId;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Exam type name is required.',
            'type.in' => 'Invalid exam type. Valid types are: unit_test, terminal, annual, board_prep.',
            'max_marks.min' => 'Maximum marks must be at least 1.',
            'code.unique' => 'This exam type code already exists.',
        ];
    }
}
