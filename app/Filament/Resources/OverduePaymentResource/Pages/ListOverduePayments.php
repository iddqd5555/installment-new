<?php

namespace App\Filament\Resources\OverduePaymentResource\Pages;

use App\Filament\Resources\OverduePaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListOverduePayments extends ListRecords
{
    protected static string $resource = OverduePaymentResource::class;
}
