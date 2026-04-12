<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'date' => ['required', 'date'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'records' => ['nullable', 'array'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.status' => ['required', Rule::in(['present', 'absent', 'late', 'excused'])],
            'records.*.remarks' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = [
                'student_id' => ['required', 'integer', 'exists:students,id'],
                'date' => ['nullable', 'date'],
                'status' => ['nullable', Rule::in(['present', 'absent', 'late', 'excused'])],
                'remarks' => ['nullable', 'string', 'max:500'],
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Date is required',
            'section_id.required' => 'Section is required',
            'section_id.exists' => 'Selected section does not exist',
            'records.*.student_id.required' => 'Student ID is required',
            'records.*.status.required' => 'Attendance status is required',
            'records.*.status.in' => 'Status must be: present, absent, late, or excused',
        ];
    }
}
