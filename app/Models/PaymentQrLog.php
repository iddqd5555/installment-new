<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentQrLog extends Model
{
    protected $fillable = [
        'qr_ref', 'amount', 'currency', 'status', 'qr_image', 'transaction_id', 'installment_payment_id', 'customer_id'
    ];

    public function installmentPayment()
    {
        return $this->belongsTo(InstallmentPayment::class);
    }
}
