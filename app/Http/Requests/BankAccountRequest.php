<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_name' => 'required|string|max:255',
            'account_title' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'branch_code' => 'nullable|string|max:20',
            'branch_address' => 'nullable|string|max:500',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
