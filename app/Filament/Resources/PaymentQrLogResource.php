<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use App\Models\PaymentQrLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PaymentQrLogResource\Pages;

class PaymentQrLogResource extends Resource
{
    protected static ?string $model = PaymentQrLog::class;
    protected static ?string $navigationLabel = 'ประวัติรับเงิน QR KBank';
    protected static ?string $navigationGroup = 'การจัดการการเงิน';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('qr_ref')->label('QR Ref')->copyable()->searchable(),
                TextColumn::make('amount')->label('ยอดเงิน')->money('THB')->sortable(),
                TextColumn::make('status')->label('สถานะ')->badge()
                    ->colors([
                        'success' => 'paid',
                        'danger' => 'void',
                        'warning' => 'pending',
                    ])
                    ->sortable(),
                TextColumn::make('transaction_id')->label('รหัสธุรกรรม')->copyable()->searchable(),
                TextColumn::make('customer_id')->label('ลูกค้า')
                    ->getStateUsing(fn($record)=> $record->customer_id ? optional($record->customer)->name ?? $record->customer_id : '-')
                    ->searchable(),
                TextColumn::make('installment_payment_id')->label('งวดผ่อน')
                    ->getStateUsing(fn($record)=> $record->installment_payment_id ? optional($record->installmentPayment)->due_date ?? $record->installment_payment_id : '-')
                    ->searchable(),
                TextColumn::make('created_at')->dateTime('d/m/Y H:i')->label('เวลาสร้าง')->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('จาก'),
                        DatePicker::make('created_until')->label('ถึง'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
                SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        'paid' => 'จ่ายแล้ว',
                        'pending' => 'รอจ่าย',
                        'void' => 'ยกเลิก/คืนเงิน',
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    ExportAction::make()->label('Export รายงาน')->fileName('qr_logs_report_'.now()->format('Ymd_His')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentQrLogs::route('/'),
        ];
    }
}
