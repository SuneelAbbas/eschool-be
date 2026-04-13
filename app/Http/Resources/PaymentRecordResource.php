<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fee_payment_id' => $this->fee_payment_id,
            'student_fee_id' => $this->student_fee_id,
            'amount_applied' => $this->amount_applied,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'fee_payment' => new FeePaymentResource($this->whenLoaded('feePayment')),
            'student_fee' => new StudentFeeResource($this->whenLoaded('studentFee')),
        ];
    }
}
