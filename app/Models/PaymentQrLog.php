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

    public function customer()
    {
        // แก้ไขชื่อ model User ตามโปรเจกต์คุณ
        return $this->belongsTo(\App\Models\User::class, 'customer_id');
    }
}
