<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'subject_id',
        'marks_obtained',
        'max_marks',
        'percentage',
        'grade',
        'remarks',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'max_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public static function calculateGrade(float $percentage, int $passingPercentage = 40): ?string
    {
        if ($percentage < $passingPercentage) {
            return 'F';
        }

        $gradeThresholds = [
            ['grade' => 'A++', 'min' => 96],
            ['grade' => 'A+', 'min' => 91],
            ['grade' => 'A', 'min' => 86],
            ['grade' => 'B++', 'min' => 81],
            ['grade' => 'B+', 'min' => 76],
            ['grade' => 'B', 'min' => 71],
            ['grade' => 'C+', 'min' => 61],
            ['grade' => 'C', 'min' => 51],
            ['grade' => 'D', 'min' => 40],
        ];

        foreach ($gradeThresholds as $threshold) {
            if ($percentage >= $threshold['min']) {
                return $threshold['grade'];
            }
        }

        return 'F';
    }

    public static function calculatePercentage(float $marksObtained, float $maxMarks): float
    {
        if ($maxMarks <= 0) {
            return 0;
        }
        return round(($marksObtained / $maxMarks) * 100, 2);
    }
}
