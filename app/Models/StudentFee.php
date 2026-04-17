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
        'is_inherited',
        'inherited_from_grade_fee_id',
        'prorate_percentage',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_custom' => 'boolean',
        'is_active' => 'boolean',
        'is_inherited' => 'boolean',
        'inherited_from_grade_fee_id' => 'integer',
        'prorate_percentage' => 'decimal:2',
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

    public function gradeFee(): BelongsTo
    {
        return $this->belongsTo(GradeFee::class, 'inherited_from_grade_fee_id');
    }

    public function paymentRecords(): HasMany
    {
        return $this->hasMany(PaymentRecord::class);
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->paymentRecords()->sum('amount_applied');
    }

    public function getBalanceAttribute(): float
    {
        return (float) $this->amount - $this->total_paid;
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }
}
