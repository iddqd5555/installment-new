<?php

namespace App\Filament\Resources\UserLocationLogResource\Pages;

use App\Filament\Resources\UserLocationLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserLocationLog extends EditRecord
{
    protected static string $resource = UserLocationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
