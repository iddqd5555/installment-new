<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\InstallmentRequest;
use Illuminate\Support\Carbon;

class DashboardApiController extends Controller
{
    public function dashboardData(Request $request)
    {
        $user = $request->user();
        $installment = InstallmentRequest::with('installmentPayments')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->orderBy('id', 'desc')
            ->first();

        if (!$installment || (!$installment->first_approved_date && !$installment->start_date)) {
            return response()->json([]);
        }

        $approvedDate = $installment->first_approved_date ?: $installment->start_date;
        $firstApprovedDate = Carbon::parse($approvedDate);
        $daysPassed = $firstApprovedDate->isFuture() ? 0 : Carbon::today()->diffInDays($firstApprovedDate);

        // 1. คำนวณค่าปรับอัตโนมัติ
        $dailyPenalty = $installment->daily_penalty ?? 100; // ใช้ default 100 ถ้าไม่ได้เซ็ต
        $totalPenalty = 0;
        $pendingPayments = $installment->installmentPayments()->where('status', 'pending')->get();
        foreach ($pendingPayments as $payment) {
            $diff = Carbon::parse($payment->payment_due_date)->diffInDays(Carbon::today(), false);
            if ($diff > 0) {
                $totalPenalty += $dailyPenalty * $diff;
            }
        }

        // 2. ยอดอื่นๆ
        $totalPaid = $installment->installmentPayments()->sum('amount_paid');
        $dueToday = $installment->daily_payment_amount;
        $advancePayment = $installment->advance_payment ?? 0;

        $latestPayment = $installment->installmentPayments()
            ->where('status', 'approved')
            ->latest('payment_due_date')
            ->first();

        $nextPayment = $latestPayment
            ? Carbon::parse($latestPayment->payment_due_date)->addDay()->format('Y-m-d H:i:s')
            : Carbon::tomorrow()->setHour(9)->format('Y-m-d H:i:s');

        return response()->json([
            'gold_amount' => number_format($installment->gold_amount, 2),
            'total_paid' => number_format($totalPaid, 2),
            'total_installment_amount' => number_format($installment->total_with_interest, 2),
            'due_today' => number_format($dueToday, 2),
            'advance_payment' => number_format($advancePayment, 2),
            'total_penalty' => number_format($totalPenalty, 2), // <== ส่งค่าปรับที่คำนวณสด
            'next_payment_date' => $nextPayment,
            'days_passed' => $daysPassed,
            'installment_period' => $installment->installment_period,
        ]);
    }

    public function paymentHistory(Request $request)
    {
        $user = $request->user();
        $contract = InstallmentRequest::where('user_id', $user->id)->where('status', 'approved')->latest()->first();
        if (!$contract) return response()->json([]);
        $payments = $contract->installmentPayments()->orderBy('payment_due_date', 'desc')->get();
        return response()->json($payments);
    }

}
