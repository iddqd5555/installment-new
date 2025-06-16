<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Widgets\StatsOverview;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->authGuard('admin') // ✅ ใช้ guard admin ชัดเจน
            ->brandName('WISDOM GOLD BACKEND')
            ->favicon(asset('images/favicon.ico'))
            ->brandLogo(null) // ✅ ปิดการใช้รูปชัดเจน (ถ้าต้องการเปิดให้แก้ไขตรงนี้)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->middleware([
                'web', 'check_admin' // ✅ Middleware แยกเฉพาะ Admin
            ])
            ->navigationGroups([
                'การจัดการสมาชิก',
                'การจัดการรายการผ่อน',
                'การจัดการการเงิน',
            ])
            ->resources([
                \App\Filament\Resources\BankAccountResource::class,
                //\App\Filament\Resources\AdminResource::class, // ✅ เพิ่ม resource สำหรับจัดการแอดมิน
                \App\Filament\Resources\UserResource::class, // ✅ จัดการสมาชิกผู้ใช้งาน
                \App\Filament\Resources\InstallmentRequestResource::class, // ✅ จัดการรายการผ่อน
                 \App\Filament\Resources\PaymentResource::class, // สามารถเปิดใช้งานตามต้องการ
            ])
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'admin'
            )
            ->widgets([
                StatsOverview::class,
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->plugins([]);
    }
}
