<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallmentPaymentResource\Pages;
use App\Models\InstallmentPayment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;

class InstallmentPaymentResource extends Resource
{
    protected static ?string $model = InstallmentPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'ข้อมูลการชำระเงิน';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('installment_request_id')
                ->relationship('installmentRequest', 'fullname')
                ->label('คำขอผ่อน')->searchable()->required(),
            Forms\Components\TextInput::make('amount')->label('ยอดที่ต้องชำระ')->numeric()->required(),
            Forms\Components\TextInput::make('amount_paid')->label('ยอดที่ชำระแล้ว')->numeric(),
            Forms\Components\DatePicker::make('payment_due_date')->label('วันที่ต้องชำระ')->required(),
            Forms\Components\FileUpload::make('payment_proof')->label('สลิปการชำระเงิน'),
            Forms\Components\Select::make('status')->label('สถานะการชำระ')->options([
                'pending' => 'รอตรวจสอบ',
                'approved' => 'อนุมัติแล้ว',
                'rejected' => 'ปฏิเสธ'
            ]),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('installmentRequest.fullname')->label('ชื่อลูกค้า'),
            Tables\Columns\TextColumn::make('amount_paid')->label('ยอดที่ชำระ')->money('THB'),
            Tables\Columns\TextColumn::make('payment_due_date')->label('วันครบกำหนด')->date('d/m/Y'),
            Tables\Columns\BadgeColumn::make('status')->label('สถานะ')->colors([
                'warning' => 'pending',
                'success' => 'approved',
                'danger' => 'rejected',
            ]),
        ])
        ->filters([])
        ->actions([
            Action::make('approve')
            ->label('อนุมัติ')
            ->action(function (InstallmentPayment $record) {
                $record->update([
                    'status' => 'approved',
                    'payment_status' => 'paid'
                ]);

                $request = $record->installmentRequest;

                if (!$request->first_approved_date) {
                    $request->first_approved_date = now(); // กำหนดวันที่อนุมัติครั้งแรก
                }

                $request->increment('total_paid', $record->amount_paid);
                $request->decrement('remaining_amount', $record->amount_paid);

                $request->updateTotalPenalty(); // อัปเดตค่าปรับสะสมใหม่ล่าสุด
                $request->save();
            })
            ->visible(fn ($record) => $record->status === 'pending')
            ->requiresConfirmation()
            ->icon('heroicon-o-check')
            ->color('success'),

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
