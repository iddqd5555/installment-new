<?php

namespace App\Filament\Resources;

use App\Models\PaymentQrLog;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use App\Filament\Resources\PaymentQrLogResource\Pages\ListPaymentQrLogs;
use App\Filament\Resources\PaymentQrLogResource\Pages\CreatePaymentQrLog;
use App\Filament\Resources\PaymentQrLogResource\Pages\EditPaymentQrLog;

class PaymentQrLogResource extends Resource
{
    protected static ?string $model = PaymentQrLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'QR Payment Logs';
    protected static ?string $navigationGroup = 'การจัดการการเงิน';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('user_id')->required(),
            Forms\Components\TextInput::make('amount')->required(),
            Forms\Components\TextInput::make('status')->required(),
            // ปรับแต่ง schema ตามที่คุณต้องการเพิ่มเติมได้
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')->label('User ID'),
                Tables\Columns\TextColumn::make('amount')->label('Amount'),
                Tables\Columns\TextColumn::make('status')->label('Status'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentQrLogs::route('/'),
            'create' => CreatePaymentQrLog::route('/create'),
            'edit' => EditPaymentQrLog::route('/{record}/edit'),
        ];
    }
}
