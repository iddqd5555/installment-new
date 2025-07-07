<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DailySummaryTable extends BaseWidget
{
    protected static ?string $heading = '📋 สรุปยอดลูกค้ารายวัน (วันนี้)';
    protected int|string|array $columnSpan = 'full';

    public function table(Tables\Table $table): Tables\Table
    {
        $admin = Auth::guard('admin')->user();
        $today = Carbon::today();

        // กรองเฉพาะลูกค้า/contract ที่มีงวดครบกำหนดวันนี้
        $installments = InstallmentRequest::query()
            ->with(['user', 'installmentPayments' => function($q) use ($today) {
                $q->whereDate('payment_due_date', $today);
            }])
            ->where('status', 'approved')
            ->when(
                !in_array($admin->role, ['admin', 'OAA']),
                fn($q) => $q->where('responsible_staff', $admin->id)
            );

        return $table
            ->query($installments)
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('ชื่อลูกค้า')
                    ->sortable(),
                Tables\Columns\TextColumn::make('contract_number')
                    ->label('หมายเลขสัญญา')
                    ->sortable(),
                Tables\Columns\TextColumn::make('installmentPayments.amount')
                    ->label('ยอดที่ต้องชำระวันนี้ (บาท)')
                    ->formatStateUsing(function($record) {
                        return $record->installmentPayments->sum('amount');
                    }),
                Tables\Columns\TextColumn::make('installmentPayments.amount_paid')
                    ->label('ยอดที่ชำระวันนี้ (บาท)')
                    ->formatStateUsing(function($record) {
                        return $record->installmentPayments->sum('amount_paid');
                    }),
                Tables\Columns\TextColumn::make('pending_count')
                    ->label('ค้างชำระ (งวด)')
                    ->formatStateUsing(function($record) use ($today) {
                        return $record->installmentPayments
                            ->where('status', 'pending')
                            ->where('payment_due_date', '<', $today)
                            ->count();
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->colors([
                        'success' => 'approved',
                        'warning' => 'pending',
                        'danger' => 'rejected',
                    ]),
            ]);
    }
}
