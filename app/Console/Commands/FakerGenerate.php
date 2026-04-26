<?php

namespace App\Console\Commands;

use App\Services\FakerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FakerGenerate extends Command
{
    protected $signature = 'faker:generate 
                            {institute_id : The ID of the institute}
                            {--grades=5 : Number of grades to generate}
                            {--sections=2 : Sections per grade}
                            {--subjects=8 : Number of subjects}
                            {--teachers=10 : Number of teachers}
                            {--students-min=20 : Minimum students per section}
                            {--students-max=40 : Maximum students per section}
                            {--fees=6 : Number of fee types}
                            {--exams=3 : Number of exams}
                            {--clear : Clear existing data before generating}
                            {--with-attendance : Generate attendance records}
                            {--with-results : Generate exam results}
                            {--with-teacher-subjects : Assign teachers to subjects/sections}';

    protected $description = 'Generate fake data for an institute using Faker';

    public function handle(): int
    {
        $instituteId = (int) $this->argument('institute_id');

        $instituteExists = DB::table('institutes')->where('id', $instituteId)->exists();
        if (!$instituteExists) {
            $this->error("Institute with ID {$instituteId} does not exist.");
            return Command::FAILURE;
        }

        $institute = DB::table('institutes')->where('id', $instituteId)->first();
        $this->info("Generating fake data for: {$institute->name} (ID: {$instituteId})");
        $this->newLine();

        $faker = new FakerService($instituteId);
        $faker->setCommand($this);

        if ($this->option('clear')) {
            $this->warn('Clearing existing data...');
            $deleted = $faker->clearInstituteData();
            $this->info("Deleted {$deleted} records.");
            $this->newLine();
        }

        $options = [
            'grades' => (int) $this->option('grades'),
            'sections_per_grade' => (int) $this->option('sections'),
            'subjects' => (int) $this->option('subjects'),
            'teachers' => (int) $this->option('teachers'),
            'students_min' => (int) $this->option('students-min'),
            'students_max' => (int) $this->option('students-max'),
            'fee_types' => (int) $this->option('fees'),
            'exams' => (int) $this->option('exams'),
            'exam_results' => $this->option('with-results'),
            'attendance' => $this->option('with-attendance'),
            'teacher_subjects' => $this->option('with-teacher-subjects'),
        ];

        $this->info('Generating data with options:');
        foreach ($options as $key => $value) {
            $this->line("  - {$key}: " . ($value === true ? 'yes' : ($value === false ? 'no' : $value)));
        }
        $this->newLine();

        $results = $faker->generateAll($options);

        if ($this->option('with-attendance')) {
            $this->info('Generating attendance records...');
            $attendanceCount = $faker->generateAttendance(30);
            $this->info("✓ Generated {$attendanceCount} attendance records");
        }

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('✓ Fake data generation complete!');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $totalStudents = DB::table('students')->where('institute_id', $instituteId)->count();
        $totalTeachers = DB::table('teachers')->where('institute_id', $instituteId)->count();
        $totalSections = DB::table('sections')->where('institute_id', $instituteId)->count();

        $this->table(
            ['Entity', 'Count'],
            [
                ['Grades', count($results['grades'] ?? [])],
                ['Sections', count($results['sections'] ?? [])],
                ['Subjects', count($results['subjects'] ?? [])],
                ['Teachers', count($results['teachers'] ?? [])],
                ['Students', $totalStudents],
                ['Fee Types', count($results['fee_types'] ?? [])],
                ['Exams', count($results['exams'] ?? [])],
            ]
        );

        return Command::SUCCESS;
    }
}
