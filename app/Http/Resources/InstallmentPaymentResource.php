<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InstallmentPaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount ?? 0,
            'amount_paid' => $this->amount_paid ?? 0,
            'payment_status' => $this->payment_status ?? '',
            'status' => $this->status ?? '',
            'admin_notes' => $this->admin_notes ?? '',
            'payment_proof' => $this->payment_proof ?? '',
            'payment_due_date' => $this->payment_due_date ?? null,
            'ref' => $this->ref ?? '',
            'slip_hash' => $this->slip_hash ?? '',
            'slip_qr_text' => $this->slip_qr_text ?? '',
            'slip_reference' => $this->slip_reference ?? '',
            'slip_ocr_json' => $this->slip_ocr_json ?? '',
        ];
    }
}
