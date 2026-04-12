<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'institute_id' => ['nullable', 'integer', 'exists:institutes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Grade name is required',
            'institute_id.exists' => 'Invalid institute',
        ];
    }
}
