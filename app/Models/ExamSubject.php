<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSubject extends Model
{
    use HasFactory;

    protected $table = 'exam_subjects';

    protected $fillable = [
        'exam_id',
        'subject_id',
        'max_marks',
        'passing_marks',
        'weightage',
    ];

    protected $casts = [
        'max_marks' => 'integer',
        'passing_marks' => 'integer',
        'weightage' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
