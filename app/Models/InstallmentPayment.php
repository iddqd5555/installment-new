<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'installment_request_id', 
        'amount',           // ✅ เพิ่มฟิลด์นี้ชัดเจนที่สุด
        'amount_paid', 
        'status', 
        'payment_status', 
        'payment_proof', 
        'payment_due_date', 
        'fine'
    ];

    public function installmentRequest()
    {
        return $this->belongsTo(InstallmentRequest::class, 'installment_request_id');
    }
}
