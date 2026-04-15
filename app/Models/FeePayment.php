<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'amount',
        'payment_date',
        'payment_method',
        'receipt_number',
        'barcode_value',
        'bank_reference',
        'bank_account_id',
        'transaction_id',
        'received_by',
        'notes',
        'month',
        'academic_year',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function paymentRecords(): HasMany
    {
        return $this->hasMany(PaymentRecord::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public static function generateReceiptNumber(): string
    {
        $yearMonth = now()->format('Ym');
        $prefix = "REC-{$yearMonth}";

        $lastPayment = static::where('receipt_number', 'like', "{$prefix}%")
            ->orderBy('receipt_number', 'desc')
            ->first();

        $sequence = 1;
        if ($lastPayment) {
            $lastSequence = (int) substr($lastPayment->receipt_number, -4);
            $sequence = $lastSequence + 1;
        }

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public static function generateBankReference(): string
    {
        return 'BT-' . now()->format('YmdHis');
    }
}
