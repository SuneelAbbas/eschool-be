<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradeRequest extends FormRequest
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
                Rule::unique('grades')->where(function ($query) use ($instituteId) {
                    return $query->where('institute_id', $instituteId);
                }),
            ],
            'description' => ['nullable', 'string'],
            'institute_id' => ['nullable', 'integer', 'exists:institutes,id'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $gradeId = $this->route('id');
            $rules['name'] = [
                'nullable',
                'string',
                'max:255',
                Rule::unique('grades')->where(function ($query) use ($instituteId, $gradeId) {
                    return $query->where('institute_id', $instituteId)
                                 ->where('id', '!=', $gradeId);
                }),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Grade name is required',
            'name.unique' => 'A grade with this name already exists in this institute',
            'institute_id.exists' => 'Invalid institute',
        ];
    }
}
