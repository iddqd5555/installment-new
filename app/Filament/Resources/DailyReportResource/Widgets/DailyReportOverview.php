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

        // 🚩 Role-based filter (staff เห็นเฉพาะลูกค้าที่ตัวเองดูแล, OAA/admin เห็นทุกคน)
        if (!in_array($admin->role, ['admin', 'OAA'])) {
            $payments = $payments->whereHas('installmentRequest', function($q) use ($admin) {
                $q->where('responsible_staff', $admin->id);
            });
        }
        $payments = $payments->get();

        $totalDue = $payments->sum('amount');
        $totalPaid = $payments->sum('amount_paid');
        $totalRemaining = $totalDue - $totalPaid;

        return [
            Stat::make('ยอดที่ต้องชำระ', number_format($totalDue, 2).' บาท'),
            Stat::make('ยอดที่ชำระแล้ว', number_format($totalPaid, 2).' บาท'),
            Stat::make('ยอดคงเหลือ', number_format($totalRemaining, 2).' บาท'),
        ];
    }
}
