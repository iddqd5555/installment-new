<?php

namespace App\Filament\Resources\OverduePaymentResource\Pages;

use App\Filament\Resources\OverduePaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOverduePayment extends CreateRecord
{
    protected static string $resource = OverduePaymentResource::class;
}
