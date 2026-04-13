<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $institutesWithGrades = \App\Models\Institute::whereHas('grades')->get();

        if ($institutesWithGrades->isEmpty()) {
            $this->command->warn('No institutes with grades found. Please run GradeSeeder first.');
            return;
        }

        foreach ($institutesWithGrades as $institute) {
            $this->seedSubjectsForInstitute($institute);
        }

        $this->command->info('Subjects seeded successfully with grade-specific curriculum!');
    }

    protected function seedSubjectsForInstitute(\App\Models\Institute $institute): void
    {
        $this->command->info("Processing institute: {$institute->name}");

        $grades = Grade::where('institute_id', $institute->id)->get();
        $this->command->info("Found {$grades->count()} grades");

        $subjectDefinitions = $this->getSubjectDefinitions();
        $subjectMap = [];

        foreach ($subjectDefinitions as $subjectData) {
            $subject = Subject::updateOrCreate(
                [
                    'institute_id' => $institute->id,
                    'code' => $subjectData['code'],
                ],
                [
                    'name' => $subjectData['name'],
                    'description' => $subjectData['description'],
                ]
            );
            $subjectMap[$subjectData['code']] = $subject->id;
        }

        $this->command->info('Created/Updated ' . count($subjectDefinitions) . ' subjects.');

        $curriculum = $this->getCurriculumByGrade();

        $linkedCount = 0;

        foreach ($grades as $grade) {
            $gradeCurriculum = $curriculum[$grade->name] ?? null;

            if (!$gradeCurriculum) {
                $this->command->warn("No curriculum defined for {$grade->name}");
                continue;
            }

            foreach ($gradeCurriculum as $subjectCode => $config) {
                $subjectId = $subjectMap[$subjectCode] ?? null;

                if (!$subjectId) {
                    continue;
                }

                $exists = GradeSubject::where('grade_id', $grade->id)
                    ->where('subject_id', $subjectId)
                    ->exists();

                if (!$exists) {
                    GradeSubject::create([
                        'grade_id' => $grade->id,
                        'subject_id' => $subjectId,
                        'is_compulsory' => $config['compulsory'],
                        'max_marks' => $config['max_marks'] ?? null,
                    ]);
                    $linkedCount++;
                }
            }
        }

        $this->command->info("Linked {$linkedCount} subject-grade combinations for {$institute->name}");
    }

    protected function getSubjectDefinitions(): array
    {
        return [
            // Core Subjects (Common across all grades)
            [
                'name' => 'English',
                'code' => 'ENG',
                'description' => 'English Language and Literature',
            ],
            [
                'name' => 'Urdu',
                'code' => 'URD',
                'description' => 'Urdu Language and Literature',
            ],
            [
                'name' => 'Mathematics',
                'code' => 'MATH',
                'description' => 'Mathematics',
            ],
            [
                'name' => 'Islamiat',
                'code' => 'ISL',
                'description' => 'Islamic Studies',
            ],
            [
                'name' => 'Pakistan Studies',
                'code' => 'PKS',
                'description' => 'Pakistan Studies / Civics',
            ],

            // Primary Level Subjects (Grades 1-5)
            [
                'name' => 'Science',
                'code' => 'SCI',
                'description' => 'General Science (Combined)',
            ],
            [
                'name' => 'Social Studies',
                'code' => 'SS',
                'description' => 'Social Studies / History & Geography',
            ],
            [
                'name' => 'Art & Craft',
                'code' => 'ART',
                'description' => 'Art and Craft',
            ],
            [
                'name' => 'Physical Education',
                'code' => 'PE',
                'description' => 'Physical Education and Sports',
            ],

            // Middle Level Subjects (Grades 6-8)
            [
                'name' => 'Computer Science',
                'code' => 'CS',
                'description' => 'Computer Science / ICT',
            ],
            [
                'name' => 'General Science',
                'code' => 'GEN_SCI',
                'description' => 'General Science (Physics, chemistry, Biology)',
            ],
            [
                'name' => 'History',
                'code' => 'HIST',
                'description' => 'History',
            ],
            [
                'name' => 'Geography',
                'code' => 'GEO',
                'description' => 'Geography',
            ],

            // Matriculation Subjects (Grades 9-10)
            [
                'name' => 'Physics',
                'code' => 'PHY',
                'description' => 'Physics',
            ],
            [
                'name' => 'Chemistry',
                'code' => 'CHEM',
                'description' => 'Chemistry',
            ],
            [
                'name' => 'Biology',
                'code' => 'BIO',
                'description' => 'Biology',
            ],

            // Elective Subjects
            [
                'name' => 'Arabic',
                'code' => 'ARB',
                'description' => 'Arabic Language',
            ],
            [
                'name' => 'Computer',
                'code' => 'COMP',
                'description' => 'Computer (Matriculation Level)',
            ],
            [
                'name' => 'Drawing',
                'code' => 'DRW',
                'description' => 'Drawing / Fine Arts',
            ],

            // Intermediate Subjects (Grades 11-12)
            [
                'name' => 'Advanced Mathematics',
                'code' => 'A_MATH',
                'description' => 'Advanced / Applied Mathematics',
            ],
            [
                'name' => 'Statistics',
                'code' => 'STAT',
                'description' => 'Statistics',
            ],
            [
                'name' => 'Economics',
                'code' => 'ECO',
                'description' => 'Economics',
            ],
            [
                'name' => 'Commerce',
                'code' => 'COM',
                'description' => 'Commerce / Business Studies',
            ],
            [
                'name' => 'Accounting',
                'code' => 'ACC',
                'description' => 'Accounting / Book Keeping',
            ],
            [
                'name' => 'Civics',
                'code' => 'CIV',
                'description' => 'Civics / Government',
            ],
            [
                'name' => 'Psychology',
                'code' => 'PSY',
                'description' => 'Psychology',
            ],
            [
                'name' => 'Sociology',
                'code' => 'SOC',
                'description' => 'Sociology',
            ],
            [
                'name' => 'Philosophy',
                'code' => 'PHI',
                'description' => 'Philosophy',
            ],
        ];
    }

    protected function getCurriculumByGrade(): array
    {
        return [
            'Grade 1' => [
                'ENG' => ['compulsory' => true, 'max_marks' => 100],
                'URD' => ['compulsory' => true, 'max_marks' => 100],
                'MATH' => ['compulsory' => true, 'max_marks' => 100],
                'ISL' => ['compulsory' => true, 'max_marks' => 50],
                'SCI' => ['compulsory' => true, 'max_marks' => 50],
                'ART' => ['compulsory' => true, 'max_marks' => 50],
                'PE' => ['compulsory' => true, 'max_marks' => 50],
            ],
            'Grade 2' => [
                'ENG' => ['compulsory' => true, 'max_marks' => 100],
                'URD' => ['compulsory' => true, 'max_marks' => 100],
                'MATH' => ['compulsory' => true, 'max_marks' => 100],
                'ISL' => ['compulsory' => true, 'max_marks' => 50],
                'SCI' => ['compulsory' => true, 'max_marks' => 50],
                'SS' => ['compulsory' => true, 'max_marks' => 50],
                'ART' => ['compulsory' => true, 'max_marks' => 50],
                'PE' => ['compulsory' => true, 'max_marks' => 50],
            ],
            'Grade 3' => [
                'ENG' => ['compulsory' => true, 'max_marks' => 100],
                'URD' => ['compulsory' => true, 'max_marks' => 100],
                'MATH' => ['compulsory' => true, 'max_marks' => 100],
                'ISL' => ['compulsory' => true, 'max_marks' => 75],
                'SCI' => ['compulsory' => true, 'max_marks' => 75],
                'SS' => ['compulsory' => true, 'max_marks' => 75],
                'CS' => ['compulsory' => false, 'max_marks' => 50],
                'ART' => ['compulsory' => true, 'max_marks' => 50],
                'PE' => ['compulsory' => true, 'max_marks' => 50],
            ],
            'Grade 4' => [
                'ENG' => ['compulsory' => true, 'max_marks' => 100],
                'URD' => ['compulsory' => true, 'max_marks' => 100],
                'MATH' => ['compulsory' => true, 'max_marks' => 100],
                'ISL' => ['compulsory' => true, 'max_marks' => 75],
                'PKS' => ['compulsory' => true, 'max_marks' => 75],
                'SCI' => ['compulsory' => true, 'max_marks' => 75],
                'SS' => ['compulsory' => true, 'max_marks' => 75],
                'CS' => ['compulsory' => false, 'max_marks' => 50],
                'ART' => ['compulsory' => true, 'max_marks' => 50],
                'PE' => ['compulsory' => true, 'max_marks' => 50],
            ],
            'Grade 5' => [
                'ENG' => ['compulsory' => true, 'max_marks' => 100],
                'URD' => ['compulsory' => true, 'max_marks' => 100],
                'MATH' => ['compulsory' => true, 'max_marks' => 100],
                'ISL' => ['compulsory' => true, 'max_marks' => 75],
                'PKS' => ['compulsory' => true, 'max_marks' => 75],
                'SCI' => ['compulsory' => true, 'max_marks' => 100],
                'SS' => ['compulsory' => true, 'max_marks' => 75],
                'CS' => ['compulsory' => false, 'max_marks' => 75],
                'ART' => ['compulsory' => true, 'max_marks' => 50],
                'PE' => ['compulsory' => true, 'max_marks' => 50],
            ],
            'Grade 6' => [
                'ENG' => ['compulsory' => true, 'max_marks' => 100],
                'URD' => ['compulsory' => true, 'max_marks' => 100],
                'MATH' => ['compulsory' => true, 'max_marks' => 100],
                'ISL' => ['compulsory' => true, 'max_marks' => 75],
                'PKS' => ['compulsory' => true, 'max_marks' => 75],
                'GEN_SCI' => ['compulsory' => true, 'max_marks' => 100],
                'HIST' => ['compulsory' => true, 'max_marks' => 75],
                'GEO' => ['compulsory' => true, 'max_marks' => 75],
                'CS' => ['compulsory' => false, 'max_marks' => 75],
                'ARB' => ['compulsory' => false, 'max_marks' => 50],
                'ART' => ['compulsory' => true, 'max_marks' => 50],
                'PE' => ['compulsory' => true, 'max_marks' => 50],
            ],
            'Grade 7' => [
                'ENG' => ['compulsory' => true, 'max_marks' => 100],
                'URD' => ['compulsory' => true, 'max_marks' => 100],
                'MATH' => ['compulsory' => true, 'max_marks' => 100],
                'ISL' => ['compulsory' => true, 'max_marks' => 75],
                'PKS' => ['compulsory' => true, 'max_marks' => 75],
                'GEN_SCI' => ['compulsory' => true, 'max_marks' => 100],
                'HIST' => ['compulsory' => true, 'max_marks' => 75],
                'GEO' => ['compulsory' => true, 'max_marks' => 75],
                'CS' => ['compulsory' => false, 'max_marks' => 75],
                'ARB' => ['compulsory' => false, 'max_marks' => 50],
                'ART' => ['compulsory' => true, 'max_marks' => 50],
                'PE' => ['compulsory' => true, 'max_marks' => 50],
            ],
            'Grade 8' => [
                'ENG' => ['compulsory' => true, 'max_marks' => 100],
                'URD' => ['compulsory' => true, 'max_marks' => 100],
                'MATH' => ['compulsory' => true, 'max_marks' => 100],
                'ISL' => ['compulsory' => true, 'max_marks' => 75],
                'PKS' => ['compulsory' => true, 'max_marks' => 75],
                'GEN_SCI' => ['compulsory' => true, 'max_marks' => 100],
                'HIST' => ['compulsory' => true, 'max_marks' => 75],
                'GEO' => ['compulsory' => true, 'max_marks' => 75],
                'CS' => ['compulsory' => false, 'max_marks' => 75],
                'ARB' => ['compulsory' => false, 'max_marks' => 50],
                'ART' => ['compulsory' => true, 'max_marks' => 50],
                'PE' => ['compulsory' => true, 'max_marks' => 50],
            ],
            // Matriculation - Science Group
            'Grade 9' => [
                'ENG' => ['compulsory' => true, 'max_marks' => 75],
                'URD' => ['compulsory' => true, 'max_marks' => 75],
                'MATH' => ['compulsory' => true, 'max_marks' => 75],
                'ISL' => ['compulsory' => true, 'max_marks' => 50],
                'PKS' => ['compulsory' => true, 'max_marks' => 50],
                'PHY' => ['compulsory' => true, 'max_marks' => 75],
                'CHEM' => ['compulsory' => true, 'max_marks' => 75],
                'BIO' => ['compulsory' => true, 'max_marks' => 75],
                'CS' => ['compulsory' => false, 'max_marks' => 75],
                'COMP' => ['compulsory' => false, 'max_marks' => 75],
                'ARB' => ['compulsory' => false, 'max_marks' => 50],
                'DRW' => ['compulsory' => false, 'max_marks' => 50],
                'PE' => ['compulsory' => true, 'max_marks' => 50],
            ],
            'Grade 10' => [
                'ENG' => ['compulsory' => true, 'max_marks' => 75],
                'URD' => ['compulsory' => true, 'max_marks' => 75],
                'MATH' => ['compulsory' => true, 'max_marks' => 75],
                'ISL' => ['compulsory' => true, 'max_marks' => 50],
                'PKS' => ['compulsory' => true, 'max_marks' => 50],
                'PHY' => ['compulsory' => true, 'max_marks' => 75],
                'CHEM' => ['compulsory' => true, 'max_marks' => 75],
                'BIO' => ['compulsory' => true, 'max_marks' => 75],
                'CS' => ['compulsory' => false, 'max_marks' => 75],
                'COMP' => ['compulsory' => false, 'max_marks' => 75],
                'ARB' => ['compulsory' => false, 'max_marks' => 50],
                'DRW' => ['compulsory' => false, 'max_marks' => 50],
                'PE' => ['compulsory' => true, 'max_marks' => 50],
            ],
            // Intermediate - Pre-Engineering
            'Grade 11' => [
                'ENG' => ['compulsory' => true, 'max_marks' => 100],
                'URD' => ['compulsory' => true, 'max_marks' => 100],
                'ISL' => ['compulsory' => true, 'max_marks' => 50],
                'PHY' => ['compulsory' => true, 'max_marks' => 100],
                'CHEM' => ['compulsory' => true, 'max_marks' => 100],
                'MATH' => ['compulsory' => true, 'max_marks' => 100],
                'A_MATH' => ['compulsory' => false, 'max_marks' => 100],
                'CS' => ['compulsory' => false, 'max_marks' => 100],
                'ARB' => ['compulsory' => false, 'max_marks' => 50],
            ],
            'Grade 12' => [
                'ENG' => ['compulsory' => true, 'max_marks' => 100],
                'URD' => ['compulsory' => true, 'max_marks' => 100],
                'ISL' => ['compulsory' => true, 'max_marks' => 50],
                'PHY' => ['compulsory' => true, 'max_marks' => 100],
                'CHEM' => ['compulsory' => true, 'max_marks' => 100],
                'MATH' => ['compulsory' => true, 'max_marks' => 100],
                'A_MATH' => ['compulsory' => false, 'max_marks' => 100],
                'CS' => ['compulsory' => false, 'max_marks' => 100],
                'ARB' => ['compulsory' => false, 'max_marks' => 50],
            ],
        ];
    }
}
