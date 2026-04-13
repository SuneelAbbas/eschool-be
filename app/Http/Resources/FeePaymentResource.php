<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeePaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'amount' => $this->amount,
            'payment_date' => $this->payment_date,
            'payment_method' => $this->payment_method,
            'receipt_number' => $this->receipt_number,
            'transaction_id' => $this->transaction_id,
            'received_by' => $this->received_by,
            'notes' => $this->notes,
            'month' => $this->month,
            'academic_year' => $this->academic_year,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'student' => new StudentResource($this->whenLoaded('student')),
            'receiver' => new UserResource($this->whenLoaded('receiver')),
            'payment_records' => PaymentRecordResource::collection($this->whenLoaded('paymentRecords')),
        ];
    }
}
