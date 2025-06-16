<?php

namespace App\Filament\Resources\InstallmentRequestResource\Pages;

use App\Filament\Resources\InstallmentRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInstallmentRequest extends EditRecord
{
    protected static string $resource = InstallmentRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
