<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterInstituteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            
            'institute_name' => ['required', 'string', 'max:255'],
            'institute_city' => ['nullable', 'string', 'max:100'],
            'institute_type' => ['nullable', 'string', 'max:50'],
            'institute_address' => ['nullable', 'string', 'max:500'],
            'institute_contact_phone' => ['nullable', 'string', 'max:50'],
            'institute_no_of_students' => ['nullable', 'integer', 'min:1'],
            'institute_description' => ['nullable', 'string', 'max:2000'],
            'institute_logo' => ['nullable', 'string', 'max:255'],
            
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'institute_name.required' => 'Institute name is required.',
            'institute_no_of_students.integer' => 'Number of students must be a valid number.',
            'institute_contact_email.email' => 'Please provide a valid contact email.',
        ];
    }
}
