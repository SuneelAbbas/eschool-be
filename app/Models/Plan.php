<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
    ];

    public function institutes(): HasMany
    {
        return $this->hasMany(Institute::class);
    }
}
