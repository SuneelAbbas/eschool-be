<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentDiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'discount_id' => ['required', 'integer', 'exists:discounts,id'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'approved_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Student is required',
            'student_id.exists' => 'Selected student does not exist',
            'discount_id.required' => 'Discount is required',
            'discount_id.exists' => 'Selected discount does not exist',
            'effective_from.required' => 'Effective start date is required',
            'effective_from.date' => 'Invalid date format',
            'effective_to.after_or_equal' => 'End date must be after or equal to start date',
        ];
    }
}
