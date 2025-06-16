<?php

namespace admin\InstallmentRequestResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\InstallmentRequest;

class InstallmentSummaryWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('คำขอรออนุมัติ', InstallmentRequest::where('status', 'pending')->count())
                ->description('คำขอที่รออนุมัติอยู่')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('คำขออนุมัติแล้ว', InstallmentRequest::where('status', 'approved')->count())
                ->description('คำขอที่อนุมัติเรียบร้อยแล้ว')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('คำขอที่ถูกปฏิเสธ', InstallmentRequest::where('status', 'rejected')->count())
                ->description('คำขอที่ถูกปฏิเสธทั้งหมด')
                ->color('danger')
                ->icon('heroicon-o-x-circle'),

            Stat::make('ยอดรวมคำขอที่อนุมัติแล้ว (บาท)', number_format(InstallmentRequest::where('status', 'approved')->sum('approved_gold_price'), 2))
                ->description('ยอดเงินรวมที่อนุมัติแล้ว')
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
