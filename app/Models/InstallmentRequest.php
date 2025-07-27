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
        'fullname', 'user_id', 'product_name', 'gold_amount', 'approved_gold_price',
        'installment_period', 'interest_rate', 'status', 'total_paid', 'remaining_amount',
        'approved_by', 'total_gold_price', 'total_with_interest', 'daily_payment_amount',
        'interest_amount', 'daily_penalty', 'total_penalty', 'first_approved_date',
        'contract_number', 'payment_number', 'responsible_staff', 'phone', 'id_card',
        'referrer_code', 'gold_price', 'start_date', 'down_payment', 'initial_payment',
        'payment_per_period', 'advance_payment'
    ];

    protected static function booted()
    {
        static::creating(function ($request) {
            $lastId = self::max('id') ?? 0;
            $prefix = ($request->gold_amount < 1) ? 'A' : 'B';
            $contractNum = $prefix . str_pad((6800000 + $lastId + 1), 7, '0', STR_PAD_LEFT);
            $request->contract_number = $contractNum;
            $request->payment_number = 'INV' . now()->format('ym') . str_pad(($lastId + 1), 4, '0', STR_PAD_LEFT);
        });
    }

    // ความสัมพันธ์ InstallmentPayment
    public function payments()
    {
        return $this->hasMany(InstallmentPayment::class);
    }

    public function installmentPayments()
    {
        return $this->hasMany(InstallmentPayment::class, 'installment_request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    // --- Getter Attributes / Logic ---
    public function getDueAmountThisPeriodAttribute()
    {
        $today = Carbon::today();
        $due = $this->installmentPayments()
            ->where('status', 'pending')
            ->where('payment_due_date', '>=', $today)
            ->orderBy('payment_due_date', 'asc')
            ->first();

        if ($due) {
            $remain = floatval($due->amount) - floatval($due->amount_paid);
            return $remain > 0 ? round($remain, 2) : 0;
        }
        return 0;
    }

    public function getOverdueAmountAttribute()
    {
        $today = Carbon::today();
        return $this->installmentPayments
            ->where('status', 'pending')
            ->where('payment_due_date', '<', $today)
            ->sum(function($p) {
                return floatval($p->amount) - floatval($p->amount_paid);
            });
    }

    public function getTotalDueAmountAttribute()
    {
        return round($this->due_amount_this_period + $this->overdue_amount, 2);
    }

    public function getNextDueDateCustomAttribute()
    {
        if (!$this->start_date) return null;
        $start = Carbon::parse($this->start_date);
        $added = 0;
        $date = $start;
        while ($added < 30) {
            $date->addDay();
            if ($date->dayOfWeek !== Carbon::SUNDAY) {
                $added++;
            }
        }
        $date->subDay();
        return $date->toDateString();
    }

    public function getDownPaymentAttribute($v)    { return floatval($v ?? 0); }

    public function getInitialPaymentAttribute($v)
    {
        if ($v !== null) return floatval($v);
        $n = 2;
        if ($this->installment_period == 45) $n = 3;
        if ($this->installment_period == 60) $n = 4;
        return round($this->daily_payment_amount * $n, 2);
    }

    public function getPaymentPerPeriodAttribute($v) { return floatval($v ?? 0); }

    public function getTotalGoldPriceAttribute()
    {
        return round($this->gold_amount * $this->approved_gold_price, 2);
    }

    public function getInterestRateFactorAttribute()
    {
        $rates = [30 => 1.27, 45 => 1.45, 60 => 1.66];
        return $rates[$this->installment_period] ?? 1;
    }

    public function getTotalWithInterestAttribute()
    {
        return round($this->total_gold_price * $this->interest_rate_factor, 2);
    }

    public function getInterestAmountAttribute()
    {
        return round($this->total_with_interest - $this->total_gold_price, 2);
    }

    public function getTotalPaidAttribute()
    {
        return $this->installmentPayments()->where('status', 'paid')->sum('amount_paid');
    }

    public function getRealRemainingAmountAttribute()
    {
        return $this->total_with_interest - $this->total_paid;
    }

    public function getTotalPenaltyAttribute()
    {
        $today = Carbon::today()->format('Y-m-d');
        $pending = $this->installmentPayments()->where('status', 'pending')->where('payment_due_date', '<', $today)->count();
        return $pending * floatval($this->daily_penalty ?? 0);
    }

    public function getNextPaymentDateAttribute()
    {
        $today = Carbon::today()->format('Y-m-d');
        $next = $this->installmentPayments()->where('status', 'pending')->where('payment_due_date', '>=', $today)->orderBy('payment_due_date')->first();
        return $next ? $next->payment_due_date : null;
    }

    public function getAdvancePaymentAttribute($v)
    {
        return floatval($v ?? 0);
    }

    // คำนวณค่างวดและบันทึก
    public function calculateInstallmentAmounts()
    {
        $interestRates = [30 => 1.27, 45 => 1.45, 60 => 1.66];
        $firstPayPeriods = [30 => 2, 45 => 3, 60 => 4];

        $goldPrice = $this->gold_amount * $this->approved_gold_price;
        $rate = $interestRates[$this->installment_period] ?? 1;

        $total_with_interest = round($goldPrice * $rate, 2);
        $daily_payment = round($total_with_interest / $this->installment_period, 2);

        $initial_payment = round($daily_payment * ($firstPayPeriods[$this->installment_period] ?? 2), 2);

        $this->total_gold_price = round($goldPrice, 2);
        $this->total_with_interest = $total_with_interest;
        $this->daily_payment_amount = $daily_payment;
        $this->initial_payment = $initial_payment;
        $this->interest_rate = $rate;
        $this->save();
    }

    // สร้างงวดผ่อนจ่าย
    public function generatePayments()
    {
        $startDate = Carbon::parse($this->start_date);
        $period = intval($this->installment_period);
        $amount = round($this->daily_payment_amount, 2);

        InstallmentPayment::where('installment_request_id', $this->id)->delete();

        for ($i = 0; $i < $period; $i++) {
            $dueDate = $startDate->copy()->addDays($i)->setTime(9, 0, 0);
            InstallmentPayment::create([
                'installment_request_id' => $this->id,
                'amount' => $amount,
                'amount_paid' => 0,
                'payment_status' => 'pending',
                'status' => 'pending',
                'payment_due_date' => $dueDate,
                'ref' => 'INV' . $this->id . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
            ]);
        }
    }
}
