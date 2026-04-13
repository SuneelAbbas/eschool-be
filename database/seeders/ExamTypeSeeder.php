<?php

namespace Database\Seeders;

use App\Models\ExamType;
use App\Models\Institute;
use Illuminate\Database\Seeder;

class ExamTypeSeeder extends Seeder
{
    public function run(): void
    {
        $instituteId = Institute::first()?->id ?? 1;

        $examTypes = [
            [
                'institute_id' => $instituteId,
                'name' => 'Unit Test 1',
                'code' => 'UT1',
                'type' => 'unit_test',
                'max_marks' => 25,
                'description' => 'First unit test of the term',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Unit Test 2',
                'code' => 'UT2',
                'type' => 'unit_test',
                'max_marks' => 25,
                'description' => 'Second unit test of the term',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Unit Test 3',
                'code' => 'UT3',
                'type' => 'unit_test',
                'max_marks' => 25,
                'description' => 'Third unit test of the term',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Mid Term',
                'code' => 'MT',
                'type' => 'terminal',
                'max_marks' => 50,
                'description' => 'Mid-term examination',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Terminal Exam',
                'code' => 'TERM',
                'type' => 'terminal',
                'max_marks' => 100,
                'description' => 'End of term terminal examination',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Annual Exam',
                'code' => 'ANNUAL',
                'type' => 'annual',
                'max_marks' => 100,
                'description' => 'Annual final examination',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Matric Preparation',
                'code' => 'MAT-PREP',
                'type' => 'board_prep',
                'max_marks' => 100,
                'description' => 'Matriculation board exam preparation tests',
                'is_active' => true,
            ],
            [
                'institute_id' => $instituteId,
                'name' => 'Intermediate Preparation',
                'code' => 'INT-PREP',
                'type' => 'board_prep',
                'max_marks' => 100,
                'description' => 'Intermediate board exam preparation tests',
                'is_active' => true,
            ],
        ];

        foreach ($examTypes as $examType) {
            ExamType::firstOrCreate(
                ['code' => $examType['code'], 'institute_id' => $instituteId],
                $examType
            );
        }
    }
}
