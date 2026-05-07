<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'grade_id',
        'fee_type_id',
        'fee_category_id',
        'amount',
        'frequency',
        'applicable_from',
        'applicable_to',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'applicable_from' => 'date',
        'applicable_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Grade::class);
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }

    /**
     * Check if this schedule is applicable for a given date
     */
    public function isApplicableForDate(string $date): bool
    {
        $checkDate = \Carbon\Carbon::parse($date);
        
        if ($this->applicable_from && $checkDate->lt($this->applicable_from)) {
            return false;
        }
        
        if ($this->applicable_to && $checkDate->gt($this->applicable_to)) {
            return false;
        }
        
        return $this->is_active;
    }

    /**
     * Generate fee instances for a student based on this schedule
     * For the entire academic year with flexibility to override
     */
    public function generateStudentFees(int $studentId, string $enrollmentDate, string $academicYear): array
    {
        $fees = [];
        $startDate = \Carbon\Carbon::parse($enrollmentDate);
        $feeCategoryId = \App\Models\Student::find($studentId)?->fee_category_id;

        // If this schedule is for a specific category, check if student matches
        if ($this->fee_category_id && $this->fee_category_id != $feeCategoryId) {
            return $fees; // Skip - doesn't apply to this student
        }

        switch ($this->frequency) {
            case 'one_time':
                // Create single fee record
                $fees[] = [
                    'student_id' => $studentId,
                    'fee_type_id' => $this->fee_type_id,
                    'fee_schedule_id' => $this->id,
                    'amount' => $this->amount,
                    'month' => '', // Empty for one-time fees
                    'academic_year' => $academicYear,
                    'effective_from' => $enrollmentDate,
                    'status' => 'pending',
                ];
                break;

            case 'monthly':
                // Create fee records for July to June (academic year)
                $yearParts = explode('-', $academicYear);
                $startOfAcademicYear = \Carbon\Carbon::parse($yearParts[0] . '-07-01');  // July start
                $endOfYear = \Carbon\Carbon::parse($yearParts[1] . '-06-30');  // June end
                
                // Always start from academic year start (July), regardless of enrollment
                // This ensures fees start from the beginning of the academic year
                $currentMonth = $startOfAcademicYear->copy()->startOfMonth();
                
                while ($currentMonth->lte($endOfYear)) {
                    $fees[] = [
                        'student_id' => $studentId,
                        'fee_type_id' => $this->fee_type_id,
                        'fee_schedule_id' => $this->id,
                        'amount' => $this->amount,
                        'month' => $currentMonth->format('F'), // "January", "February", etc.
                        'academic_year' => $academicYear,
                        'effective_from' => $enrollmentDate,
                        'status' => 'pending',
                    ];
                    $currentMonth->addMonth();
                }
                break;

            case 'quarterly':
                // Create fee records for each quarter
                $quarters = [
                    ['January', 'February', 'March'],
                    ['April', 'May', 'June'],
                    ['July', 'August', 'September'],
                    ['October', 'November', 'December'],
                ];
                
                $startQuarter = ceil($startDate->month / 3);
                
                for ($q = $startQuarter - 1; $q < 4; $q++) {
                    $fees[] = [
                        'student_id' => $studentId,
                        'fee_type_id' => $this->fee_type_id,
                        'fee_schedule_id' => $this->id,
                        'amount' => $this->amount,
                        'month' => implode(', ', $quarters[$q]), // "January, February, March"
                        'academic_year' => $academicYear,
                        'effective_from' => $enrollmentDate,
                        'status' => 'pending',
                    ];
                }
                break;

            case 'annual':
                // Create single fee record for the academic year
                $fees[] = [
                    'student_id' => $studentId,
                    'fee_type_id' => $this->fee_type_id,
                    'fee_schedule_id' => $this->id,
                    'amount' => $this->amount,
                    'month' => 'Annual', // Special marker
                    'academic_year' => $academicYear,
                    'effective_from' => $enrollmentDate,
                    'status' => 'pending',
                ];
                break;
        }

        return $fees;
    }
}
