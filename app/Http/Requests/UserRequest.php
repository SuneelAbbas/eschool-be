<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email' . ($userId ? ",$userId" : ''),
            'password' => $this->isMethod('post') ? 'required|string|min:8|confirmed' : 'nullable|string|min:8',
            'user_type' => 'required|in:' . implode(',', array_keys($this->getUserTypeOptions())),
            'institute_id' => 'nullable|exists:institutes,id',
            'status' => 'nullable|in:active,pending,suspended,inactive',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered.',
            'user_type.in' => 'Invalid user type selected.',
            'status.in' => 'Invalid status selected.',
        ];
    }

    private function getUserTypeOptions(): array
    {
        return [
            'admin' => 'Admin',
            'teacher' => 'Teacher',
            'student' => 'Student',
            'parent' => 'Parent',
            'accountant' => 'Accountant',
            'librarian' => 'Librarian',
        ];
    }
}
