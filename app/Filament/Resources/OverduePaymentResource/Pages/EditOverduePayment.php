<?php

namespace App\Filament\Resources\OverduePaymentResource\Pages;

use App\Filament\Resources\OverduePaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOverduePayment extends EditRecord
{
    protected static string $resource = OverduePaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
