<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyReportResource\Pages;
use App\Models\InstallmentPayment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DailyReportResource extends Resource
{
    protected static ?string $model = InstallmentPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'รายงานสรุปการผ่อน';
    protected static ?string $navigationGroup = 'รายงาน';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('payment_due_date')
                ->label('วันที่ชำระ')
                ->date('Y-m-d')
                ->sortable(),
            TextColumn::make('payment_due_date')
                ->label('เวลา')
                ->date('H:i:s'),
            TextColumn::make('installmentRequest.fullname')->label('ชื่อลูกค้า'),
            TextColumn::make('installmentRequest.contract_number')->label('หมายเลขสัญญา'),
            TextColumn::make('installmentRequest.payment_number')->label('เลขที่ใบแจ้งหนี้'),
            TextColumn::make('installmentRequest.approved_gold_price')
                ->label('ราคาทองบาทละ')
                ->money('THB')
                ->formatStateUsing(fn($record) =>
                    number_format($record->installmentRequest->approved_gold_price ?? 0, 2)
                ),
            TextColumn::make('installmentRequest.gold_amount')->label('จำนวนทอง (บาททอง)'),
            TextColumn::make('installmentRequest.total_gold_real_price')
                ->label('รวมราคาทอง (บาท)')
                ->money('THB')
                ->formatStateUsing(fn($record) =>
                    number_format(
                        ($record->installmentRequest->approved_gold_price ?? 0)
                        * ($record->installmentRequest->gold_amount ?? 0), 2
                    )
                ),
            TextColumn::make('amount_paid')->label('ยอดที่ชำระแล้ว (บาท)')->money('THB'),
            TextColumn::make('installmentRequest.remaining_amount')
                ->label('ยอดคงเหลือ (บาททอง)')
                ->formatStateUsing(fn($record) => number_format(
                    max(0, ($record->installmentRequest->gold_amount ?? 0)
                        - (($record->installmentRequest->total_paid ?? 0) / max(1, ($record->installmentRequest->approved_gold_price ?? 1)))
                    ), 2
                )),
            TextColumn::make('installmentRequest.remaining_gold_value')
                ->label('ยอดคงเหลือมูลค่า (บาท)')
                ->money('THB')
                ->formatStateUsing(fn($record) => number_format(
                    max(0,
                        (($record->installmentRequest->approved_gold_price ?? 0)
                        * ($record->installmentRequest->gold_amount ?? 0))
                        - ($record->installmentRequest->total_paid ?? 0)
                    ), 2
                )),
            TextColumn::make('installmentRequest.responsible_staff')->label('พนักงานที่รับผิดชอบ'),
            TextColumn::make('status')->label('สถานะ')->badge()
                ->colors([
                    'warning' => 'pending',
                    'success' => 'approved',
                    'danger' => 'rejected',
                ]),
        ])
        ->filters([
            Tables\Filters\Filter::make('payment_due_date')
                ->form([
                    Forms\Components\DatePicker::make('date_from')->label('จากวันที่'),
                    Forms\Components\DatePicker::make('date_until')->label('ถึงวันที่'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['date_from'], fn($q) => $q->whereDate('payment_due_date', '>=', $data['date_from']))
                        ->when($data['date_until'], fn($q) => $q->whereDate('payment_due_date', '<=', $data['date_until']));
                }),
            Tables\Filters\SelectFilter::make('staff')
                ->label('พนักงานที่ดูแล')
                ->options(fn() => \App\Models\Admin::pluck('username', 'id')->toArray())
                ->searchable()
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['value'])) {
                        $query->whereHas('installmentRequest', fn($q) => $q->where('responsible_staff', $data['value']));
                    }
                }),
        ])
        ->modifyQueryUsing(function (Builder $query) {
            $dateFrom = session('daily_reports.date_from', Carbon::today()->toDateString());
            $dateUntil = session('daily_reports.date_until', Carbon::today()->toDateString());
            $admin = Auth::guard('admin')->user();

            $query->whereBetween('payment_due_date', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateUntil)->endOfDay()
            ]);

            if (!in_array($admin->role, ['admin', 'OAA'])) {
                $query->whereHas('installmentRequest', function($q) use ($admin) {
                    $q->where('responsible_staff', $admin->id);
                });
            }
        });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyReports::route('/'),
        ];
    }
}
