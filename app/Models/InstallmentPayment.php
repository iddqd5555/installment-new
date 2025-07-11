<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'installment_request_id', 'amount', 'amount_paid', 'status', 'payment_status',
        'payment_proof', 'payment_due_date', 'fine'
    ];

    public function installmentRequest() {
        return $this->belongsTo(InstallmentRequest::class, 'installment_request_id');
    }

    public function qrLogs() {
        return $this->hasMany(\App\Models\PaymentQrLog::class, 'installment_payment_id');
    }

}
