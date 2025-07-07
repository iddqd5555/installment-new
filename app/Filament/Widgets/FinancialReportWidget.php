<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use App\Models\InstallmentRequest;
use Illuminate\Support\Facades\Auth;

class FinancialReportWidget extends BaseWidget
{
    protected static ?string $heading = '📊 รายงานการชำระเงินและค่าปรับ';
    protected int|string|array $columnSpan = 'full';

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user();

        return $table
            ->query(
                \App\Models\InstallmentRequest::query()
                    ->with(['user', 'installmentPayments'])
                    ->where('status', 'approved')
                    ->when(
                        !in_array($admin->role, ['admin', 'OAA']),
                        fn($q) => $q->where('responsible_staff', $admin->id)
                    )
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('user.first_name')
                    ->label('ชื่อลูกค้า')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('total_paid')
                    ->label('ยอดชำระแล้ว (บาท)')
                    ->money('THB')
                    ->sortable()
                    ->formatStateUsing(fn($record) => number_format(
                        $record->installmentPayments()->where('status', 'approved')->sum('amount_paid'), 2
                    )),

                \Filament\Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('ยอดคงเหลือ (บาท)')
                    ->money('THB')
                    ->sortable()
                    ->formatStateUsing(fn($record) =>
                        number_format(
                            max(0, ($record->total_with_interest ?? 0)
                                - $record->installmentPayments()->where('status', 'approved')->sum('amount_paid')
                            ), 2
                        )
                    ),

                \Filament\Tables\Columns\TextColumn::make('advance_payment')
                    ->label('ยอดชำระล่วงหน้า (บาท)')
                    ->money('THB')
                    ->sortable()
                    ->formatStateUsing(fn($record) =>
                        number_format($record->advance_payment ?? 0, 2)
                    ),

                \Filament\Tables\Columns\TextColumn::make('total_penalty')
                    ->label('ค่าปรับสะสม (บาท)')
                    ->money('THB')
                    ->sortable()
                    ->formatStateUsing(fn($record) =>
                        number_format($record->total_penalty ?? 0, 2)
                    ),

                \Filament\Tables\Columns\TextColumn::make('next_payment_date')
                    ->label('วันชำระครั้งถัดไป')
                    ->date('d/m/Y')
                    ->sortable()
                    ->formatStateUsing(fn($record) =>
                        optional(
                            $record->installmentPayments()
                                ->where('status', 'pending')
                                ->orderBy('payment_due_date')
                                ->first()
                        )?->payment_due_date
                    ),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('has_penalty')
                    ->label('ลูกค้าที่มีค่าปรับ')
                    ->query(fn ($query) => $query->where('total_penalty', '>', 0)),

                \Filament\Tables\Filters\Filter::make('has_advance')
                    ->label('ลูกค้าที่ชำระล่วงหน้า')
                    ->query(fn ($query) => $query->where('advance_payment', '>', 0)),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('total_penalty', 'desc');
    }
}
