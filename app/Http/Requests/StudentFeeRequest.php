<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'fee_type_id' => ['required', 'integer', 'exists:fee_types,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'is_custom' => ['boolean'],
            'is_active' => ['boolean'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Student is required',
            'student_id.exists' => 'Selected student does not exist',
            'fee_type_id.required' => 'Fee type is required',
            'fee_type_id.exists' => 'Selected fee type does not exist',
            'amount.numeric' => 'Amount must be a number',
            'effective_to.after_or_equal' => 'End date must be after or equal to start date',
        ];
    }
}
