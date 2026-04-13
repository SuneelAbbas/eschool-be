<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'exam_type_id' => 'required|exists:exam_types,id',
            'grade_id' => 'required|exists:grades,id',
            'section_id' => 'nullable|exists:sections,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'total_marks' => 'nullable|integer|min:1|max:1000',
            'status' => 'nullable|in:scheduled,ongoing,completed,cancelled',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'exam_type_id.required' => 'Exam type is required.',
            'exam_type_id.exists' => 'Selected exam type does not exist.',
            'grade_id.required' => 'Grade is required.',
            'grade_id.exists' => 'Selected grade does not exist.',
            'section_id.exists' => 'Selected section does not exist.',
            'title.required' => 'Exam title is required.',
            'start_date.required' => 'Start date is required.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'status.in' => 'Invalid status. Valid statuses are: scheduled, ongoing, completed, cancelled.',
        ];
    }
}
