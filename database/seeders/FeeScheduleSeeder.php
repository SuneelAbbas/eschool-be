<?php

namespace Database\Seeders;

use App\Models\FeeSchedule;
use App\Models\FeeType;
use App\Models\Grade;
use App\Models\Institute;
use App\Models\FeeCategory;
use Illuminate\Database\Seeder;

class FeeScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $institutes = Institute::all();

        if ($institutes->isEmpty()) {
            $this->command->warn('No institutes found. Please run InstituteSeeder first.');
            return;
        }

        $totalCreated = 0;

        foreach ($institutes as $institute) {
            // Get all grades for this institute
            $grades = Grade::where('institute_id', $institute->id)->get();
            
            if ($grades->isEmpty()) {
                $this->command->warn("No grades found for institute: {$institute->name}");
                continue;
            }

            // Get fee types for this institute
            $feeTypes = FeeType::where('institute_id', $institute->id)->get();
            
            if ($feeTypes->isEmpty()) {
                $this->command->warn("No fee types found for institute: {$institute->name}");
                continue;
            }

            // Check if schedules already exist
            $existingCount = FeeSchedule::where('institute_id', $institute->id)->count();
            if ($existingCount > 0) {
                $this->command->info("Fee schedules already exist for institute: {$institute->name}, skipping.");
                continue;
            }

            // Create schedules for each grade and fee type
            foreach ($grades as $grade) {
                foreach ($feeTypes as $feeType) {
                    // Skip one-time fees for now (handled separately)
                    if ($feeType->type === 'one_time') {
                        continue;
                    }

                    // Define amount based on grade level
                    $baseAmount = $this->getAmountForFeeType($feeType->code);
                    $gradeMultiplier = $this->getGradeMultiplier($grade->name);
                    $amount = $baseAmount * $gradeMultiplier;

                    FeeSchedule::create([
                        'institute_id' => $institute->id,
                        'grade_id' => $grade->id,
                        'fee_type_id' => $feeType->id,
                        'fee_category_id' => null,
                        'amount' => $amount,
                        'frequency' => 'monthly',
                        'applicable_from' => now()->startOfYear(),
                        'applicable_to' => null,
                        'is_active' => true,
                    ]);
                    $totalCreated++;
                }
            }

            // Create one-time fee schedules (Admission Fee) - only for NEW students
            $admissionFee = $feeTypes->where('code', 'ADMISSION')->first();
            if ($admissionFee) {
                $newCategory = FeeCategory::where('institute_id', $institute->id)
                    ->where('code', 'NEW')
                    ->first();
                
                $feeCategoryId = $newCategory ? $newCategory->id : null;
                
                foreach ($grades as $grade) {
                    FeeSchedule::create([
                        'institute_id' => $institute->id,
                        'grade_id' => $grade->id,
                        'fee_type_id' => $admissionFee->id,
                        'fee_category_id' => $feeCategoryId,
                        'amount' => 5000,
                        'frequency' => 'one_time',
                        'applicable_from' => now()->startOfYear(),
                        'applicable_to' => null,
                        'is_active' => true,
                    ]);
                    $totalCreated++;
                }
            }

            $this->command->info("Created fee schedules for institute: {$institute->name}");
        }

        $this->command->info("Total fee schedules created: {$totalCreated}");
    }

    private function getAmountForFeeType(string $code): float
    {
        return match($code) {
            'TUITION' => 5000,
            'LAB' => 1000,
            'LIBRARY' => 500,
            'SPORTS' => 500,
            'TRANSPORT' => 2000,
            'ACTIVITY' => 800,
            'EXAM' => 2000,
            default => 1000,
        };
    }

    private function getGradeMultiplier(string $gradeName): float
    {
        // Extract grade number from name
        preg_match('/\d+/', $gradeName, $matches);
        $gradeNumber = isset($matches[0]) ? (int)$matches[0] : 1;
        
        // Higher grades pay slightly more
        return 1 + (($gradeNumber - 1) * 0.1);
    }
}
