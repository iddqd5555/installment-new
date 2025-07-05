<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use App\Models\InstallmentRequest;

class FinancialReportWidget extends BaseWidget
{
    protected static ?string $heading = '📊 รายงานการชำระเงินและค่าปรับ';
    protected int|string|array $columnSpan = 'full';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                InstallmentRequest::query()
                    ->with(['user'])
                    ->where('status', 'approved')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('ชื่อลูกค้า')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_paid')
                    ->label('ยอดชำระแล้ว (บาท)')
                    ->money('THB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('ยอดคงเหลือ (บาท)')
                    ->money('THB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('advance_payment')
                    ->label('ยอดชำระล่วงหน้า (บาท)')
                    ->money('THB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_penalty')
                    ->label('ค่าปรับสะสม (บาท)')
                    ->money('THB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('next_payment_date')
                    ->label('วันชำระครั้งถัดไป')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_penalty')
                    ->label('ลูกค้าที่มีค่าปรับ')
                    ->query(fn ($query) => $query->where('total_penalty', '>', 0)),

                Tables\Filters\Filter::make('has_advance')
                    ->label('ลูกค้าที่ชำระล่วงหน้า')
                    ->query(fn ($query) => $query->where('advance_payment', '>', 0)),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('total_penalty', 'desc');
    }
}
