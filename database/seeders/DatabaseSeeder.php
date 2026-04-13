<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            GradeSeeder::class,
            SectionSeeder::class,
            StudentSeeder::class,
            FeeTypeSeeder::class,
            DiscountSeeder::class,
            ExamTypeSeeder::class,
        ]);
    }
}
