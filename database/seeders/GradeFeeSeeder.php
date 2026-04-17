<?php

namespace Database\Seeders;

use App\Models\FeeType;
use App\Models\Grade;
use App\Models\GradeFee;
use Illuminate\Database\Seeder;

class GradeFeeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = Grade::all();
        $feeTypes = FeeType::where('is_active', true)->get();
        
        $currentYear = (int) now()->year;
        $currentMonth = (int) now()->month;
        
        if ($currentMonth >= 6) {
            $academicYear = "{$currentYear}-" . ($currentYear + 1);
        } else {
            $academicYear = ($currentYear - 1) . "-{$currentYear}";
        }

        foreach ($grades as $grade) {
            foreach ($feeTypes as $feeType) {
                GradeFee::firstOrCreate(
                    [
                        'grade_id' => $grade->id,
                        'fee_type_id' => $feeType->id,
                        'academic_year' => $academicYear,
                    ],
                    [
                        'amount' => $feeType->amount,
                        'effective_from' => now()->startOfYear(),
                        'effective_to' => null,
                    ]
                );
            }
        }

        $this->command->info("Grade fees seeded for {$grades->count()} grades and {$feeTypes->count()} fee types for academic year {$academicYear}.");
    }
}
