<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamType;
use App\Models\FeeType;
use App\Models\Grade;
use App\Models\GradeFee;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FakerService
{
    private int $instituteId;
    private $faker;

    public function __construct(int $instituteId)
    {
        $this->instituteId = $instituteId;
        $this->faker = \Faker\Factory::create('en_PK');
    }

    public function getInstituteId(): int
    {
        return $this->instituteId;
    }

    public function generateAll(array $options = []): array
    {
        $counts = [
            'grades' => $options['grades'] ?? 5,
            'sections_per_grade' => $options['sections_per_grade'] ?? 2,
            'subjects' => $options['subjects'] ?? 8,
            'teachers' => $options['teachers'] ?? 10,
            'students_min' => $options['students_min'] ?? 20,
            'students_max' => $options['students_max'] ?? 40,
            'fee_types' => $options['fee_types'] ?? 6,
            'exams' => $options['exams'] ?? 3,
            'exam_results' => $options['exam_results'] ?? true,
            'attendance' => $options['attendance'] ?? true,
        ];

        $results = [];

        $results['grades'] = $this->generateGrades($counts['grades']);
        $this->command->info("✓ Generated {$counts['grades']} grades");

        $results['sections'] = $this->generateSections($counts['sections_per_grade']);
        $totalSections = count($results['sections']);
        $this->command->info("✓ Generated {$totalSections} sections");

        $results['subjects'] = $this->generateSubjects($counts['subjects']);
        $this->command->info("✓ Generated {$counts['subjects']} subjects");

        $results['teachers'] = $this->generateTeachers($counts['teachers']);
        $this->command->info("✓ Generated {$counts['teachers']} teachers");

        $results['teacher_sections'] = $this->assignTeachersToSections();
        $this->command->info("✓ Assigned teachers to sections");

        $results['fee_types'] = $this->generateFeeTypes($counts['fee_types']);
        $this->command->info("✓ Generated {$counts['fee_types']} fee types");

        $results['grade_fees'] = $this->generateGradeFees();
        $this->command->info("✓ Generated grade fees");

        $results['students'] = $this->generateStudents($counts['students_min'], $counts['students_max']);
        $this->command->info("✓ Generated {$counts['students_min']}-{$counts['students_max']} students per section (random)");

        $results['discounts'] = $this->generateDiscounts(5);
        $this->command->info("✓ Generated 5 discount types");

        if ($counts['exams'] > 0) {
            $results['exams'] = $this->generateExams($counts['exams']);
            $this->command->info("✓ Generated {$counts['exams']} exams");
        }

        if ($counts['exam_results'] && !empty($results['exams'])) {
            $results['exam_results'] = $this->generateExamResults($results['exams']);
            $this->command->info("✓ Generated exam results");
        }

        return $results;
    }

    private ?object $command = null;

    public function setCommand($command): self
    {
        $this->command = $command;
        return $this;
    }

    public function generateGrades(int $count = 5): array
    {
        $grades = [];
        $gradeNames = [
            'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5',
            'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10',
            'Grade 11', 'Grade 12', 'ECD-1', 'ECD-2', 'Playgroup',
            'Nursery', 'LKG', 'UKG', 'Pre-Primary', 'Primary 1', 'Primary 2',
        ];

        for ($i = 0; $i < $count; $i++) {
            $name = $gradeNames[$i] ?? "Grade " . ($i + 1);
            $grades[] = DB::table('grades')->insertGetId([
                'name' => $name,
                'description' => $this->faker->sentence(6),
                'institute_id' => $this->instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $grades;
    }

    public function generateSections(int $sectionsPerGrade = 2): array
    {
        $grades = DB::table('grades')->where('institute_id', $this->instituteId)->get();
        $sections = [];
        $sectionNames = ['A', 'B', 'C', 'D', 'E', 'Morning', 'Evening', 'Regular'];

        foreach ($grades as $grade) {
            for ($i = 0; $i < $sectionsPerGrade; $i++) {
                $name = $sectionNames[$i] ?? chr(65 + $i);
                $sections[] = DB::table('sections')->insertGetId([
                    'grade_id' => $grade->id,
                    'institute_id' => $this->instituteId,
                    'name' => $name,
                    'room_no' => 'Room ' . $this->faker->numberBetween(100, 999),
                    'capacity' => $this->faker->numberBetween(20, 40),
                    'class_teacher' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $sections;
    }

    public function generateSubjects(int $count = 8): array
    {
        $subjectData = [
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'English', 'code' => 'ENG'],
            ['name' => 'Urdu', 'code' => 'URD'],
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'Islamiyat', 'code' => 'ISL'],
            ['name' => 'Pakistan Studies', 'code' => 'PKS'],
            ['name' => 'Computer Science', 'code' => 'CS'],
            ['name' => 'Physics', 'code' => 'PHY'],
            ['name' => 'Chemistry', 'code' => 'CHEM'],
            ['name' => 'Biology', 'code' => 'BIO'],
            ['name' => 'History', 'code' => 'HIST'],
            ['name' => 'Geography', 'code' => 'GEO'],
        ];

        $subjects = [];
        $selectedSubjects = array_slice($subjectData, 0, min($count, count($subjectData)));

        foreach ($selectedSubjects as $subject) {
            $subjects[] = DB::table('subjects')->insertGetId([
                'name' => $subject['name'],
                'code' => $subject['code'],
                'description' => $this->faker->sentence(4),
                'institute_id' => $this->instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $subjects;
    }

    public function generateTeachers(int $count = 10): array
    {
        $teachers = [];
        $genders = ['male', 'female'];
        $qualifications = ['BSc', 'MSc', 'MA', 'BA', 'MPhil', 'PhD'];
        $subjects = ['Mathematics', 'English', 'Urdu', 'Science', 'Islamiyat', 'Computer Science'];

        for ($i = 0; $i < $count; $i++) {
            $gender = $this->faker->randomElement($genders);
            $firstName = $this->faker->firstName($gender);
            $lastName = $this->faker->lastName();

            $teachers[] = DB::table('teachers')->insertGetId([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName . '.' . $lastName) . $i . '@school.edu.pk',
                'cnic_number' => $this->faker->numerify('#####-#######-#'),
                'subject' => $this->faker->randomElement($subjects),
                'gender' => $gender,
                'mobile_number' => '03' . $this->faker->numerify('#########'),
                'join_date' => $this->faker->dateTimeBetween('-5 years', '-1 month')->format('Y-m-d'),
                'date_of_birth' => $this->faker->dateTimeBetween('-55 years', '-25 years')->format('Y-m-d'),
                'blood_group' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
                'address' => $this->faker->address(),
                'academic_qualification' => $this->faker->randomElement($qualifications),
                'institute_id' => $this->instituteId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $teachers;
    }

    public function assignTeachersToSections(): int
    {
        $sections = DB::table('sections')->where('institute_id', $this->instituteId)->get();
        $subjects = DB::table('subjects')->where('institute_id', $this->instituteId)->get();
        $teachers = DB::table('teachers')->where('institute_id', $this->instituteId)->get();

        if ($sections->isEmpty() || $teachers->isEmpty()) {
            return 0;
        }

        $assignments = 0;

        foreach ($sections as $section) {
            $sectionTeachers = $this->faker->randomElements($teachers->toArray(), min(4, $teachers->count()));

            foreach ($sectionTeachers as $teacher) {
                $subject = $this->faker->randomElement($subjects->toArray());

                $exists = DB::table('teacher_section')
                    ->where('teacher_id', $teacher->id)
                    ->where('section_id', $section->id)
                    ->where('subject_id', $subject->id)
                    ->exists();

                if (!$exists) {
                    DB::table('teacher_section')->insert([
                        'teacher_id' => $teacher->id,
                        'section_id' => $section->id,
                        'subject_id' => $subject->id,
                        'is_class_teacher' => $assignments === 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $assignments++;
                }
            }
        }

        return $assignments;
    }

    public function generateFeeTypes(int $count = 6): array
    {
        $feeTypes = [
            ['name' => 'Admission Fee', 'code' => 'ADM', 'type' => 'one_time', 'amount' => 5000],
            ['name' => 'Monthly Tuition', 'code' => 'TUIT', 'type' => 'monthly', 'amount' => 2000],
            ['name' => 'Annual Charges', 'code' => 'ANN', 'type' => 'one_time', 'amount' => 3000],
            ['name' => 'Examination Fee', 'code' => 'EXAM', 'type' => 'one_time', 'amount' => 1500],
            ['name' => 'Laboratory Fee', 'code' => 'LAB', 'type' => 'monthly', 'amount' => 500],
            ['name' => 'Sports Fee', 'code' => 'SPT', 'type' => 'monthly', 'amount' => 300],
            ['name' => 'Library Fee', 'code' => 'LIB', 'type' => 'monthly', 'amount' => 200],
            ['name' => 'Transport Fee', 'code' => 'TRANS', 'type' => 'monthly', 'amount' => 1500],
        ];

        $feeTypeIds = [];
        $selected = array_slice($feeTypes, 0, min($count, count($feeTypes)));

        foreach ($selected as $fee) {
            $feeTypeIds[] = DB::table('fee_types')->insertGetId([
                'institute_id' => $this->instituteId,
                'name' => $fee['name'],
                'code' => $fee['code'],
                'amount' => $fee['amount'],
                'type' => $fee['type'],
                'due_day' => $fee['type'] === 'monthly' ? $this->faker->randomElement([5, 10, 15, 20]) : null,
                'description' => $this->faker->sentence(3),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $feeTypeIds;
    }

    public function generateGradeFees(): array
    {
        $grades = DB::table('grades')->where('institute_id', $this->instituteId)->get();
        $feeTypes = DB::table('fee_types')->where('institute_id', $this->instituteId)->get();
        $gradeFees = [];
        $academicYear = (string) now()->year;

        foreach ($grades as $grade) {
            foreach ($feeTypes as $feeType) {
                $gradeFees[] = DB::table('grade_fees')->insertGetId([
                    'grade_id' => $grade->id,
                    'fee_type_id' => $feeType->id,
                    'academic_year' => $academicYear,
                    'amount' => $feeType->amount + ($grade->id * 100),
                    'effective_from' => now()->startOfYear()->format('Y-m-d'),
                    'effective_to' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $gradeFees;
    }

    public function generateStudents(int $minPerSection = 20, int $maxPerSection = 40): array
    {
        $sections = DB::table('sections')->where('institute_id', $this->instituteId)->get();
        $students = [];
        $genders = ['male', 'female'];
        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];

        foreach ($sections as $section) {
            $perSection = $this->faker->numberBetween($minPerSection, $maxPerSection);
            $sectionCapacity = $section->capacity ?? 30;
            $studentCount = min($perSection, $sectionCapacity);

            for ($i = 0; $i < $studentCount; $i++) {
                $gender = $this->faker->randomElement($genders);
                $firstName = $this->faker->firstName($gender);
                $lastName = $this->faker->lastName();

                $students[] = DB::table('students')->insertGetId([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => strtolower($firstName . '.' . $lastName . $i) . '@student.edu.pk',
                    'registration_date' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                    'registration_number' => 'REG-' . date('Y') . '-' . str_pad(DB::table('students')->count() + 1, 4, '0', STR_PAD_LEFT),
                    'roll_no' => $section->name . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'gender' => $gender,
                    'mobile_number' => '03' . $this->faker->numerify('#########'),
                    'parents_name' => $this->faker->name('male'),
                    'parents_mobile_number' => '03' . $this->faker->numerify('#########'),
                    'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-5 years')->format('Y-m-d'),
                    'blood_group' => $this->faker->randomElement($bloodGroups),
                    'address' => $this->faker->address(),
                    'institute_id' => $this->instituteId,
                    'section_id' => $section->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $students;
    }

    public function generateDiscounts(int $count = 5): array
    {
        $discountTypes = ['sibling', 'scholarship', 'need_based', 'merit', 'custom'];
        $discounts = [
            ['name' => 'Sibling Discount', 'percentage' => 10],
            ['name' => 'Annual Payment Discount', 'percentage' => 15],
            ['name' => 'Merit Scholarship', 'percentage' => 25],
            ['name' => 'Staff Child Waiver', 'percentage' => 50],
            ['name' => 'Early Bird Discount', 'percentage' => 5],
            ['name' => 'Need Based Support', 'percentage' => 30],
        ];

        $discountIds = [];
        $selected = array_slice($discounts, 0, min($count, count($discounts)));

        foreach ($selected as $index => $discount) {
            $discountIds[] = DB::table('discounts')->insertGetId([
                'institute_id' => $this->instituteId,
                'name' => $discount['name'],
                'code' => strtoupper(Str::random(5)),
                'type' => $discountTypes[$index] ?? 'custom',
                'percentage' => $discount['percentage'],
                'fixed_amount' => 0,
                'conditions' => null,
                'description' => $this->faker->sentence(3),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $discountIds;
    }

    public function generateExams(int $count = 3): array
    {
        $examTypes = DB::table('exam_types')->where('institute_id', $this->instituteId)->get();
        $grades = DB::table('grades')->where('institute_id', $this->instituteId)->get();
        $exams = [];

        if ($examTypes->isEmpty()) {
            $this->generateExamTypes();
            $examTypes = DB::table('exam_types')->where('institute_id', $this->instituteId)->get();
        }

        $titles = ['First Terminal', 'Second Terminal', 'Annual Final', 'Unit Test 1', 'Unit Test 2', 'Mid Term'];

        for ($i = 0; $i < $count; $i++) {
            $grade = $this->faker->randomElement($grades);
            $examType = $this->faker->randomElement($examTypes);
            $title = $titles[$i] ?? "Exam " . ($i + 1);
            $startDate = $this->faker->dateTimeBetween('-3 months', '+1 month');
            $endDate = (clone $startDate)->modify('+7 days');

            $exams[] = DB::table('exams')->insertGetId([
                'institute_id' => $this->instituteId,
                'exam_type_id' => $examType->id,
                'grade_id' => $grade->id,
                'section_id' => null,
                'title' => $title,
                'description' => $this->faker->sentence(5),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_marks' => $examType->max_marks ?? 100,
                'status' => $this->faker->randomElement(['scheduled', 'ongoing', 'completed']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $exams;
    }

    public function generateExamTypes(): array
    {
        $types = [
            ['name' => 'Unit Test', 'code' => 'UT', 'type' => 'unit_test', 'max_marks' => 25],
            ['name' => 'Terminal Exam', 'code' => 'TERM', 'type' => 'terminal', 'max_marks' => 100],
            ['name' => 'Annual Exam', 'code' => 'ANN', 'type' => 'annual', 'max_marks' => 100],
        ];

        $ids = [];
        foreach ($types as $type) {
            $ids[] = DB::table('exam_types')->insertGetId([
                'institute_id' => $this->instituteId,
                'name' => $type['name'],
                'code' => $type['code'],
                'type' => $type['type'],
                'max_marks' => $type['max_marks'],
                'description' => $this->faker->sentence(3),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $ids;
    }

    public function generateExamResults(array $examIds): int
    {
        $count = 0;

        foreach ($examIds as $examId) {
            $exam = DB::table('exams')->where('id', $examId)->first();
            if (!$exam) {
                continue;
            }

            $sections = DB::table('sections')->where('grade_id', $exam->grade_id)->get();
            $subjects = DB::table('subjects')->where('institute_id', $this->instituteId)->get();

            foreach ($sections as $section) {
                $students = DB::table('students')->where('section_id', $section->id)->get();

                foreach ($students as $student) {
                    foreach ($subjects as $subject) {
                        $totalMarks = $exam->total_marks ?? 100;
                        $obtainedMarks = $this->faker->numberBetween((int)($totalMarks * 0.3), $totalMarks);

                        DB::table('exam_results')->insert([
                            'institute_id' => $this->instituteId,
                            'exam_id' => $examId,
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'marks_obtained' => $obtainedMarks,
                            'total_marks' => $totalMarks,
                            'grade' => $this->calculateGrade($obtainedMarks, $totalMarks),
                            'remarks' => $this->faker->sentence(3),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    private function calculateGrade(float $obtained, float $total): string
    {
        $percentage = ($obtained / $total) * 100;

        return match (true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 40 => 'D',
            default => 'F',
        };
    }

    public function generateAttendance(int $days = 30): int
    {
        $students = DB::table('students')->where('institute_id', $this->instituteId)->get();
        $count = 0;

        foreach ($students as $student) {
            for ($i = 0; $i < $days; $i++) {
                $date = now()->subDays($i)->format('Y-m-d');
                $status = $this->faker->randomElement(['present', 'present', 'present', 'absent', 'late', 'excused']);

                if ($status === 'present') {
                    $checkIn = '08:00:00';
                    $checkOut = '14:00:00';
                } elseif ($status === 'late') {
                    $checkIn = '08:30:00';
                    $checkOut = '14:00:00';
                } else {
                    $checkIn = null;
                    $checkOut = null;
                }

                DB::table('attendance')->insert([
                    'institute_id' => $this->instituteId,
                    'student_id' => $student->id,
                    'section_id' => $student->section_id,
                    'date' => $date,
                    'status' => $status,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'remarks' => $status === 'absent' ? $this->faker->sentence(2) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        }

        return $count;
    }

    public function clearInstituteData(): int
    {
        $count = 0;

        $count += DB::table('attendance')->where('institute_id', $this->instituteId)->delete();

        $studentIds = DB::table('students')->where('institute_id', $this->instituteId)->pluck('id');
        if ($studentIds->isNotEmpty()) {
            $count += DB::table('payment_records')->whereIn('fee_payment_id', function ($q) use ($studentIds) {
                $q->select('id')->from('fee_payments')->whereIn('student_id', $studentIds);
            })->delete();
            $count += DB::table('fee_payments')->whereIn('student_id', $studentIds)->delete();
            $count += DB::table('student_discounts')->whereIn('student_id', $studentIds)->delete();
            $count += DB::table('student_fees')->whereIn('student_id', $studentIds)->delete();
        }

        $examIds = DB::table('exams')->where('institute_id', $this->instituteId)->pluck('id');
        if ($examIds->isNotEmpty()) {
            $count += DB::table('exam_results')->whereIn('exam_id', $examIds)->delete();
            $count += DB::table('exam_subjects')->whereIn('exam_id', $examIds)->delete();
            $count += DB::table('report_cards')->whereIn('exam_id', $examIds)->delete();
        }
        $count += DB::table('exams')->where('institute_id', $this->instituteId)->delete();

        $sectionIds = DB::table('sections')->where('institute_id', $this->instituteId)->pluck('id');
        if ($sectionIds->isNotEmpty()) {
            $count += DB::table('teacher_section')->whereIn('section_id', $sectionIds)->delete();
        }

        $count += DB::table('students')->where('institute_id', $this->instituteId)->delete();
        $count += DB::table('sections')->where('institute_id', $this->instituteId)->delete();
        $count += DB::table('teachers')->where('institute_id', $this->instituteId)->delete();

        $gradeIds = DB::table('grades')->where('institute_id', $this->instituteId)->pluck('id');
        if ($gradeIds->isNotEmpty()) {
            $count += DB::table('grade_fees')->whereIn('grade_id', $gradeIds)->delete();
        }
        $count += DB::table('fee_types')->where('institute_id', $this->instituteId)->delete();
        $count += DB::table('discounts')->where('institute_id', $this->instituteId)->delete();
        $count += DB::table('subjects')->where('institute_id', $this->instituteId)->delete();
        $count += DB::table('grades')->where('institute_id', $this->instituteId)->delete();
        $count += DB::table('exam_types')->where('institute_id', $this->instituteId)->delete();

        return $count;
    }
}
