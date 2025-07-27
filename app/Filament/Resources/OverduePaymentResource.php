<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OverduePaymentResource\Pages;
use App\Models\InstallmentPayment;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class OverduePaymentResource extends Resource
{
    protected static ?string $model = InstallmentPayment::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';
    protected static ?string $navigationGroup = 'รายงาน';
    protected static ?string $navigationLabel = 'ติดตามยอดค้างชำระ';

    public static function getEloquentQuery(): Builder
    {
        $today = Carbon::today()->toDateString();
        // เอาเฉพาะ payment ที่ยังไม่ได้จ่าย + วันที่ <= วันนี้
        return parent::getEloquentQuery()
            ->where(function($q) {
                $q->where('status', 'pending')
                  ->orWhere('payment_status', 'pending');
            })
            ->whereDate('payment_due_date', '<=', $today)
            ->with(['installmentRequest.user']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('installmentRequest.contract_number')
                    ->label('เลขที่สัญญา')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('installmentRequest.user.full_name')
                    ->label('ชื่อลูกค้า')
                    ->getStateUsing(function ($record) {
                        $user = $record->installmentRequest?->user;
                        if ($user) {
                            return ($user->first_name ?? '') . ' ' . ($user->last_name ?? '');
                        }
                        return $record->installmentRequest?->fullname ?? '-';
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('installmentRequest.user.phone')
                    ->label('เบอร์โทร')
                    ->getStateUsing(function ($record) {
                        return $record->installmentRequest?->user?->phone ?? $record->installmentRequest?->phone ?? '-';
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('installment_number')
                    ->label('งวดที่'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('ยอดค้าง')
                    ->money('THB', true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_due_date')
                    ->label('วันที่ครบกำหนด')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('filter_type')
                    ->label('ดูตาม')
                    ->options([
                        'daily' => 'รายวัน',
                        'monthly' => 'รายเดือน',
                        'yearly' => 'รายปี',
                    ])
                    ->default('daily')
                    ->query(function (Builder $query, array $data) {
                        $today = Carbon::today();
                        if (!isset($data['value']) || !$data['value']) return;
                        if ($data['value'] === 'daily') {
                            $query->whereDate('payment_due_date', $today);
                        } elseif ($data['value'] === 'monthly') {
                            $query->whereMonth('payment_due_date', $today->month)
                                  ->whereYear('payment_due_date', $today->year)
                                  ->whereDate('payment_due_date', '<=', $today);
                        } elseif ($data['value'] === 'yearly') {
                            $query->whereYear('payment_due_date', $today->year)
                                  ->whereDate('payment_due_date', '<=', $today);
                        }
                    }),
            ])
            ->defaultSort('payment_due_date', 'asc');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOverduePayments::route('/'),
        ];
    }
}
