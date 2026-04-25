<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'transaction_id',
        'amount',
        'due_date',
        'status',
        'fee_breakdown',
        'paid_at',
        'paid_by',
        'payment_method',
        'bank_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function paidByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public static function generateTransactionId(): string
    {
        $prefix = 'RX';
        $date = now()->format('Ymd');
        $lastReceipt = static::where('transaction_id', 'like', "{$prefix}-{$date}%")
            ->orderBy('transaction_id', 'desc')
            ->first();

        if ($lastReceipt) {
            $sequence = (int) substr($lastReceipt->transaction_id, -4) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    public static function calculateDueDate(): \Carbon\Carbon
    {
        // Default to 7th of next month
        return now()->addMonthNoOverflow(1)->day(7);
    }

    public function markAsPaid(User $user, array $paymentData = []): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now()->toDateString(),
            'paid_by' => $user->id,
            'payment_method' => $paymentData['payment_method'] ?? null,
            'bank_reference' => $paymentData['bank_reference'] ?? null,
        ]);
    }
}