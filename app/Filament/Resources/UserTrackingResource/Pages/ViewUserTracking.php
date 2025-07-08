<?php

namespace App\Filament\Resources\UserTrackingResource\Pages;

use App\Filament\Resources\UserTrackingResource;
use App\Models\UserLocationLog;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Actions\ActionEntry;

class ViewUserTracking extends ViewRecord
{
    protected static string $resource = UserTrackingResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        $logs = UserLocationLog::where('user_id', $this->record->id)
            ->orderByDesc('created_at')
            ->get();

        // ถ้าไม่มี log
        if ($logs->isEmpty()) {
            return $infolist->schema([
                Section::make('ประวัติเข้าใช้งาน')
                    ->schema([
                        TextEntry::make('no_logs')
                            ->label('')
                            ->default('ไม่มีประวัติการใช้งาน')
                    ]),
            ]);
        }

        // ถ้ามี log
        $schemas = [];
        foreach ($logs as $i => $log) {
            $schemas[] = Section::make('Log #' . ($i + 1) . ': ' . ($log->created_at?->format('Y-m-d H:i:s')))
                ->schema([
                    TextEntry::make('ip')->label('IP')->default($log->ip),
                    TextEntry::make('vpn_status')->label('VPN')->default($log->vpn_status),
                    TextEntry::make('latitude')->label('Latitude')->default($log->latitude),
                    TextEntry::make('longitude')->label('Longitude')->default($log->longitude),
                    TextEntry::make('notes')->label('Notes')->default($log->notes),
                    TextEntry::make('map')
                        ->label('แผนที่')
                        ->default(($log->latitude && $log->longitude)
                            ? '<a href="https://www.google.com/maps?q='.$log->latitude.','.$log->longitude.'" target="_blank" style="color:#fff;background:#2563eb;padding:3px 10px;border-radius:6px;font-weight:bold;text-decoration:none;">ดูแผนที่</a>'
                            : '-'
                        )
                        ->html(),
                ]);
        }

        return $infolist->schema($schemas);
    }
}
