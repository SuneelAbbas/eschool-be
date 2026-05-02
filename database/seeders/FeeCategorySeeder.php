<?php

namespace Database\Seeders;

use App\Models\FeeCategory;
use App\Models\Institute;
use Illuminate\Database\Seeder;

class FeeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $institutes = Institute::all();

        if ($institutes->isEmpty()) {
            $this->command->warn('No institutes found. Please run InstituteSeeder first.');
            return;
        }

        $categoriesTemplate = [
            [
                'name' => 'New Student',
                'code' => 'NEW',
                'description' => 'Fresh admissions - includes admission fee',
                'is_active' => true,
            ],
            [
                'name' => 'Old Student',
                'code' => 'OLD',
                'description' => 'Existing students - no admission fee',
                'is_active' => true,
            ],
            [
                'name' => 'RTE (Right to Education)',
                'code' => 'RTE',
                'description' => 'Government quota students',
                'is_active' => true,
            ],
            [
                'name' => 'Scholarship',
                'code' => 'SCHOLAR',
                'description' => 'Merit-based scholarship students',
                'is_active' => true,
            ],
        ];

        $totalCreated = 0;

        foreach ($institutes as $institute) {
            // Check if categories already exist
            $existingCount = FeeCategory::where('institute_id', $institute->id)->count();
            
            if ($existingCount > 0) {
                $this->command->info("Fee categories already exist for institute: {$institute->name}, skipping.");
                continue;
            }

            foreach ($categoriesTemplate as $category) {
                FeeCategory::create([
                    'institute_id' => $institute->id,
                    ...$category,
                ]);
                $totalCreated++;
            }

            $this->command->info("Created 4 fee categories for institute: {$institute->name}");
        }

        $this->command->info("Total fee categories created: {$totalCreated}");
    }
}
