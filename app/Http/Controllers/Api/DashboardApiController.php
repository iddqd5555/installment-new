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
            ->latest('first_approved_date')
            ->first();

        if (!$installment) {
            return response()->json([]);
        }

        $firstApprovedDate = $installment->first_approved_date ?? $installment->start_date;
        $today = Carbon::today();

        return response()->json([
            'gold_amount' => number_format($installment->gold_amount, 2),
            'total_paid' => number_format($installment->total_paid, 2),
            'total_installment_amount' => number_format($installment->total_with_interest, 2),
            'due_today' => number_format(
                $installment->installmentPayments
                    ->filter(fn($p) => \Carbon\Carbon::parse($p->payment_due_date)->isSameDay($today))
                    ->sum('amount'), 2),
            'advance_payment' => number_format($installment->advance_payment, 2),
            'total_penalty' => number_format($installment->total_penalty, 2),
            'next_payment_date' => $installment->next_payment_date ?: '-',
            'days_passed' => $firstApprovedDate ? Carbon::parse($firstApprovedDate)->diffInDays($today) : 0,
            'installment_period' => $installment->installment_period ?? 0,
            'payment_history' => $installment->payment_history->map(function ($p) {
                return [
                    'amount' => (float) $p->amount,
                    'amount_paid' => (float) $p->amount_paid,
                    'status' => $p->status,
                    'payment_due_date' => $p->payment_due_date,
                ];
            })->values(),
        ]);
    }
}
