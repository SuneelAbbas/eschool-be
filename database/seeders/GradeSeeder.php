<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $instituteId = DB::table('institutes')->value('id');

        if (!$instituteId) {
            $this->command->warn('No institute found. Please create an institute first.');
            return;
        }

        $grades = [
            [
                'name' => 'Grade 1',
                'description' => 'Primary Level - First Year',
                'institute_id' => $instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grade 2',
                'description' => 'Primary Level - Second Year',
                'institute_id' => $instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grade 3',
                'description' => 'Primary Level - Third Year',
                'institute_id' => $instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grade 4',
                'description' => 'Primary Level - Fourth Year',
                'institute_id' => $instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grade 5',
                'description' => 'Primary Level - Fifth Year',
                'institute_id' => $instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grade 6',
                'description' => 'Middle Level - First Year (Junior High)',
                'institute_id' => $instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grade 7',
                'description' => 'Middle Level - Second Year (Junior High)',
                'institute_id' => $instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grade 8',
                'description' => 'Middle Level - Third Year (Junior High)',
                'institute_id' => $instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grade 9',
                'description' => 'Secondary Level - First Year (Matriculation)',
                'institute_id' => $instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grade 10',
                'description' => 'Secondary Level - Second Year (Matriculation)',
                'institute_id' => $instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grade 11',
                'description' => 'Higher Secondary - First Year (Intermediate)',
                'institute_id' => $instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grade 12',
                'description' => 'Higher Secondary - Second Year (Intermediate)',
                'institute_id' => $instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('grades')->insertOrIgnore($grades);
    }
}
