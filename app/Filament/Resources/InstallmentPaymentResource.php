<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallmentPaymentResource\Pages;
use App\Models\InstallmentPayment;
use App\Models\InstallmentRequest;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;

class InstallmentPaymentResource extends Resource
{
    protected static ?string $model = InstallmentPayment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'การจัดการการเงิน';

    public static function getEloquentQuery(): Builder
    {
        $user = auth('admin')->user();
        $query = parent::getEloquentQuery();
        if ($user && $user->role === 'staff') {
            $query->whereHas('installmentRequest', function($q) use ($user) {
                $q->where('responsible_staff', $user->username);
            });
        }
        return $query;
    }

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
            Forms\Components\TextInput::make('ref')->label('INV/งวด')->disabled(),
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
            Tables\Columns\TextColumn::make('ref')->label('INV/งวด'),
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
                        $request->first_approved_date = now();
                    }
                    $request->increment('total_paid', $record->amount_paid);
                    $request->decrement('remaining_amount', $record->amount_paid);
                    $request->updateTotalPenalty();
                    $request->save();
                })
                ->visible(fn ($record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->icon('heroicon-o-check')
                ->color('success'),

            Action::make('download_pdf')
                ->label('ดาวน์โหลดใบเสร็จ')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->action(function (InstallmentPayment $record) {
                    $pdf = Pdf::loadView('pdf.receipt', [
                        'payment' => $record,
                        'contract' => $record->installmentRequest,
                        'customer' => $record->installmentRequest->user,
                    ]);

                    return response()->streamDownload(function () use ($pdf, $record) {
                        echo $pdf->stream();
                    }, 'RECEIPT_' . $record->ref . '.pdf');
                }),

            Action::make('sunmi_print')
                ->label('พิมพ์ใบเสร็จ Sunmi V2')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn($record) => route('sunmi.print', ['id' => $record->id]), true),

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
