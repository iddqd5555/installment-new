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
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Token ผิดหรือหมดอายุ'], 401);
            }

            $installment = InstallmentRequest::where('user_id', $user->id)
                ->where('status', 'approved')
                ->latest()
                ->first();

            if (!$installment) {
                return response()->json(['error' => 'ไม่พบสัญญาผ่อนที่อนุมัติล่าสุด'], 404);
            }

            $payments = InstallmentPayment::where('installment_request_id', $installment->id)->get();

            $totalPaid = $payments->sum('amount_paid');
            $totalInstallmentAmount = $payments->sum('amount');

            $nextPayment = $payments->where('status', '!=', 'approved')->sortBy('payment_due_date')->first();

            return response()->json([
                'gold_amount' => $installment->gold_amount,
                'total_paid' => $totalPaid,
                'total_installment_amount' => $totalInstallmentAmount,
                'due_today' => $nextPayment ? ($nextPayment->amount - $nextPayment->amount_paid) : 0,
                'advance_payment' => $installment->advance_payment,
                'total_penalty' => $installment->total_penalty,
                'next_payment_date' => ($nextPayment && $nextPayment->payment_due_date)
                    ? \Carbon\Carbon::parse($nextPayment->payment_due_date)->format('Y-m-d H:i:s')
                    : '-',
                'days_passed' => $installment->start_date
                    ? now()->diffInDays(\Carbon\Carbon::parse($installment->start_date))
                    : 0,
                'installment_period' => $installment->installment_period,
                'payment_history' => $payments->map(function ($p) {
                    return [
                        'amount' => $p->amount,
                        'amount_paid' => $p->amount_paid,
                        'status' => $p->status,
                        'payment_status' => $p->payment_status,
                        'payment_due_date' => $p->payment_due_date
                            ? \Carbon\Carbon::parse($p->payment_due_date)->format('Y-m-d H:i:s')
                            : null,
                    ];
                }),
            ]);
        } catch (\Throwable $e) {
            \Log::error('[API] DASHBOARD ERROR: '.$e->getMessage().' LINE '.$e->getLine());
            return response()->json(['error' => 'เกิดข้อผิดพลาดที่ server: '.$e->getMessage()], 500);
        }
    }
}
