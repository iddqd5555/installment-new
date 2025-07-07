<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InstallmentPayment;
use App\Models\User;
use App\Models\Admin;
use Carbon\Carbon;

class InstallmentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_name', 'gold_amount', 'approved_gold_price',
        'installment_period', 'interest_rate', 'status', 'total_paid',
        'remaining_amount', 'approved_by', 'total_gold_price',
        'total_with_interest', 'daily_payment_amount', 'interest_amount',
        'daily_penalty', 'total_penalty', 'first_approved_date',
        'advance_payment', 'contract_number', 'payment_number', 'responsible_staff'
    ];

    // --- RELATION ---
    public function payments() { return $this->hasMany(InstallmentPayment::class); }
    public function installmentPayments() { return $this->hasMany(InstallmentPayment::class, 'installment_request_id'); }
    public function approvedPayments() { return $this->payments()->where('status', 'approved'); }
    public function user() { return $this->belongsTo(User::class); }
    public function approvedBy() { return $this->belongsTo(Admin::class, 'approved_by'); }

    // --- BUSINESS CALCULATION ---
    public function getTotalGoldPriceAttribute() {
        return round($this->gold_amount * $this->approved_gold_price, 2);
    }

    public function getInterestRateFactorAttribute() {
        $rates = [30 => 1.27, 45 => 1.45, 60 => 1.66];
        return $rates[$this->installment_period] ?? 1;
    }

    public function getTotalWithInterestAttribute() {
        return round($this->total_gold_price * $this->interest_rate_factor, 2);
    }

    public function getDailyPaymentAmountAttribute() {
        return round($this->total_with_interest / max(1, $this->installment_period), 2);
    }

    public function getInterestAmountAttribute() {
        return round($this->total_with_interest - $this->total_gold_price, 2);
    }

    public function getAdvancePaymentAttribute($value) {
        return $value ?: 0;
    }

    // --- DYNAMIC STAT ---
    public function getTotalPaidAttribute() {
        return $this->installmentPayments()->where('status', 'approved')->sum('amount_paid');
    }

    public function getRealRemainingAmountAttribute() {
        return $this->total_with_interest - $this->total_paid;
    }

    // --- PENALTY LOGIC ---
    public function getTotalPenaltyAttribute() {
        // ค่าปรับทุกงวดที่ payment_due_date < today && status pending
        $today = Carbon::today()->format('Y-m-d');
        $pending = $this->installmentPayments()->where('status', 'pending')->where('payment_due_date', '<', $today)->count();
        return $pending * ($this->daily_penalty ?? 0);
    }

    // --- PAYMENT HISTORY DYNAMIC ---
    public function getPaymentHistoryAttribute() {
        return $this->installmentPayments()->orderBy('payment_due_date', 'desc')->take(20)->get();
    }

    // --- NEXT PAYMENT ---
    public function getNextPaymentDateAttribute() {
        $today = Carbon::today()->format('Y-m-d');
        $next = $this->installmentPayments()->where('status', 'pending')->where('payment_due_date', '>=', $today)->orderBy('payment_due_date')->first();
        return $next ? $next->payment_due_date : null;
    }

    // --- CREATE AUTONUMBER ---
    protected static function boot() {
        parent::boot();
        static::creating(function ($request) {
            $lastId = self::max('id') ?? 0;
            $request->contract_number = 'A' . str_pad((68000 + $lastId + 1), 5, '0', STR_PAD_LEFT);
            $request->payment_number = 'INV' . now()->format('ym') . str_pad(($lastId + 1), 4, '0', STR_PAD_LEFT);
        });
    }
}
