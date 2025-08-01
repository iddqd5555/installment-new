<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use App\Filament\Resources\AdminResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\InstallmentRequestResource;
use App\Filament\Resources\PaymentQrLogResource;
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
use App\Filament\Resources\OverduePaymentResource;
use App\Filament\Resources\ReviewResource;
use App\Filament\Resources\LogoResource;
use App\Filament\Resources\NotificationResource; // <<== เพิ่ม use ตรงนี้
use App\Filament\Resources\BankAccountResource;

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
                UserResource::class,
                InstallmentRequestResource::class,
                ApprovedInstallmentRequestResource::class,
                InstallmentPaymentResource::class,
                OverduePaymentResource::class,
                DailyReportResource::class,
                UserTrackingResource::class,
                PaymentQrLogResource::class,
                CommissionResource::class,
                ReviewResource::class,
                LogoResource::class,
                NotificationResource::class, // <<== เพิ่ม resource ตรงนี้
                BankAccountResource::class, 
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
