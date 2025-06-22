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
            ->login(\App\Filament\Admin\Pages\Auth\Login::class)
            ->brandName('WISDOM GOLD BACKEND')
            ->favicon(asset('images/favicon.ico'))
            ->resources([
                AdminResource::class,
                BankAccountResource::class,
                UserResource::class,
                InstallmentRequestResource::class,
                PaymentResource::class,
                ApprovedInstallmentRequestResource::class,
            ])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
            StatsOverview::class, // ✅ ต้องเพิ่มกลับไป!
        ]);
    }
}
