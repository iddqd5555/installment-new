<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'การชำระเงิน';
    protected static ?string $navigationGroup = 'การจัดการรายการผ่อน';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('installment_request_id')
                    ->label('รหัสคำขอผ่อน')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('amount')
                    ->label('จำนวนเงิน')
                    ->required()
                    ->numeric(),

                Forms\Components\TextInput::make('payment_method')
                    ->label('ช่องทางการชำระเงิน')
                    ->maxLength(255),

                Forms\Components\FileUpload::make('payment_proof')
                    ->label('หลักฐานการชำระเงิน')
                    ->image()
                    ->directory('payment_slips')
                    ->required(),
                    
                Forms\Components\Select::make('payment_status')
                    ->label('สถานะการชำระเงิน')
                    ->options([
                        'pending' => 'รอตรวจสอบ',
                        'approved' => 'อนุมัติแล้ว',
                        'rejected' => 'ปฏิเสธแล้ว'
                    ])->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('installment_request_id')
                    ->label('รหัสคำขอผ่อน')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('จำนวนเงิน')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('ช่องทางการชำระเงิน')
                    ->searchable(),

                Tables\Columns\ImageColumn::make('payment_proof')
                    ->label('หลักฐานชำระเงิน')
                    ->width(100)
                    ->height(100),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('สถานะชำระเงิน')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('วันที่สร้าง')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('อนุมัติ')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['payment_status' => 'approved']);

                        Notification::make()
                            ->title('✅ อนุมัติการชำระเงินเรียบร้อย')
                            ->success()
                            ->send();
                    })
                    ->hidden(fn ($record) => $record->payment_status === 'approved'),

                Tables\Actions\Action::make('reject')
                    ->label('ปฏิเสธ')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['payment_status' => 'rejected']);

                        Notification::make()
                            ->title('❌ ปฏิเสธการชำระเงินเรียบร้อย')
                            ->danger()
                            ->send();
                    })
                    ->hidden(fn ($record) => $record->payment_status === 'rejected'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
