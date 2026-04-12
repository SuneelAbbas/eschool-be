<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'description' => 'Perfect for small schools getting started',
                'price' => 0,
                'duration_days' => 30,
                'features' => json_encode(['ERP Core', 'LMS Basic', 'Email Support']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Growth',
                'description' => 'For growing institutions with more needs',
                'price' => 4999,
                'duration_days' => 30,
                'features' => json_encode(['Everything in Starter', 'Finance Reports', 'Priority Support']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Elite',
                'description' => 'Full-featured for large institutions',
                'price' => 9999,
                'duration_days' => 30,
                'features' => json_encode(['Everything in Growth', 'Custom Domain', 'Dedicated Manager']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('plans')->insertOrIgnore($plans);
    }
}
