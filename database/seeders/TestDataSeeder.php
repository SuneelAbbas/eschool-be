<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $instituteId = 1;

        // Create 2 grades
        $grade1 = Grade::create(['name' => 'Class 1', 'institute_id' => $instituteId]);
        $grade2 = Grade::create(['name' => 'Class 2', 'institute_id' => $instituteId]);

        // Create 2 sections each
        $sec1A = Section::create(['name' => 'A', 'grade_id' => $grade1->id, 'institute_id' => $instituteId]);
        $sec1B = Section::create(['name' => 'B', 'grade_id' => $grade1->id, 'institute_id' => $instituteId]);
        $sec2A = Section::create(['name' => 'A', 'grade_id' => $grade2->id, 'institute_id' => $instituteId]);
        $sec2B = Section::create(['name' => 'B', 'grade_id' => $grade2->id, 'institute_id' => $instituteId]);

        // Create 5 students per section (20 total)
        $sections = [$sec1A, $sec1B, $sec2A, $sec2B];
        
        foreach ($sections as $section) {
            Student::factory()->count(5)->create([
                'section_id' => $section->id,
                'institute_id' => $instituteId,
            ]);
        }

        $this->command->info('Created 2 grades, 4 sections, 20 students');
    }
}