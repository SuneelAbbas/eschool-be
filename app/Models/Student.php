<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'registration_date',
        'registration_number',
        'roll_no',
        'gender',
        'mobile_number',
        'parents_name',
        'parents_mobile_number',
        'date_of_birth',
        'blood_group',
        'address',
        'upload',
        'institute_id',
        'user_id',
        'section_id',
        'admission_date',
        'fee_category_id',
    ];

    protected $casts = [
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

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }

    public function studentDiscounts(): HasMany
    {
        return $this->hasMany(StudentDiscount::class);
    }

    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function pendingReceipts(): HasMany
    {
        return $this->hasMany(PendingReceipt::class);
    }

    public static function generateRegistrationNumber(int $instituteId, ?int $gradeId = null): string
    {
        $year = date('Y');
        
        $gradePrefix = '';
        if ($gradeId) {
            $grade = Grade::find($gradeId);
            if ($grade) {
                preg_match('/\d+/', $grade->name, $matches);
                $gradePrefix = $matches[0] ?? substr($grade->name, 0, 2);
            }
        }

        $lastStudent = static::where('institute_id', $instituteId)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastStudent && preg_match('/\d{4}-(\d+)/', $lastStudent->registration_number, $matches)) {
            $sequence = ((int)$matches[1]) + 1;
        }

        $regNumber = sprintf('%d-%s%03d', $year, $gradePrefix, $sequence);
        
        $attempts = 0;
        while (static::where('registration_number', $regNumber)->exists() && $attempts < 100) {
            $sequence++;
            $regNumber = sprintf('%d-%s%03d', $year, $gradePrefix, $sequence);
            $attempts++;
        }

        return $regNumber;
    }
}
