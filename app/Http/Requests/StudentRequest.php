<?php

namespace App\Http\Requests;

use App\Models\Section;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $instituteId = $this->input('institute_id') ?? $this->user()?->institute_id;
        $sectionId = $this->input('section_id');
        $studentId = $this->route('id');

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'registration_date' => ['required', 'date'],
            'registration_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('students')->where(function ($query) use ($instituteId) {
                    return $query->where('institute_id', $instituteId);
                }),
            ],
            'roll_no' => ['nullable', 'string', 'max:50'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id', function ($attribute, $value, $fail) use ($sectionId, $studentId) {
                if ($sectionId) {
                    $section = Section::find($sectionId);
                    if ($section) {
                        $studentCount = $section->students()->count();
                        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                            $studentCount = $section->students()->where('id', '!=', $studentId)->count();
                        }
                        if ($studentCount >= $section->capacity) {
                            $fail("Section '{$section->name}' has reached its capacity of {$section->capacity} students.");
                        }
                    }
                }
            }],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'parents_name' => ['nullable', 'string', 'max:255'],
            'parents_mobile_number' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string'],
            'upload' => ['nullable', 'string', 'max:255'],
            'institute_id' => ['nullable', 'integer', 'exists:institutes,id'],
            'admission_date' => ['nullable', 'date'],
            'fee_category_id' => ['nullable', 'integer', 'exists:fee_categories,id'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['first_name'] = ['nullable', 'string', 'max:255'];
            $rules['last_name'] = ['nullable', 'string', 'max:255'];
            $rules['registration_date'] = ['nullable', 'date'];
            $rules['registration_number'] = [
                'nullable',
                'string',
                'max:255',
                Rule::unique('students')->where(function ($query) use ($instituteId, $studentId) {
                    return $query->where('institute_id', $instituteId)
                                 ->where('id', '!=', $studentId);
                }),
            ];
            $rules['fee_category_id'] = ['sometimes', 'nullable', 'integer', 'exists:fee_categories,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'section_id.exists' => 'Selected section does not exist',
            'registration_number.unique' => 'A student with this registration number already exists in this institute',
        ];
    }
}
