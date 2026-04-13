<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiscountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $instituteId = $this->input('institute_id') ?? $this->user()?->institute_id;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('discounts')->where(function ($query) use ($instituteId) {
                    return $query->where('institute_id', $instituteId);
                }),
            ],
            'type' => ['required', Rule::in(['sibling', 'scholarship', 'need_based', 'merit', 'custom'])],
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fixed_amount' => ['nullable', 'numeric', 'min:0'],
            'conditions' => ['nullable', 'array'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'institute_id' => ['nullable', 'integer', 'exists:institutes,id'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $discountId = $this->route('id');
            $rules['code'][1] = Rule::unique('discounts')->where(function ($query) use ($instituteId, $discountId) {
                return $query->where('institute_id', $instituteId)
                             ->where('id', '!=', $discountId);
            });
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Discount name is required',
            'type.required' => 'Discount type is required',
            'type.in' => 'Invalid discount type',
            'percentage.max' => 'Percentage cannot exceed 100',
        ];
    }
}
