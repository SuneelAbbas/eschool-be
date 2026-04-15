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

        foreach ($grades as $grade) {
            foreach ($feeTypes as $feeType) {
                GradeFee::firstOrCreate(
                    [
                        'grade_id' => $grade->id,
                        'fee_type_id' => $feeType->id,
                    ],
                    [
                        'amount' => $feeType->amount,
                        'effective_from' => now()->startOfYear(),
                        'effective_to' => null,
                    ]
                );
            }
        }

        $this->command->info('Grade fees seeded for ' . $grades->count() . ' grades and ' . $feeTypes->count() . ' fee types.');
    }
}
