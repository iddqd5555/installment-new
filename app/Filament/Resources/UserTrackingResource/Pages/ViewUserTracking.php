<?php

namespace App\Filament\Resources\UserTrackingResource\Pages;

use App\Filament\Resources\UserTrackingResource;
use App\Models\UserLocationLog;
use Filament\Resources\Pages\ViewRecord;

class ViewUserTracking extends ViewRecord
{
    protected static string $resource = UserTrackingResource::class;

    public function getLocationLogs()
    {
        return UserLocationLog::where('user_id', $this->record->id)
            ->orderByDesc('created_at')->get();
    }

    protected function getViewData(): array
    {
        return [
            'locationLogs' => $this->getLocationLogs(),
        ];
    }
}
