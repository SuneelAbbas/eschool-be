<?php

namespace Database\Seeders;

use App\Models\Institute;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Seeder;

class MassStudentSeeder extends Seeder
{
    public function run(): void
    {
        $institute = Institute::where('status', 'approved')->first();
        $section = Section::first();

        if (!$institute || !$section) {
            $this->command->error('No institute or section found. Run InstituteSeeder and SectionSeeder first.');
            return;
        }

        $firstNames = [
            'Ahmed', 'Fatima', 'Muhammad', 'Ayesha', 'Hassan', 'Zainab', 'Ali', 'Sara', 'Umar', 'Hira',
            'Bilal', 'Sana', 'Kamran', 'Mariam', 'Arif', 'Nadia', 'Usman', 'Saima', 'Adil', 'Rabia',
            'Imran', 'Sadia', 'Shahid', 'Kiran', 'Tariq', 'Amna', 'Rashid', 'Farah', 'Naveed', 'Gul',
            'Saad', 'Laraib', 'Faisal', 'Mina', 'Waseem', 'Hina', 'Azhar', 'Saba', 'Saqib', 'Uzma',
            'Shahid', 'Parveen', 'Tanvir', 'Shabana', 'Iqbal', 'Nasreen', 'Kashif', 'Tehreem', 'Humayun', 'Qurat',
        ];

        $lastNames = [
            'Khan', 'Ahmed', 'Ali', 'Hussain', 'Rashid', 'Malik', 'Qureshi', 'Sheikh', 'Butt', 'Nawaz',
            'Abbasi', 'Akram', 'Baig', 'Chaudhry', 'Dar', 'Farooq', 'Gul', 'Haider', 'Iqbal', 'Javed',
        ];

        $created = 0;
        for ($i = 0; $i < 100; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            Student::create([
                'institute_id' => $institute->id,
                'section_id' => $section->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName . $i) . '@test.com',
                'registration_number' => Student::generateRegistrationNumber($institute->id),
                'parents_name' => 'Mr. ' . $lastName,
                'date_of_birth' => '2010-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                'status' => 'active',
            ]);
            $created++;
        }

        $this->command->info("Created {$created} students.");
    }
}
