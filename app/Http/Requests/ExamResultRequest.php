<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'marks_obtained' => 'required|numeric|min:0',
            'max_marks' => 'required|numeric|min:1',
            'remarks' => 'nullable|string|max:500',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'exam_id.required' => 'Exam is required.',
            'exam_id.exists' => 'Selected exam does not exist.',
            'student_id.required' => 'Student is required.',
            'student_id.exists' => 'Selected student does not exist.',
            'subject_id.required' => 'Subject is required.',
            'subject_id.exists' => 'Selected subject does not exist.',
            'marks_obtained.required' => 'Marks obtained is required.',
            'marks_obtained.min' => 'Marks obtained cannot be negative.',
            'max_marks.required' => 'Maximum marks is required.',
            'max_marks.min' => 'Maximum marks must be at least 1.',
        ];
    }
}
