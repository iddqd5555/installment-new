<?php

namespace App\Filament\Resources\DailyReportResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\InstallmentPayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DailyReportOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $dateFrom = session('daily_reports.date_from', Carbon::today()->toDateString());
        $dateUntil = session('daily_reports.date_until', Carbon::today()->toDateString());

        $dateFrom = Carbon::parse($dateFrom)->startOfDay();
        $dateUntil = Carbon::parse($dateUntil)->endOfDay();
        $admin = Auth::guard('admin')->user();

        $payments = InstallmentPayment::with('installmentRequest')
            ->whereBetween('payment_due_date', [$dateFrom, $dateUntil]);

        if (!in_array($admin->role, ['admin', 'OAA'])) {
            $payments = $payments->whereHas('installmentRequest', function($q) use ($admin) {
                $q->where('responsible_staff', $admin->id);
            });
        }
        $payments = $payments->get();

        $totalDue = $payments->sum('amount');
        $totalPaid = $payments->sum('amount_paid');
        $totalRemaining = $totalDue - $totalPaid;

        // ===== ดอกเบี้ยรวม (ดึงจาก installmentRequest) =====
        $contractIds = $payments->pluck('installmentRequest.id')->unique()->filter();
        $totalInterest = 0;
        if ($contractIds->count()) {
            $contracts = \App\Models\InstallmentRequest::whereIn('id', $contractIds)->get();
            $totalInterest = $contracts->sum(function($c) {
                return ($c->total_with_interest ?? 0) - ($c->total_gold_price ?? 0);
            });
        }

        return [
            Stat::make('ยอดที่ต้องชำระ', number_format($totalDue, 2).' บาท'),
            Stat::make('ยอดที่ชำระแล้ว', number_format($totalPaid, 2).' บาท'),
            Stat::make('ยอดคงเหลือ', number_format($totalRemaining, 2).' บาท'),
            Stat::make('ดอกเบี้ยรวม (ตามสัญญา)', number_format($totalInterest, 2).' บาท'),
        ];
    }
}
