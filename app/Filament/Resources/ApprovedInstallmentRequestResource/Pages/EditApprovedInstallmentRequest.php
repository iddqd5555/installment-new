<?php

namespace App\Filament\Resources\ApprovedInstallmentRequestResource\Pages;

use App\Filament\Resources\ApprovedInstallmentRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApprovedInstallmentRequest extends EditRecord
{
    protected static string $resource = ApprovedInstallmentRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
