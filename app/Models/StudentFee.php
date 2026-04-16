<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fee_type_id',
        'academic_year',
        'amount',
        'is_custom',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_custom' => 'boolean',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function paymentRecords(): HasMany
    {
        return $this->hasMany(PaymentRecord::class);
    }
}
