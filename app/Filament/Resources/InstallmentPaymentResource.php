<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallmentPaymentResource\Pages;
use App\Models\InstallmentPayment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class InstallmentPaymentResource extends Resource
{
    protected static ?string $model = InstallmentPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'ข้อมูลการชำระเงิน';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('installment_request_id')->relationship('installmentRequest', 'fullname')->label('คำขอผ่อน')->searchable(),
            Forms\Components\TextInput::make('amount')->label('ยอดที่ต้องชำระ')->numeric()->required(),
            Forms\Components\TextInput::make('amount_paid')->label('ยอดที่ชำระแล้ว')->numeric(),
            Forms\Components\DatePicker::make('payment_due_date')->label('วันที่ต้องชำระ')->required(),
            Forms\Components\FileUpload::make('payment_proof')->label('สลิปการชำระเงิน'),
            Forms\Components\Select::make('status')->label('สถานะการชำระ')->options([
                'pending' => 'รอตรวจสอบ', 'approved' => 'อนุมัติแล้ว', 'rejected' => 'ปฏิเสธ'
            ]),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('amount')->label('ยอดชำระ'),
            Tables\Columns\TextColumn::make('payment_due_date')->label('วันครบกำหนด'),
            Tables\Columns\TextColumn::make('status')->label('สถานะ')->badge()
                ->colors([
                    'warning' => 'pending',
                    'success' => 'approved',
                    'danger' => 'rejected'
                ]),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallmentPayments::route('/'),
            'create' => Pages\CreateInstallmentPayment::route('/create'),
            'edit' => Pages\EditInstallmentPayment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'การจัดการการเงิน';
    }
}
