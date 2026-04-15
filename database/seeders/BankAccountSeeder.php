<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $institutes = \App\Models\Institute::where('status', 'approved')->get();

        foreach ($institutes as $index => $institute) {
            BankAccount::create([
                'institute_id' => $institute->id,
                'bank_name' => 'Meezan Bank',
                'account_title' => $institute->name . ' - School Account',
                'account_number' => '1234-5678-9012-3456',
                'branch_code' => '0142',
                'branch_address' => 'Main Branch, ' . ($institute->city ?? 'City'),
                'is_default' => true,
                'is_active' => true,
            ]);

            BankAccount::create([
                'institute_id' => $institute->id,
                'bank_name' => 'JazzCash',
                'account_title' => $institute->name . ' - JazzCash',
                'account_number' => '03' . rand(100000000, 999999999),
                'is_default' => false,
                'is_active' => true,
            ]);
        }
    }
}
