<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $instituteId = DB::table('institutes')->value('id');

        if (!$instituteId) {
            $this->command->warn('No institute found. Please create an institute first.');
            return;
        }

        // Get ALL fee category IDs for random assignment
        $feeCategoryIds = DB::table('fee_categories')->pluck('id')->toArray();

        if (empty($feeCategoryIds)) {
            $this->command->warn('No fee categories found. Please run FeeCategorySeeder first.');
            return;
        }

        $sections = DB::table('sections')
            ->where('institute_id', $instituteId)
            ->get();

        if ($sections->isEmpty()) {
            $this->command->warn('No sections found. Please run SectionSeeder first.');
            return;
        }

        $grade9SectionA = $sections->firstWhere('name', '9-A');
        $grade9SectionB = $sections->firstWhere('name', '9-B');
        $grade10SectionA = $sections->firstWhere('name', '10-A');
        $grade11SectionA = $sections->firstWhere('name', '11-A');

        $students = [
            // Grade 9-A
            [
                'first_name' => 'Fatima',
                'last_name' => 'Iqbal',
                'email' => 'fatima.iqbal@eschool.pk',
                'registration_date' => '2025-03-01',
                'registration_number' => 'REG-2025-001',
                'roll_no' => '9A-001',
                'gender' => 'female',
                'mobile_number' => '0300-1112233',
                'parents_name' => 'Muhammad Iqbal',
                'parents_mobile_number' => '0301-1234567',
                'date_of_birth' => '2010-05-15',
                'blood_group' => 'A+',
                'address' => 'House 12, Block B, Satellite Town, Gilgit',
                'section_id' => $grade9SectionA->id ?? null,
            ],
            [
                'first_name' => 'Ali',
                'last_name' => 'Raza',
                'email' => 'ali.raza@eschool.pk',
                'registration_date' => '2025-03-01',
                'registration_number' => 'REG-2025-002',
                'roll_no' => '9A-002',
                'gender' => 'male',
                'mobile_number' => '0300-2223344',
                'parents_name' => 'Hassan Raza',
                'parents_mobile_number' => '0302-2345678',
                'date_of_birth' => '2010-08-22',
                'blood_group' => 'O+',
                'address' => 'House 5, Block C, Jutial, Gilgit',
                'section_id' => $grade9SectionA->id ?? null,
            ],
            [
                'first_name' => 'Sara',
                'last_name' => 'Khan',
                'email' => 'sara.khan@eschool.pk',
                'registration_date' => '2025-03-02',
                'registration_number' => 'REG-2025-003',
                'roll_no' => '9A-003',
                'gender' => 'female',
                'mobile_number' => '0300-3334455',
                'parents_name' => 'Imran Khan',
                'parents_mobile_number' => '0303-3456789',
                'date_of_birth' => '2010-01-10',
                'blood_group' => 'B+',
                'address' => 'House 8, Block A, Civil Lines, Gilgit',
                'section_id' => $grade9SectionA->id ?? null,
            ],

            // Grade 9-B
            [
                'first_name' => 'Aisha',
                'last_name' => 'Nawaz',
                'email' => 'aisha.nawaz@eschool.pk',
                'registration_date' => '2025-03-03',
                'registration_number' => 'REG-2025-004',
                'roll_no' => '9B-001',
                'gender' => 'female',
                'mobile_number' => '0300-4445566',
                'parents_name' => 'Khalid Nawaz',
                'parents_mobile_number' => '0304-4567890',
                'date_of_birth' => '2010-03-25',
                'blood_group' => 'AB+',
                'address' => 'House 15, Block D, Alnoor Colony, Gilgit',
                'section_id' => $grade9SectionB->id ?? null,
            ],
            [
                'first_name' => 'Hamza',
                'last_name' => 'Ali',
                'email' => 'hamza.ali@eschool.pk',
                'registration_date' => '2025-03-04',
                'registration_number' => 'REG-2025-005',
                'roll_no' => '9B-002',
                'gender' => 'male',
                'mobile_number' => '0300-5556677',
                'parents_name' => 'Shahid Ali',
                'parents_mobile_number' => '0305-5678901',
                'date_of_birth' => '2010-07-18',
                'blood_group' => 'A-',
                'address' => 'House 3, Block E, Sharah-e-Quaid, Gilgit',
                'section_id' => $grade9SectionB->id ?? null,
            ],

            // Grade 10-A
            [
                'first_name' => 'Bilal',
                'last_name' => 'Shah',
                'email' => 'bilal.shah@eschool.pk',
                'registration_date' => '2024-02-15',
                'registration_number' => 'REG-2024-001',
                'roll_no' => '10A-001',
                'gender' => 'male',
                'mobile_number' => '0300-6667788',
                'parents_name' => 'Asif Shah',
                'parents_mobile_number' => '0306-6789012',
                'date_of_birth' => '2009-11-05',
                'blood_group' => 'O-',
                'address' => 'House 22, Block F, Airport Road, Gilgit',
                'section_id' => $grade10SectionA->id ?? null,
            ],
            [
                'first_name' => 'Zara',
                'last_name' => 'Sheikh',
                'email' => 'zara.sheikh@eschool.pk',
                'registration_date' => '2024-02-16',
                'registration_number' => 'REG-2024-002',
                'roll_no' => '10A-002',
                'gender' => 'female',
                'mobile_number' => '0300-7778899',
                'parents_name' => 'Farhan Sheikh',
                'parents_mobile_number' => '0307-7890123',
                'date_of_birth' => '2009-04-12',
                'blood_group' => 'B-',
                'address' => 'House 18, Block G, Hospital Road, Gilgit',
                'section_id' => $grade10SectionA->id ?? null,
            ],
            [
                'first_name' => 'Hassan',
                'last_name' => 'Hussain',
                'email' => 'hassan.hussain@eschool.pk',
                'registration_date' => '2024-02-17',
                'registration_number' => 'REG-2024-003',
                'roll_no' => '10A-003',
                'gender' => 'male',
                'mobile_number' => '0300-8889900',
                'parents_name' => 'Raza Hussain',
                'parents_mobile_number' => '0308-8901234',
                'date_of_birth' => '2009-09-28',
                'blood_group' => 'A+',
                'address' => 'House 7, Block H, GPO Road, Gilgit',
                'section_id' => $grade10SectionA->id ?? null,
            ],

            // Grade 11-A
            [
                'first_name' => 'Mariam',
                'last_name' => 'Baig',
                'email' => 'mariam.baig@eschool.pk',
                'registration_date' => '2023-03-20',
                'registration_number' => 'REG-2023-001',
                'roll_no' => '11A-001',
                'gender' => 'female',
                'mobile_number' => '0300-9990011',
                'parents_name' => 'Kamran Baig',
                'parents_mobile_number' => '0309-9012345',
                'date_of_birth' => '2008-06-14',
                'blood_group' => 'O+',
                'address' => 'House 25, Block I, Danyore, Gilgit',
                'section_id' => $grade11SectionA->id ?? null,
            ],
            [
                'first_name' => 'Usman',
                'last_name' => 'Gul',
                'email' => 'usman.gul@eschool.pk',
                'registration_date' => '2023-03-21',
                'registration_number' => 'REG-2023-002',
                'roll_no' => '11A-002',
                'gender' => 'male',
                'mobile_number' => '0300-0001122',
                'parents_name' => 'Fazal Gul',
                'parents_mobile_number' => '0310-0123456',
                'date_of_birth' => '2008-02-20',
                'blood_group' => 'AB-',
                'address' => 'House 30, Block J, Sikandarabad, Gilgit',
                'section_id' => $grade11SectionA->id ?? null,
            ],
        ];

        $now = now();
        foreach ($students as &$student) {
            $student['institute_id'] = $instituteId;
            $student['fee_category_id'] = $feeCategoryIds[array_rand($feeCategoryIds)]; // Random category
            $student['created_at'] = $now;
            $student['updated_at'] = $now;
        }

        try {
            DB::table('students')->insert($students);
            $this->command->info('StudentSeeder: ' . count($students) . ' students seeded.');
        } catch (\Exception $e) {
            $this->command->error('StudentSeeder error: ' . $e->getMessage());
        }
    }
}
