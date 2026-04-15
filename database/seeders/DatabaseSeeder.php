<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            InstituteSeeder::class,
            GradeSeeder::class,
            SectionSeeder::class,
            StudentSeeder::class,
            SubjectSeeder::class,
            FeeTypeSeeder::class,
            BankAccountSeeder::class,
            DiscountSeeder::class,
            ExamTypeSeeder::class,
        ]);
    }
}
