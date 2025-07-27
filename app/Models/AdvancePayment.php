<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvancePayment extends Model
{
    protected $fillable = [
        'installment_request_id',
        'user_id',
        'amount',
        'slip_image',
        'slip_hash',
        'slip_reference',
        'slip_ocr_json',
    ];

    public function installmentRequest()
    {
        return $this->belongsTo(InstallmentRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
