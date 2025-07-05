<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use App\Filament\Resources\AdminResource;
use App\Filament\Resources\BankAccountResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\InstallmentRequestResource;
use App\Filament\Resources\PaymentResource;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Resources\ApprovedInstallmentRequestResource;
use App\Filament\Resources\InstallmentPaymentResource;
use App\Filament\Widgets\FinancialReportWidget;

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
                BankAccountResource::class,
                UserResource::class,
                InstallmentRequestResource::class,
                PaymentResource::class,
                ApprovedInstallmentRequestResource::class,
                InstallmentPaymentResource::class, // 👈 เพิ่มบรรทัดนี้ชัดเจน
            ])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                StatsOverview::class,
                FinancialReportWidget::class,
            ]);
    }
}
