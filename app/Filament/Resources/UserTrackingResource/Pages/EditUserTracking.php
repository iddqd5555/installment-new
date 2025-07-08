<?php

namespace App\Filament\Resources\UserTrackingResource\Pages;

use App\Filament\Resources\UserTrackingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserTracking extends EditRecord
{
    protected static string $resource = UserTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
