<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentRequest extends Model
{
    use HasFactory;

        protected $fillable = [
        'fullname',
        'phone',
        'gold_type',
        'gold_amount',
        'installment_period',
        'status',
        'user_id',
        'product_name',
        'price',
        'installment_months',
        'product_image',
        'interest_rate', // เพิ่มฟิลด์นี้
        'approved_gold_price',
    ];

    /**
     * Relation กับ Model InstallmentPayment (ประวัติการชำระเงิน)
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    /**
     * Relation กับ Model User (เจ้าของคำขอ)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
