<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }

    public function feeSchedules(): HasMany
    {
        return $this->hasMany(\App\Models\FeeSchedule::class);
    }
}
