<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamType extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'name',
        'code',
        'type',
        'max_marks',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_marks' => 'integer',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getTypesForSelect(): array
    {
        return [
            'unit_test' => 'Unit Test',
            'terminal' => 'Terminal',
            'annual' => 'Annual',
            'board_prep' => 'Board Prep',
        ];
    }
}
