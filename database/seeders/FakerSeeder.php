<?php

namespace Database\Seeders;

use App\Services\FakerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FakerSeeder extends Seeder
{
    public function run(): void
    {
        $instituteId = $this->command->choice(
            'Select an institute ID for fake data generation',
            $this->getInstituteIds(),
            1
        );

        $faker = new FakerService((int) $instituteId);
        $faker->setCommand($this->command);

        $this->command->info("Generating fake data for institute ID: {$instituteId}");
        $this->command->newLine();

        $options = [
            'grades' => (int) $this->command->ask('Number of grades', 5),
            'sections_per_grade' => (int) $this->command->ask('Sections per grade', 2),
            'subjects' => (int) $this->command->ask('Number of subjects', 8),
            'teachers' => (int) $this->command->ask('Number of teachers', 10),
            'students_min' => (int) $this->command->ask('Min students per section', 20),
            'students_max' => (int) $this->command->ask('Max students per section', 40),
            'fee_types' => (int) $this->command->ask('Number of fee types', 6),
            'exams' => (int) $this->command->ask('Number of exams', 3),
            'exam_results' => $this->command->confirm('Generate exam results?', true),
            'attendance' => $this->command->confirm('Generate attendance records?', false),
        ];

        $faker->generateAll($options);
    }

    private function getInstituteIds(): array
    {
        $institutes = DB::table('institutes')->pluck('id')->toArray();
        return array_map('strval', $institutes);
    }
}
