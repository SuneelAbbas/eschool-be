<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $defaultSubjects = [
            ['name' => 'Mathematics', 'code' => 'MATH', 'description' => 'Mathematics'],
            ['name' => 'English', 'code' => 'ENG', 'description' => 'English Language'],
            ['name' => 'Urdu', 'code' => 'URD', 'description' => 'Urdu Language'],
            ['name' => 'Science', 'code' => 'SCI', 'description' => 'General Science'],
            ['name' => 'Islamiat', 'code' => 'ISL', 'description' => 'Islamic Studies'],
            ['name' => 'Pakistan Studies', 'code' => 'PKS', 'description' => 'Pakistan Studies'],
            ['name' => 'Computer Science', 'code' => 'CS', 'description' => 'Computer Science'],
            ['name' => 'Physics', 'code' => 'PHY', 'description' => 'Physics'],
            ['name' => 'Chemistry', 'code' => 'CHEM', 'description' => 'Chemistry'],
            ['name' => 'Biology', 'code' => 'BIO', 'description' => 'Biology'],
            ['name' => 'History', 'code' => 'HIST', 'description' => 'History'],
            ['name' => 'Geography', 'code' => 'GEO', 'description' => 'Geography'],
            ['name' => 'Art & Drawing', 'code' => 'ART', 'description' => 'Art & Drawing'],
            ['name' => 'Physical Education', 'code' => 'PE', 'description' => 'Physical Education'],
            ['name' => 'Arabic', 'code' => 'ARB', 'description' => 'Arabic Language'],
        ];

        foreach ($defaultSubjects as $subject) {
            Subject::create($subject);
        }
    }
}
