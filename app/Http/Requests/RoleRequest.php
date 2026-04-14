<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role');

        return [
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:50|unique:roles,slug' . ($roleId ? ",$roleId" : ''),
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'A role with this slug already exists.',
            'permissions.*.exists' => 'One or more selected permissions are invalid.',
        ];
    }
}
