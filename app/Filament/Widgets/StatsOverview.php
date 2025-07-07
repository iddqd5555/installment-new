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
        $today = Carbon::today()->format('Y-m-d');

        $installmentsQuery = InstallmentRequest::query()->where('status', 'approved');
        $paymentsQuery = InstallmentPayment::query();

        if (!in_array($admin->role, ['admin', 'OAA'])) {
            $installmentsQuery = $installmentsQuery->where('responsible_staff', $admin->id);
            $paymentsQuery = $paymentsQuery->whereHas('installmentRequest', function ($q) use ($admin) {
                $q->where('responsible_staff', $admin->id);
            });
        }

        $userCount = User::count();
        $activeInstallment = $installmentsQuery->count();

        $pendingToday = $paymentsQuery->whereDate('payment_due_date', $today)->where('status', 'pending')->count();
        $totalIncomeToday = $paymentsQuery->whereDate('payment_due_date', $today)->where('status', 'approved')->sum('amount_paid');
        $dueToday = InstallmentPayment::where('payment_due_date', $today)
            ->when(!in_array($admin->role, ['admin', 'OAA']), function($q) use ($admin) {
                $q->whereHas('installmentRequest', function($q2) use ($admin) {
                    $q2->where('responsible_staff', $admin->id);
                });
            })
            ->sum('amount');
        $overdueCount = InstallmentPayment::where('status', 'pending')
            ->whereDate('payment_due_date', '<', $today)
            ->when(!in_array($admin->role, ['admin', 'OAA']), function($q) use ($admin) {
                $q->whereHas('installmentRequest', function($q2) use ($admin) {
                    $q2->where('responsible_staff', $admin->id);
                });
            })
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
