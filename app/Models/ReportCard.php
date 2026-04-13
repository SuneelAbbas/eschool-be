<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'total_marks',
        'marks_obtained',
        'percentage',
        'grade',
        'section_position',
        'grade_position',
        'remarks',
        'subject_results',
        'generated_at',
    ];

    protected $casts = [
        'total_marks' => 'decimal:2',
        'marks_obtained' => 'decimal:2',
        'percentage' => 'decimal:2',
        'section_position' => 'integer',
        'grade_position' => 'integer',
        'subject_results' => 'array',
        'generated_at' => 'datetime',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public static function calculateOverallGrade(float $percentage, int $passingPercentage = 40): ?string
    {
        return ExamResult::calculateGrade($percentage, $passingPercentage);
    }
}
