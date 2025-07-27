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

        $installmentsQuery = InstallmentRequest::query()->where('status', 'approved');
        if (!in_array($admin->role, ['admin', 'OAA'])) {
            $installmentsQuery = $installmentsQuery->where('responsible_staff', $admin->id);
        }

        $userCount = User::count();
        $activeInstallment = $installmentsQuery->count();

        // ========== ยอดดอกเบี้ยรวมตามสัญญา ==========
        $contracts = $installmentsQuery->get();
        $totalInterest = $contracts->sum(function($row) {
            // สมมติฟิลด์คือ total_with_interest กับ total_gold_price
            return $row->total_with_interest - $row->total_gold_price;
        });

        // ------------------------ ยอดเดิม ------------------------
        $pendingToday = InstallmentPayment::query()
            ->whereDate('payment_due_date', $today)
            ->where(function($q) {
                $q->where('status', 'pending')->orWhere('payment_status', 'pending');
            })
            ->count();

        $totalIncomeToday = InstallmentPayment::query()
            ->whereDate('payment_due_date', $today)
            ->where('status', 'approved')
            ->sum('amount_paid');

        $dueToday = InstallmentPayment::query()
            ->whereDate('payment_due_date', $today)
            ->where(function($q) {
                $q->where('status', 'pending')->orWhere('payment_status', 'pending');
            })
            ->sum('amount');

        $totalDueAccumulated = InstallmentPayment::query()
            ->whereDate('payment_due_date', '<=', $today)
            ->where(function($q) {
                $q->where('status', 'pending')->orWhere('payment_status', 'pending');
            })
            ->sum('amount');

        // ------------------------ การ์ดที่โชว์ ------------------------
        return [
            Card::make('สมาชิกทั้งหมด', $userCount)
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->description('ลูกค้าทั้งหมด'),

            Card::make('คำขอผ่อนที่อนุมัติ', $activeInstallment)
                ->icon('heroicon-o-hand-thumb-up')
                ->color('info')
                ->description('สัญญาอนุมัติ'),

            Card::make('ยอดดอกเบี้ยรวม (ตามสัญญา)', number_format($totalInterest, 2) . ' บาท')
                ->icon('heroicon-o-banknotes')
                ->color('rose')
                ->description('ดอกเบี้ยจากสัญญาทั้งหมด'),

            Card::make('ยอดที่ต้องชำระวันนี้', number_format($dueToday, 2) . ' บาท')
                ->icon('heroicon-o-calendar-days')
                ->color('warning')
                ->description('รวมทุกลูกค้างวดที่ครบกำหนดวันนี้'),

            Card::make('รออนุมัติวันนี้', $pendingToday)
                ->icon('heroicon-o-clock')
                ->color('danger')
                ->description('งวดวันนี้ที่ยังรออนุมัติ'),

            Card::make('ค้างชำระ (สะสม)', number_format($totalDueAccumulated, 2) . ' บาท')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->description('ยอดค้างชำระสะสม (รวมถึงวันนี้เท่านั้น ไม่รวมอนาคต)'),

            Card::make('รายได้รวมวันนี้', number_format($totalIncomeToday, 2) . ' บาท')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary')
                ->description('ลูกค้าชำระแล้ว (วันนี้)'),
        ];
    }
}
