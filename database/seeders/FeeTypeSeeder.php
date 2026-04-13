<?php

namespace Database\Seeders;

use App\Models\FeeType;
use Illuminate\Database\Seeder;

class FeeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $instituteId = 1; // Assuming institute ID 1 for demo

        $feeTypes = [
            [
                'institute_id' => $instituteId,
                'name' => 'Tuition Fee',
                'code' => 'TUITION',
                'amount' => 5000,
                'type' => 'monthly',
                'due_day' => 10,
                'description' => 'Monthly tuition fee for regular classes',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Lab Fee',
                'code' => 'LAB',
                'amount' => 1000,
                'type' => 'monthly',
                'due_day' => 10,
                'description' => 'Monthly laboratory fee for science subjects',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Library Fee',
                'code' => 'LIBRARY',
                'amount' => 500,
                'type' => 'monthly',
                'due_day' => 10,
                'description' => 'Monthly library maintenance fee',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Sports Fee',
                'code' => 'SPORTS',
                'amount' => 500,
                'type' => 'monthly',
                'due_day' => 10,
                'description' => 'Monthly sports and physical education fee',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Admission Fee',
                'code' => 'ADMISSION',
                'amount' => 5000,
                'type' => 'one_time',
                'due_day' => null,
                'description' => 'One-time admission processing fee',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Annual Exam Fee',
                'code' => 'EXAM',
                'amount' => 2000,
                'type' => 'one_time',
                'due_day' => null,
                'description' => 'Annual examination fee',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Transport Fee',
                'code' => 'TRANSPORT',
                'amount' => 2000,
                'type' => 'monthly',
                'due_day' => 10,
                'description' => 'Monthly transportation fee',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Activity Fee',
                'code' => 'ACTIVITY',
                'amount' => 800,
                'type' => 'monthly',
                'due_day' => 10,
                'description' => 'Monthly co-curricular activities fee',
                'is_active' => true,
            ],
        ];

        foreach ($feeTypes as $feeType) {
            FeeType::create($feeType);
        }
    }
}
