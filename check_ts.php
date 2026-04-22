<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Teacher 2 assignments ===\n";
$ts = App\Models\TeacherSection::where('teacher_id', 2)->get();
foreach ($ts as $t) {
    echo "ID: {$t->id}, Section: {$t->section_id}, Subject: {$t->subject_id}, IsClass: " . ($t->is_class_teacher ? '1' : '0') . "\n";
}

echo "\n=== Section 32 assignments check ===\n";
$section32 = App\Models\TeacherSection::where('section_id', 32)->get();
foreach ($section32 as $t) {
    echo "Teacher {$t->teacher_id}: Subject {$t->subject_id}, IsClass: " . ($t->is_class_teacher ? '1' : '0') . "\n";
}

echo "\n=== Subject 48 in section 32 ===\n";
$subject48 = App\Models\TeacherSection::where('section_id', 32)->where('subject_id', 48)->first();
if ($subject48) {
    echo "Found - Teacher: {$subject48->teacher_id}\n";
} else {
    echo "No assignment found\n";
}