<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $instituteId = DB::table('institutes')->value('id');

        if (!$instituteId) {
            $this->command->warn('No institute found. Please create an institute first.');
            return;
        }

        $grades = DB::table('grades')->where('institute_id', $instituteId)->get();

        if ($grades->isEmpty()) {
            $this->command->warn('No grades found. Please run GradeSeeder first.');
            return;
        }

        $sections = [];
        $gradeMap = $grades->keyBy('name');

        foreach ($gradeMap as $gradeName => $grade) {
            $gradeNum = str_replace('Grade ', '', $gradeName);

            if (in_array($gradeNum, ['1', '2', '3', '4', '5'])) {
                $sectionNames = ['A', 'B'];
            } elseif (in_array($gradeNum, ['6', '7', '8'])) {
                $sectionNames = ['A', 'B', 'C'];
            } else {
                $sectionNames = ['A', 'B'];
            }

            foreach ($sectionNames as $sectionName) {
                $sections[] = [
                    'grade_id' => $grade->id,
                    'name' => "{$gradeNum}-{$sectionName}",
                    'room_no' => "Room " . rand(100, 299),
                    'capacity' => 30,
                    'class_teacher' => null,
                    'institute_id' => $instituteId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('sections')->insertOrIgnore($sections);
    }
}
