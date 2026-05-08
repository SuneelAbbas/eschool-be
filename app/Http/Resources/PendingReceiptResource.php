<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendingReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->status;
        
        // If pending and past due date, show as overdue
        if ($this->status === 'pending' && $this->due_date && strtotime($this->due_date) < strtotime(now()->toDateString())) {
            $status = 'overdue';
        }

        return [
            'id' => $this->id,
            'transaction_id' => $this->transaction_id,
            'amount' => (string) $this->amount,
            'status' => $status,
            'due_date' => $this->due_date,
            'month' => $this->month,
            'academic_year' => $this->academic_year,
            'paid_at' => $this->paid_at,
            'payment_method' => $this->payment_method,
            'bank_reference' => $this->bank_reference,
            'fee_breakdown' => is_string($this->fee_breakdown) 
                ? json_decode($this->fee_breakdown, true) 
                : $this->fee_breakdown,
            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->first_name . ' ' . $this->student->last_name,
                'registration_number' => $this->student->registration_number,
                'grade' => $this->student->section?->grade?->name,
                'section' => $this->student->section?->name,
            ],
        ];
    }
}