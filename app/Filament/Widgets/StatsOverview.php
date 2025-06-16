<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    protected function getCards(): array
    {
        $stats = Cache::remember('admin.dashboard.stats', now()->addMinutes(10), function () {
            return [
                'user_count' => User::count(),
                'installment_request_count' => InstallmentRequest::count(),
                'pending_payment_count' => InstallmentPayment::where('status', 'pending')->count(),
                'total_income' => InstallmentPayment::sum('amount_paid'),
            ];
        });

        return [
            Card::make('จำนวนสมาชิกทั้งหมด', $stats['user_count'])
                ->description('สมาชิกทั้งหมดที่มีอยู่ในระบบ')
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Card::make('คำขอผ่อนทองทั้งหมด', $stats['installment_request_count'])
                ->description('จำนวนรายการขอผ่อนทั้งหมด')
                ->icon('heroicon-o-document-text')
                ->color('warning'),

            Card::make('รายการชำระที่รออนุมัติ', $stats['pending_payment_count'])
                ->description('จำนวนรายการที่รอการตรวจสอบ')
                ->icon('heroicon-o-clock')
                ->color('danger'),

            Card::make('รายได้รวม', number_format($stats['total_income'], 2))
                ->description('ยอดเงินที่ได้รับจากการชำระแล้ว')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary'),
        ];
    }
}

