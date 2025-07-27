<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InstallmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'installment_request_id', 'amount', 'amount_paid',
        'payment_status', 'status', 'admin_notes',
        'payment_proof', 'payment_due_date', 'ref',
        'slip_hash', 'slip_qr_text', 'slip_reference', 'slip_ocr_json'
    ];

    protected $casts = [
        'status' => 'string',
        'payment_status' => 'string',
        'amount' => 'float',
        'amount_paid' => 'float',
        'payment_due_date' => 'datetime:Y-m-d H:i:s',
        'slip_reference' => 'string',
    ];

    // Relation: InstallmentRequest (contract)
    public function installmentRequest()
    {
        return $this->belongsTo(InstallmentRequest::class, 'installment_request_id');
    }

    // Relation: User (shortcut)
    public function user()
    {
        return $this->hasOneThrough(
            \App\Models\User::class,
            InstallmentRequest::class,
            'id', // foreign key on InstallmentRequest
            'id', // foreign key on User
            'installment_request_id', // local key on this model
            'user_id' // local key on InstallmentRequest
        );
    }

    public function qrLogs()
    {
        return $this->hasMany(\App\Models\PaymentQrLog::class, 'installment_payment_id');
    }

    public function getIsOverdueAttribute()
    {
        if (empty($this->payment_due_date)) {
            return false;
        }
        try {
            return Carbon::parse($this->payment_due_date)->isBefore(Carbon::today()) && 
                   $this->payment_status === 'pending';
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getPenaltyAttribute()
    {
        if ($this->is_overdue) {
            return floatval($this->installmentRequest->daily_penalty ?? 0);
        }
        return 0;
    }

    public function getStatusAttribute($value)
    {
        return is_string($value) ? $value : '';
    }

    public function getPaymentStatusAttribute($value)
    {
        return is_string($value) ? $value : '';
    }
}
