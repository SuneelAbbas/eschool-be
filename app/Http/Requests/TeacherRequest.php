<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $instituteId = $this->input('institute_id') ?? $this->user()?->institute_id;

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'cnic_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('teachers')->where(function ($query) use ($instituteId) {
                    return $query->where('institute_id', $instituteId);
                }),
            ],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'subject' => ['nullable', 'string', 'max:255'],
            'join_date' => ['required', 'date'],
            'date_of_birth' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string'],
            'academic_qualification' => ['nullable', 'string', 'max:255'],
            'institute_id' => ['nullable', 'integer', 'exists:institutes,id'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $teacherId = $this->route('id');
            $rules['first_name'] = ['nullable', 'string', 'max:255'];
            $rules['last_name'] = ['nullable', 'string', 'max:255'];
            $rules['email'] = ['nullable', 'email', 'max:255'];
            $rules['cnic_number'] = [
                'nullable',
                'string',
                'max:20',
                Rule::unique('teachers')->where(function ($query) use ($instituteId, $teacherId) {
                    return $query->where('institute_id', $instituteId)
                                 ->where('id', '!=', $teacherId);
                }),
            ];
            $rules['join_date'] = ['nullable', 'date'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'cnic_number.required' => 'CNIC number is required',
            'cnic_number.unique' => 'A teacher with this CNIC already exists in this institute',
            'join_date.required' => 'Join date is required',
        ];
    }
}
