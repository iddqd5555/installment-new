<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InstallmentRequest;
use App\Models\AdvancePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    public function dashboardData(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Token ผิดหรือหมดอายุ', 'contracts' => []], 401);
            }

            $today = Carbon::today()->setTime(9, 0, 0);

            $installments = InstallmentRequest::with(['installmentPayments' => function ($q) {
                $q->orderBy('payment_due_date', 'asc');
            }])
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->orderByDesc('id')
                ->get();

            $result = $installments->map(function ($item) use ($today) {
                $payments = collect($item->installmentPayments);

                $currentDuePayment = $payments->first(function ($p) use ($today) {
                    return strtolower((string)($p->status ?? '')) === 'pending'
                        && $p->payment_due_date
                        && Carbon::parse($p->payment_due_date)->equalTo($today);
                });

                $dueThisPeriod = $currentDuePayment
                    ? max(floatval($currentDuePayment->amount) - floatval($currentDuePayment->amount_paid), 0)
                    : 0;

                $overdueAmount = $payments->where('status', 'pending')
                    ->where('payment_due_date', '<', $today)
                    ->sum(function ($p) {
                        return floatval($p->amount) - floatval($p->amount_paid);
                    });

                $totalDueAmount = $dueThisPeriod + $overdueAmount;

                $paid_payments = $payments->where('status', 'paid')->values();

                $totalPaid = $paid_payments->sum(function ($p) {
                    return floatval($p->amount_paid ?? 0);
                });
                $totalInstallmentAmount = $payments->sum(function ($p) {
                    return floatval($p->amount ?? 0);
                });

                $uploadedSlips = $payments
                    ->filter(function($p) {
                        return !empty($p->slip_hash) && !empty($p->payment_proof);
                    })
                    ->map(function($p) {
                        return [
                            'id' => $p->id,
                            'amount_paid' => floatval($p->amount_paid ?? 0),
                            'created_at' => $p->created_at ? $p->created_at->toDateTimeString() : null,
                            'slip_hash' => $p->slip_hash,
                            'payment_proof' => $p->payment_proof,
                            'payment_due_date' => $p->payment_due_date,
                        ];
                    })->values();

                $totalUploadedAmount = $uploadedSlips->sum('amount_paid');

                $daysPassed = 0;
                $startDate = $item->start_date ? Carbon::parse($item->start_date) : null;
                if ($startDate && $today->greaterThanOrEqualTo($startDate)) {
                    $daysPassed = $startDate->diffInDays($today) + 1;
                }

                $lastPayment = $payments->last();
                $endDate = $lastPayment ? $lastPayment->payment_due_date : ($item->start_date ?? '-');

                // งวดที่เป็นเงินมัดจำ+ชำระล่วงหน้า
                $initial_paid_payments = $payments->filter(function($p){
                    return $p->admin_notes === 'เงินมัดจำ' || $p->admin_notes === 'ชำระล่วงหน้า';
                })->map(function($p){
                    return [
                        'id' => $p->id,
                        'amount' => floatval($p->amount ?? 0),
                        'payment_due_date' => $p->payment_due_date,
                        'admin_notes' => $p->admin_notes,
                        'status' => $p->status,
                        'amount_paid' => floatval($p->amount_paid),
                    ];
                })->values();

                return [
                    'id' => $item->id,
                    'contract_number' => $item->contract_number,
                    'gold_amount' => floatval($item->gold_amount),
                    'installment_period' => intval($item->installment_period ?? 0),
                    'daily_payment_amount' => floatval($item->daily_payment_amount),
                    'total_installment_amount' => $totalInstallmentAmount,
                    'total_paid' => floatval($totalPaid),
                    'total_uploaded_amount' => floatval($totalUploadedAmount),
                    'advance_payment' => floatval($item->advance_payment ?? 0),
                    'due_this_period' => round($dueThisPeriod, 2),
                    'overdue_amount' => round($overdueAmount, 2),
                    'total_due_amount' => round($totalDueAmount, 2),
                    'next_due_date_custom' => $item->next_due_date_custom ?? null,
                    'down_payment' => floatval($item->down_payment ?? 0),
                    'initial_payment' => floatval($item->initial_payment ?? 0),
                    'payment_per_period' => floatval($item->payment_per_period ?? 0),
                    'start_date' => $item->start_date,
                    'end_date' => $endDate,
                    'status' => $item->status,
                    'days_passed' => $daysPassed,
                    'uploaded_slips' => $uploadedSlips,
                    'initial_paid_payments' => $initial_paid_payments,
                    'installment_payments' => $payments->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'amount' => floatval($p->amount ?? 0),
                            'amount_paid' => floatval($p->amount_paid ?? 0),
                            'status' => $p->status,
                            'payment_due_date' => $p->payment_due_date,
                            'payment_status' => $p->payment_status,
                            'ref' => $p->ref ?? null,
                            'created_at' => $p->created_at,
                            'slip_hash' => $p->slip_hash ?? null,
                            'payment_proof' => $p->payment_proof ?? null,
                        ];
                    })->values(),
                ];
            });

            // รายการเติมเงินล่วงหน้า
            $advances = AdvancePayment::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->get(['amount', 'created_at', 'slip_image', 'installment_request_id']);

            return response()->json([
                'contracts' => $result,
                'advance_payments' => $advances,
            ]);
        } catch (\Throwable $e) {
            \Log::error('[API] DASHBOARD ERROR: ' . $e->getMessage() . ' LINE ' . $e->getLine(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดที่ server',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'contracts' => [],
            ], 500);
        }
    }

    public function notifications(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Token ผิดหรือหมดอายุ', 'notifications' => []], 401);
        }

        $today = Carbon::today();
        $notifications = [];

        $contracts = InstallmentRequest::with(['installmentPayments' => function ($q) {
                $q->orderBy('payment_due_date', 'asc');
            }])
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->get();

        foreach ($contracts as $contract) {
            foreach ($contract->installmentPayments as $payment) {
                if (
                    strtolower((string)($payment->status ?? '')) === 'pending'
                    && $payment->payment_due_date
                    && Carbon::parse($payment->payment_due_date)->lte($today)
                ) {
                    $notifications[] = [
                        'contract_number' => $contract->contract_number,
                        'amount' => (float) $payment->amount,
                        'due_date' => $payment->payment_due_date,
                        'status' => $payment->status,
                    ];
                }
            }
        }

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    public function payFromAdvance(Request $request, $contractId)
    {
        $user = Auth::user();
        $contract = \App\Models\InstallmentRequest::where('id', $contractId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $advance = $contract->advance_payment;
        if ($advance <= 0) {
            return response()->json(['error' => 'ยอดเงิน Advance ไม่พอ'], 400);
        }

        $beforeAdvance = $advance;
        $payments = $contract->installmentPayments()
            ->where('status', 'pending')
            ->where('payment_due_date', '<=', Carbon::today())
            ->orderBy('payment_due_date', 'asc')
            ->get();

        foreach ($payments as $payment) {
            $remain = $payment->amount - ($payment->amount_paid ?? 0);
            if ($remain <= 0) continue;

            $pay = min($remain, $advance);

            $payment->amount_paid = ($payment->amount_paid ?? 0) + $pay;
            if ($payment->amount_paid >= $payment->amount) {
                $payment->status = 'paid';
            }
            $payment->save();

            $advance -= $pay;

            if ($advance <= 0) break;
        }

        $contract->advance_payment = $advance;
        $contract->save();

        // Log หัก advance (optional)
        AdvancePayment::create([
            'installment_request_id' => $contract->id,
            'user_id' => $user->id,
            'amount' => -($beforeAdvance - $advance),
            'slip_image' => null,
            'slip_hash' => null,
            'slip_reference' => 'advance-deduct',
            'slip_ocr_json' => null,
        ]);

        return response()->json(['success' => true, 'remaining_advance' => $advance]);
    }
}
