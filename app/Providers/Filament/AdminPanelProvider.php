<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use App\Filament\Resources\AdminResource;
//use App\Filament\Resources\BankAccountResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\InstallmentRequestResource;
use App\Filament\Resources\PaymentQrLogResource; // <<-- เพิ่มใหม่
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\DailySummaryTable;
use App\Filament\Resources\ApprovedInstallmentRequestResource;
use App\Filament\Resources\InstallmentPaymentResource;
use App\Filament\Widgets\FinancialReportWidget;
use App\Filament\Resources\DailyReportResource;
use App\Filament\Resources\DailyReportResource\Widgets\DailyReportOverview;
use App\Filament\Resources\UserTrackingResource;
use App\Filament\Resources\CommissionResource;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->authGuard('admin')
            ->middleware(['web'])
            ->authMiddleware(['auth:admin'])
            ->brandName('WISDOM GOLD BACKEND')
            ->favicon(asset('images/favicon.ico'))
            ->resources([
                AdminResource::class,
                //BankAccountResource::class, // ถ้าไม่ใช้ ลบ/คอมเมนต์ออก
                UserResource::class,
                InstallmentRequestResource::class,
                // PaymentResource::class,   // <-- ลบออก
                ApprovedInstallmentRequestResource::class,
                InstallmentPaymentResource::class,
                DailyReportResource::class,
                UserTrackingResource::class,
                PaymentQrLogResource::class, // <<-- เพิ่มอันนี้เข้าไป
                CommissionResource::class,
            ])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                StatsOverview::class,
                FinancialReportWidget::class,
                DailyReportOverview::class,
                DailySummaryTable::class,
            ]);
    }
}
