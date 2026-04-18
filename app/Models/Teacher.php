<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'cnic_number',
        'subject',
        'gender',
        'mobile_number',
        'join_date',
        'date_of_birth',
        'blood_group',
        'address',
        'academic_qualification',
        'institute_id',
        'user_id',
    ];

    protected $casts = [
        'join_date' => 'date',
        'date_of_birth' => 'date',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'teacher_section')
            ->withPivot('subject_id', 'is_class_teacher');
    }

    public function teacherSections(): HasMany
    {
        return $this->hasMany(TeacherSection::class);
    }
}
