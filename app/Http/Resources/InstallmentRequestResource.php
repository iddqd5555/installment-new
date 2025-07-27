<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InstallmentRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'fullname' => $this->fullname ?? '',
            'phone' => $this->phone ?? '',
            'id_card' => $this->id_card ?? '',
            'product_name' => $this->product_name ?? '',
            'gold_amount' => $this->gold_amount ?? 0,
            'approved_gold_price' => $this->approved_gold_price ?? 0,
            'installment_period' => $this->installment_period ?? 0,
            'interest_rate' => $this->interest_rate ?? 0,
            'status' => $this->status ?? '',
            'total_paid' => $this->total_paid ?? 0,
            'remaining_amount' => $this->remaining_amount ?? 0,
            'approved_by' => $this->approved_by ?? null,
            'total_gold_price' => $this->total_gold_price ?? 0,
            'total_with_interest' => $this->total_with_interest ?? 0,
            'daily_payment_amount' => $this->daily_payment_amount ?? 0,
            'interest_amount' => $this->interest_amount ?? 0,
            'daily_penalty' => $this->daily_penalty ?? 0,
            'total_penalty' => $this->total_penalty ?? 0,
            'first_approved_date' => $this->first_approved_date ?? null,
            'advance_payment' => $this->advance_payment ?? 0,
            'contract_number' => $this->contract_number ?? '',
            'payment_number' => $this->payment_number ?? '',
            'responsible_staff' => $this->responsible_staff ?? '',
            'user' => [
                'id' => $this->user->id ?? null,
                'first_name' => $this->user->first_name ?? '',
                'last_name' => $this->user->last_name ?? '',
                'phone' => $this->user->phone ?? '',
            ],
            'approved_by_admin' => [
                'id' => $this->approvedBy->id ?? null,
                'username' => $this->approvedBy->username ?? '',
                'role' => $this->approvedBy->role ?? '',
            ],
            'payments' => InstallmentPaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
