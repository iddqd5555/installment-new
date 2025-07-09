<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentQrLog extends Model
{
    protected $fillable = [
        'qr_ref',
        'amount',
        'currency',
        'status',
        'qr_image',
        'transaction_id',
        'customer_id',
    ];
}
