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
            SubjectSeeder::class,
            FeeTypeSeeder::class,
            FeeCategorySeeder::class,
            FeeScheduleSeeder::class,
            StudentSeeder::class,
            BankAccountSeeder::class,
            DiscountSeeder::class,
            ExamTypeSeeder::class,
            // FakerSeeder::class, // TODO: Fix duplicate grade error
        ]);
    }
}
