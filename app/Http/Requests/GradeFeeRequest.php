<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_id' => ['required', 'integer', 'exists:grades,id'],
            'fee_type_id' => ['required', 'integer', 'exists:fee_types,id'],
            'academic_year' => ['nullable', 'string', 'max:9'],
            'amount' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade_id.required' => 'Grade is required',
            'grade_id.exists' => 'Selected grade does not exist',
            'fee_type_id.required' => 'Fee type is required',
            'fee_type_id.exists' => 'Selected fee type does not exist',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a number',
            'effective_to.after_or_equal' => 'End date must be after or equal to start date',
        ];
    }
}
