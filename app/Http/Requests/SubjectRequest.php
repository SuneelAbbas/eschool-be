<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectRequest extends FormRequest
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
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'institute_id' => ['nullable', 'integer', 'exists:institutes,id'],
        ];

        if ($instituteId) {
            $rules['name'][] = Rule::unique('subjects')->where(function ($query) use ($instituteId) {
                return $query->where('institute_id', $instituteId);
            });
            $rules['code'][] = Rule::unique('subjects')->where(function ($query) use ($instituteId) {
                return $query->where('institute_id', $instituteId);
            });
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $subjectId = $this->route('id');
            $rules['name'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects')->where(function ($query) use ($instituteId, $subjectId) {
                    $query->where('institute_id', $instituteId ?? null)->where('id', '!=', $subjectId);
                }),
            ];
            $rules['code'] = [
                'nullable',
                'string',
                'max:50',
                Rule::unique('subjects')->where(function ($query) use ($instituteId, $subjectId) {
                    $query->where('institute_id', $instituteId ?? null)->where('id', '!=', $subjectId);
                }),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Subject name is required',
            'name.unique' => 'A subject with this name already exists in this institute',
            'code.unique' => 'A subject with this code already exists in this institute',
        ];
    }
}
