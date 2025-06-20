<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InstallmentPayment;
use App\Models\User; // ✅ อย่าลืม import User ด้วยนะคะ


class InstallmentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_name', 'gold_amount', 'approved_gold_price',
        'installment_period', 'interest_rate', 'status', 'total_paid',
        'remaining_amount'
    ];

    public function payments()
    {
        return $this->hasMany(InstallmentPayment::class);
    }

    public function approvedPayments()
    {
        return $this->payments()->where('status', 'approved');
    }

    public function calculateMonthlyPayment()
    {
        return ($this->approved_gold_price * $this->gold_amount * (1 + $this->interest_rate / 100)) / $this->installment_period;
    }

    public function installmentPayments()
    {
        return $this->hasMany(InstallmentPayment::class, 'installment_request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calculateInstallment($goldPrice, $goldAmount, $periodDays)
    {
        $totalGoldPrice = $goldPrice * $goldAmount;

        $rates = [
            30 => 1.27,
            45 => 1.45,
            60 => 1.66,
        ];

        if (!isset($rates[$periodDays])) {
            throw new \Exception('Invalid installment period.');
        }

        $totalPrice = $totalGoldPrice * $rates[$periodDays];
        $dailyPayment = round($totalPrice / $periodDays, 2);

        return [
            'total_price' => round($totalPrice, 2),
            'daily_payment' => $dailyPayment,
            'total_gold_price' => $totalGoldPrice,
        ];
    }

}
