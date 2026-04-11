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
                'slug' => 'starter',
                'price' => 0,
                'billing_period' => 'monthly',
                'max_students' => 150,
                'features' => json_encode(['ERP Core', 'LMS Basic', 'Email Support']),
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'price' => 4999,
                'billing_period' => 'monthly',
                'max_students' => 500,
                'features' => json_encode(['Everything in Starter', 'Finance Reports', 'Priority Support']),
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Elite',
                'slug' => 'elite',
                'price' => 9999,
                'billing_period' => 'monthly',
                'max_students' => null,
                'features' => json_encode(['Everything in Growth', 'Custom Domain', 'Dedicated Manager']),
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('plans')->insertOrIgnore($plans);
    }
}
