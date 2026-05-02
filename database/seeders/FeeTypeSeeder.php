<?php

namespace Database\Seeders;

use App\Models\FeeType;
use App\Models\Institute;
use Illuminate\Database\Seeder;

class FeeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $institutes = Institute::all();

        if ($institutes->isEmpty()) {
            $this->command->warn('No institutes found. Please run InstituteSeeder first.');
            return;
        }

        $feeTypesTemplate = [
            [
                'name' => 'Tuition Fee',
                'code' => 'TUITION',
                'type' => 'monthly',
                'due_day' => 10,
                'description' => 'Monthly tuition fee for regular classes',
                'is_active' => true,
            ],
            [
                'name' => 'Lab Fee',
                'code' => 'LAB',
                'type' => 'monthly',
                'due_day' => 10,
                'description' => 'Monthly laboratory fee for science subjects',
                'is_active' => true,
            ],
            [
                'name' => 'Library Fee',
                'code' => 'LIBRARY',
                'type' => 'monthly',
                'due_day' => 10,
                'description' => 'Monthly library maintenance fee',
                'is_active' => true,
            ],
            [
                'name' => 'Sports Fee',
                'code' => 'SPORTS',
                'type' => 'monthly',
                'due_day' => 10,
                'description' => 'Monthly sports and physical education fee',
                'is_active' => true,
            ],
            [
                'name' => 'Admission Fee',
                'code' => 'ADMISSION',
                'type' => 'one_time',
                'due_day' => null,
                'description' => 'One-time admission processing fee',
                'is_active' => true,
            ],
            [
                'name' => 'Annual Exam Fee',
                'code' => 'EXAM',
                'type' => 'one_time',
                'due_day' => null,
                'description' => 'Annual examination fee',
                'is_active' => true,
            ],
            [
                'name' => 'Transport Fee',
                'code' => 'TRANSPORT',
                'type' => 'monthly',
                'due_day' => 10,
                'description' => 'Monthly transportation fee',
                'is_active' => true,
            ],
            [
                'name' => 'Activity Fee',
                'code' => 'ACTIVITY',
                'type' => 'monthly',
                'due_day' => 10,
                'description' => 'Monthly co-curricular activities fee',
                'is_active' => true,
            ],
        ];

        $totalCreated = 0;

        foreach ($institutes as $institute) {
            // Check if fee types already exist for this institute
            $existingCount = FeeType::where('institute_id', $institute->id)->count();
            
            if ($existingCount > 0) {
                $this->command->info("Fee types already exist for institute: {$institute->name} (ID: {$institute->id}), skipping.");
                continue;
            }

            foreach ($feeTypesTemplate as $feeType) {
                FeeType::create([
                    'institute_id' => $institute->id,
                    ...$feeType,
                ]);
                $totalCreated++;
            }

            $this->command->info("Created 8 fee types for institute: {$institute->name} (ID: {$institute->id})");
        }

        $this->command->info("Total fee types created: {$totalCreated}");
    }
}
