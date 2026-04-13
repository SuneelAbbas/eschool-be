<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $instituteId = $this->input('institute_id') ?? $this->user()?->institute_id;

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('fee_types')->where(function ($query) use ($instituteId) {
                    return $query->where('institute_id', $instituteId);
                }),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('fee_types')->where(function ($query) use ($instituteId) {
                    return $query->where('institute_id', $instituteId);
                }),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', Rule::in(['monthly', 'one_time'])],
            'due_day' => ['nullable', 'integer', 'min:1', 'max:28'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'institute_id' => ['nullable', 'integer', 'exists:institutes,id'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $feeTypeId = $this->route('id');
            $rules['name'][1] = Rule::unique('fee_types')->where(function ($query) use ($instituteId, $feeTypeId) {
                return $query->where('institute_id', $instituteId)
                             ->where('id', '!=', $feeTypeId);
            });
            $rules['code'][1] = Rule::unique('fee_types')->where(function ($query) use ($instituteId, $feeTypeId) {
                return $query->where('institute_id', $instituteId)
                             ->where('id', '!=', $feeTypeId);
            });
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Fee type name is required',
            'name.unique' => 'A fee type with this name already exists in this institute',
            'amount.required' => 'Fee amount is required',
            'amount.numeric' => 'Fee amount must be a number',
            'type.required' => 'Fee type is required',
            'type.in' => 'Fee type must be either monthly or one_time',
            'due_day.min' => 'Due day must be between 1 and 28',
            'due_day.max' => 'Due day must be between 1 and 28',
        ];
    }
}
