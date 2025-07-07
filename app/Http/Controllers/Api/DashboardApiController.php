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

        $installment = InstallmentRequest::with(['installmentPayments'])
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest('first_approved_date')
            ->first();

        if (!$installment) {
            return response()->json([
                'gold_amount' => '0.00',
                'total_paid' => '0.00',
                'total_installment_amount' => '0.00',
                'due_today' => '0.00',
                'advance_payment' => '0.00',
                'total_penalty' => '0.00',
                'next_payment_date' => '-',
                'days_passed' => 0,
                'installment_period' => 0,
                'payment_history' => [],
            ]);
        }

        $today = Carbon::today()->format('Y-m-d');
        $firstApprovedDate = $installment->first_approved_date ?? $installment->start_date;

        // 1. ยอดที่ต้องชำระ "วันนี้" (payment_due_date = วันนี้, เอาทั้งหมด)
        $dueToday = $installment->installmentPayments
            ->where('payment_due_date', $today)
            ->sum('amount');

        // ถ้าไม่มีงวดวันนี้เลย ให้ return 0.00
        $dueToday = $dueToday ?: 0.00;

        // 2. ยอดที่ชำระไปแล้ว
        $totalPaid = $installment->installmentPayments
            ->where('status', 'approved')
            ->sum('amount_paid');

        // 3. ค่าปรับ
        $penaltyPerDay = $installment->daily_penalty ?? 0;
        $overdue = $installment->installmentPayments
            ->where('status', 'pending')
            ->where('payment_due_date', '<', $today)
            ->count();
        $totalPenalty = $overdue * $penaltyPerDay;

        // 4. advance_payment
        $advancePayment = $installment->advance_payment ?? 0;

        // 5. ประวัติการชำระเงิน (เอาทุก status เรียงล่าสุด 20 งวด)
        $paymentHistory = $installment->installmentPayments
            ->sortByDesc('payment_due_date')
            ->take(20)
            ->map(function ($p) {
                return [
                    'amount' => (float) $p->amount,
                    'amount_paid' => (float) $p->amount_paid,
                    'status' => $p->status,
                    'payment_due_date' => $p->payment_due_date,
                ];
            })->values();

        // 6. ระยะเวลาการผ่อน
        $daysPassed = $firstApprovedDate ? Carbon::parse($firstApprovedDate)->diffInDays(Carbon::today()) : 0;
        $installmentPeriod = $installment->installment_period ?? 0;

        // 7. วันชำระครั้งถัดไป (pending ที่ date >= today)
        $nextPayment = $installment->installmentPayments
            ->where('status', 'pending')
            ->where('payment_due_date', '>=', $today)
            ->sortBy('payment_due_date')
            ->first();
        $nextPaymentDate = $nextPayment ? $nextPayment->payment_due_date : '-';

        return response()->json([
            'gold_amount' => number_format($installment->gold_amount, 2),
            'total_paid' => number_format($totalPaid, 2),
            'total_installment_amount' => number_format($installment->total_with_interest, 2),
            'due_today' => number_format($dueToday, 2),
            'advance_payment' => number_format($advancePayment, 2),
            'total_penalty' => number_format($totalPenalty, 2),
            'next_payment_date' => $nextPaymentDate,
            'days_passed' => $daysPassed,
            'installment_period' => $installmentPeriod,
            'payment_history' => $paymentHistory,
        ]);
    }
}
