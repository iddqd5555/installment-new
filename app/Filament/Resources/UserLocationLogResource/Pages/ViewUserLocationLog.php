<?php

namespace App\Filament\Resources\UserLocationLogResource\Pages;

use App\Filament\Resources\UserLocationLogResource;
use App\Models\UserLocationLog;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\Actions\Action;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class ViewUserLocationLog extends ViewRecord
{
    protected static string $resource = UserLocationLogResource::class;

    public function getLocationLogsForUser()
    {
        return UserLocationLog::where('user_id', $this->record->user_id)
            ->orderByDesc('created_at')->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('กลับหน้าหลัก')
                ->url(route('filament.resources.user-location-logs.index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    public function mount($record)
    {
        parent::mount($record);
    }

    protected function getViewData(): array
    {
        return [
            'locationLogs' => $this->getLocationLogsForUser(),
        ];
    }
}
