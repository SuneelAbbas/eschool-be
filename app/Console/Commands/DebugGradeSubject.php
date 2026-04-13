<?php

namespace App\Console\Commands;

use App\Models\Grade;
use App\Models\GradeSubject;
use App\Models\Subject;
use Illuminate\Console\Command;

class DebugGradeSubject extends Command
{
    protected $signature = 'debug:grade-subject {grade_name?} {subject_code?}';
    protected $description = 'Debug grade subject link';

    public function handle(): int
    {
        $gradeName = $this->argument('grade_name') ?? 'Grade 7';
        $subjectCode = $this->argument('subject_code') ?? 'ENG';

        $grade = Grade::where('name', $gradeName)->first();
        
        if (!$grade) {
            $this->error("Grade '{$gradeName}' not found");
            return 1;
        }

        $subject = Subject::where('code', $subjectCode)->first();
        
        if (!$subject) {
            $this->error("Subject '{$subjectCode}' not found");
            return 1;
        }

        $this->info("Grade: {$grade->name} (ID: {$grade->id}, Institute ID: {$grade->institute_id})");
        $this->info("Subject: {$subject->name} (ID: {$subject->id}, Code: {$subject->code}, Institute ID: {$subject->institute_id})");

        $existing = GradeSubject::where('grade_id', $grade->id)
            ->where('subject_id', $subject->id)
            ->with('grade')
            ->first();

        if ($existing) {
            $this->warn("EXISTING LINK FOUND!");
            $this->line("Grade: " . $existing->grade->name . " (Institute ID: " . $existing->grade->institute_id . ")");
        } else {
            $this->info("No existing link found");
        }

        $allLinks = GradeSubject::where('grade_id', $grade->id)
            ->with('grade', 'subject')
            ->get();

        $this->info("\nAll links for {$gradeName}:");
        foreach ($allLinks as $link) {
            $this->line("- " . ($link->subject->name ?? 'N/A') . " (Institute: " . $link->grade->institute_id . ")");
        }

        return 0;
    }
}
