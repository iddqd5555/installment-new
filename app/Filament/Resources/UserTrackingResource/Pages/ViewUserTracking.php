<?php

namespace App\Filament\Resources\UserTrackingResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\UserTrackingResource;
use Carbon\Carbon;

class ViewUserTracking extends ViewRecord
{
    protected static string $resource = UserTrackingResource::class;

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        // ดึง GPS 7 วันล่าสุด (สูงสุด 30 รายการ)
        $logs = $this->record?->userLocationLogs()
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        // เรียก view blade: resources/views/filament/custom/gps-history-table.blade.php
        return view('filament.custom.gps-history-table', ['logs' => $logs]);
    }
}
