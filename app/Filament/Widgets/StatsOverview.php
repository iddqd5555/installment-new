<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\User;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getCards(): array
    {
        $admin = Auth::guard('admin')->user();
        $today = Carbon::today();

        // 1. สมาชิกทั้งหมด
        $userCount = User::count();

        // 2. ลูกค้าที่กำลังผ่อน (active installment)
        $activeInstallment = InstallmentRequest::where('status', 'approved')->count();

        // 3. ยอดชำระที่รออนุมัติ (pending) วันนี้
        $pendingToday = InstallmentPayment::whereDate('payment_due_date', $today)
            ->where('status', 'pending')
            ->count();

        // 4. รายได้รวมวันนี้ (ชำระแล้ววันนี้)
        $totalIncomeToday = InstallmentPayment::whereDate('payment_due_date', $today)
            ->where('status', 'approved')
            ->sum('amount_paid');

        // 5. ยอดที่ต้องชำระวันนี้ (ทุกคน, เฉพาะงวดวันนี้)
        $dueToday = InstallmentPayment::whereDate('payment_due_date', $today)
            ->sum('amount');

        // 6. ยอดที่ค้างชำระ (overdue ทุกงวดก่อนวันนี้)
        $overdueCount = InstallmentPayment::where('status', 'pending')
            ->whereDate('payment_due_date', '<', $today)
            ->count();

        return [
            Card::make('สมาชิกทั้งหมด', $userCount)
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->description('ลูกค้าทั้งหมด'),

            Card::make('คำขอผ่อนที่อนุมัติ', $activeInstallment)
                ->icon('heroicon-o-hand-thumb-up')
                ->color('info')
                ->description('สัญญาอนุมัติ'),

            Card::make('ยอดที่ต้องชำระวันนี้', number_format($dueToday, 2) . ' บาท')
                ->icon('heroicon-o-calendar-days')
                ->color('warning')
                ->description('รวมทุกลูกค้างวดวันนี้'),

            Card::make('รออนุมัติวันนี้', $pendingToday)
                ->icon('heroicon-o-clock')
                ->color('danger')
                ->description('งวดวันนี้ที่ยังรออนุมัติ'),

            Card::make('ค้างชำระ (สะสม)', $overdueCount)
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->description('งวดที่เลยวันครบกำหนด'),

            Card::make('รายได้รวมวันนี้', number_format($totalIncomeToday, 2) . ' บาท')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary')
                ->description('ลูกค้าชำระแล้ว (วันนี้)'),
        ];
    }
}
