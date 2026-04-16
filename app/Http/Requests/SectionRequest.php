<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        $rules = [
            'grade_id' => $isUpdate ? ['sometimes', 'integer', 'exists:grades,id'] : ['required', 'integer', 'exists:grades,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sections')->where(function ($query) {
                    $gradeId = $this->input('grade_id');
                    return $query->where('grade_id', $gradeId);
                }),
            ],
            'room_no' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'class_teacher' => ['nullable', 'string', 'max:255'],
        ];

        if ($isUpdate) {
            $sectionId = $this->route('id');
            $rules['name'] = [
                'nullable',
                'string',
                'max:255',
                Rule::unique('sections')->where(function ($query) use ($sectionId) {
                    $gradeId = $this->input('grade_id');
                    if ($gradeId) {
                        return $query->where('grade_id', $gradeId)
                                     ->where('id', '!=', $sectionId);
                    }
                    return $query->where('id', '!=', $sectionId);
                }),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'grade_id.required' => 'Grade is required',
            'grade_id.exists' => 'Selected grade does not exist',
            'name.required' => 'Section name is required',
            'name.unique' => 'A section with this name already exists in this grade',
        ];
    }
}
