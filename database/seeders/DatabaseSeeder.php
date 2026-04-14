<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            InstituteSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            GradeSeeder::class,
            SectionSeeder::class,
            StudentSeeder::class,
            SubjectSeeder::class,
            FeeTypeSeeder::class,
            DiscountSeeder::class,
            ExamTypeSeeder::class,
        ]);
    }
}
