<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkExamResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exam_id' => 'required|exists:exams,id',
            'results' => 'required|array|min:1',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.subject_id' => 'required|exists:subjects,id',
            'results.*.marks_obtained' => 'required|numeric|min:0',
            'results.*.max_marks' => 'required|numeric|min:1',
            'results.*.remarks' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'exam_id.required' => 'Exam is required.',
            'exam_id.exists' => 'Selected exam does not exist.',
            'results.required' => 'Results data is required.',
            'results.min' => 'At least one result entry is required.',
            'results.*.student_id.required' => 'Student ID is required for each result.',
            'results.*.student_id.exists' => 'One or more student IDs are invalid.',
            'results.*.subject_id.required' => 'Subject ID is required for each result.',
            'results.*.subject_id.exists' => 'One or more subject IDs are invalid.',
            'results.*.marks_obtained.required' => 'Marks obtained is required for each result.',
            'results.*.max_marks.required' => 'Maximum marks is required for each result.',
        ];
    }
}
