<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'exam_id' => 'required|exists:exams,id',
            'student_id' => 'nullable|exists:students,id',
            'remarks' => 'nullable|string|max:1000',
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'exam_id.required' => 'Exam is required.',
            'exam_id.exists' => 'Selected exam does not exist.',
            'student_id.exists' => 'Selected student does not exist.',
        ];
    }
}
