<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_id',
        'name',
        'room_no',
        'capacity',
        'class_teacher',
    ];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function institute(): BelongsTo
    {
        return $this->grade->institute();
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'teacher_section')
            ->withPivot('subject_id', 'is_class_teacher');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
