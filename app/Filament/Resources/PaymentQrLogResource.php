<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Resources\Table;
use App\Models\PaymentQrLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Pages\ListRecords;

class PaymentQrLogResource extends Resource
{
    protected static ?string $model = PaymentQrLog::class;
    protected static ?string $navigationLabel = 'ประวัติรับเงิน QR KBank';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('qr_ref')->label('QR Ref'),
                TextColumn::make('amount')->label('ยอดเงิน'),
                TextColumn::make('status')->label('สถานะ'),
                TextColumn::make('transaction_id')->label('รหัสธุรกรรม'),
                TextColumn::make('customer_id')->label('ลูกค้า')->formatStateUsing(fn($state)=>$state ?: '-'),
                TextColumn::make('installment_payment_id')->label('งวดผ่อน')->formatStateUsing(fn($state)=>$state ?: '-'),
                TextColumn::make('created_at')->dateTime('d/m/Y H:i')->label('เวลาสร้าง'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecords::route('/'),
        ];
    }
}
