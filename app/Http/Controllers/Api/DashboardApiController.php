<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardApiController extends Controller
{
    public function dashboardData(Request $request)
    {
        $user = Auth::user();

        // ดึง Installment ล่าสุดของ User
        $installment = InstallmentRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        if (!$installment) {
            return response()->json(['error' => 'No active installment found'], 404);
        }

        // ดึง payment ล่าสุด
        $payments = InstallmentPayment::where('installment_request_id', $installment->id)
            ->orderBy('payment_due_date')
            ->get();

        $nextPayment = $payments->firstWhere('status', 'pending');

        return response()->json([
            'gold_amount' => $installment->gold_amount,
            'total_paid' => $payments->sum('amount_paid'),
            'total_installment_amount' => $payments->sum('amount'),
            'due_today' => $nextPayment ? $nextPayment->amount - $nextPayment->amount_paid : 0,
            'advance_payment' => $installment->advance_payment,
            'total_penalty' => $installment->total_penalty,
            'next_payment_date' => $nextPayment ? $nextPayment->payment_due_date->format('Y-m-d') : '-',
            'days_passed' => now()->diffInDays($installment->start_date),
            'installment_period' => $installment->installment_period,
            'payment_history' => $payments,
        ]);
    }

    public function paymentHistory(Request $request)
    {
        $user = Auth::user();
        $installment = InstallmentRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        if (!$installment) {
            return response()->json(['error' => 'No active installment found'], 404);
        }

        $payments = InstallmentPayment::where('installment_request_id', $installment->id)
            ->orderByDesc('payment_due_date')
            ->get();

        return response()->json(['payments' => $payments]);
    }
}
