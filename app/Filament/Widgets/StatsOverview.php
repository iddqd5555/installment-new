<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\User;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getCards(): array
    {
        $admin = Auth::guard('admin')->user();

        $stats = Cache::remember('admin.dashboard.stats.' . $admin->id, now()->addMinutes(10), function () use ($admin) {
            if (in_array($admin->role, ['admin', 'OAA'])) {
                return [
                    'user_count' => User::count(),
                    'installment_request_count' => InstallmentRequest::count(),
                    'pending_payment_count' => InstallmentPayment::where('status', 'pending')->count(),
                    'total_income' => InstallmentPayment::sum('amount_paid'),
                ];
            }

            return [
                'user_count' => User::whereHas('installmentRequests', function ($query) use ($admin) {
                    $query->where('approved_by', $admin->id);
                })->count(),
                'installment_request_count' => InstallmentRequest::where('approved_by', $admin->id)->count(),
                'pending_payment_count' => InstallmentPayment::where('status', 'pending')
                    ->whereHas('installmentRequest', function ($query) use ($admin) {
                        $query->where('approved_by', $admin->id);
                    })->count(),
                'total_income' => InstallmentPayment::whereHas('installmentRequest', function ($query) use ($admin) {
                        $query->where('approved_by', $admin->id);
                    })->sum('amount_paid'),
            ];
        });

        return [
            Card::make('จำนวนสมาชิกทั้งหมด', $stats['user_count'])
                ->description('สมาชิกที่ดูแล')
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Card::make('คำขอผ่อนทองที่ดูแล', $stats['installment_request_count'])
                ->description('จำนวนรายการขอผ่อนที่รับผิดชอบ')
                ->icon('heroicon-o-document-text')
                ->color('warning'),

            Card::make('รายการชำระที่รออนุมัติ', $stats['pending_payment_count'])
                ->description('รายการที่รอการตรวจสอบ')
                ->icon('heroicon-o-clock')
                ->color('danger'),

            Card::make('รายได้รวม', number_format($stats['total_income'], 2))
                ->description('ยอดเงินที่ได้รับจากการชำระแล้ว')
                ->icon('heroicon-o-currency-dollar')
                ->color('primary'),
        ];
    }
}
