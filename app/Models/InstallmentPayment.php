<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'installment_request_id',
        'amount',
        'payment_date',
        'payment_method',
        'receipt_image',
    ];

    /**
     * Relation กับ Model InstallmentRequest
     */
    public function installmentRequest()
    {
        return $this->belongsTo(InstallmentRequest::class);
    }
}
