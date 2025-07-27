<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovedInstallmentRequestResource\Pages;
use App\Models\InstallmentRequest;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ApprovedInstallmentRequestResource extends Resource
{
    protected static ?string $model = InstallmentRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationGroup = 'การจัดการรายการผ่อน';
    protected static ?string $navigationLabel = 'คำขอผ่อนทองที่อนุมัติแล้ว';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_number')->label('เลขสัญญา')->formatStateUsing(fn($state) => $state ?: '-'),
                Tables\Columns\TextColumn::make('payment_number')->label('เลข INV')->formatStateUsing(fn($state) => $state ?: '-'),
                Tables\Columns\TextColumn::make('fullname')->label('ชื่อ-นามสกุล')->formatStateUsing(fn($state) => $state ?: '-'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('เบอร์โทรศัพท์')
                    ->formatStateUsing(function ($state, $record) {
                        return $state ?: optional($record->user)->phone ?: '-';
                    }),
                Tables\Columns\TextColumn::make('gold_amount')->label('จำนวนทอง (บาท)')->formatStateUsing(fn($state) => $state ?: '-'),
                Tables\Columns\TextColumn::make('approved_gold_price')->label('ราคาทองอนุมัติ')->formatStateUsing(fn($state) => $state ?: '-'),

                Tables\Columns\TextColumn::make('total_gold_price')->label('เงินต้น')->money('THB'),
                Tables\Columns\TextColumn::make('total_with_interest')->label('ยอดรวม')->money('THB'),

                Tables\Columns\TextColumn::make('interest_amount')
                    ->label('ดอกเบี้ย')
                    ->money('THB'),

                Tables\Columns\TextColumn::make('installment_period')->label('จำนวนวัน')->formatStateUsing(fn($state) => $state ?: '-'),
                Tables\Columns\TextColumn::make('daily_payment_amount')->label('ยอดชำระรายวัน')->formatStateUsing(fn($state) => $state ?: '-'),
                Tables\Columns\TextColumn::make('initial_payment')
                    ->label('ยอดชำระวันแรก')
                    ->money('THB')
                    ->getStateUsing(fn($record) => number_format($record->initial_payment, 2)),
                Tables\Columns\TextColumn::make('daily_penalty')->label('ค่าปรับรายวัน')->formatStateUsing(fn($state) => $state ?: '-'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('สถานะ')
                    ->colors(['success' => 'approved', 'primary' => 'completed']),
                Tables\Columns\TextColumn::make('responsible_staff')
                    ->label('พนักงานที่ดูแล')
                    ->formatStateUsing(fn($state) => $state ?: '-'),
                Tables\Columns\TextColumn::make('approvedBy.username')->label('ผู้อนุมัติ')->default('-'),
            ])
            ->actions([
                Action::make('create_or_link_user')
                    ->label('สร้างยูส/เชื่อมยูส + รันวันแรก/งวดแรก')
                    ->color('primary')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn ($record) => empty($record->user_id) && !empty($record->phone))
                    ->action(function ($record) {
                        $user = \App\Models\User::where('phone', $record->phone)->first();
                        if (!$user) {
                            $names = explode(' ', trim($record->fullname));
                            $first = $names[0] ?? '-';
                            $last = $names[1] ?? '';
                            $user = \App\Models\User::create([
                                'first_name' => $first,
                                'last_name' => $last,
                                'phone' => $record->phone,
                                'password' => bcrypt('123456'),
                                'status' => 'active',
                                'id_card_number' => $record->id_card ?? '-',
                            ]);
                        }
                        $record->update(['user_id' => $user->id]);

                        $period = intval($record->installment_period);
                        $paidPeriods = 2;
                        if ($period == 45) $paidPeriods = 3;
                        if ($period == 60) $paidPeriods = 4;

                        $payments = $record->installmentPayments()->orderBy('payment_due_date')->get();
                        $total = $payments->count();

                        foreach ($payments as $i => $p) {
                            $dueDate = Carbon::parse($p->payment_due_date);

                            // paid งวดแรก: ถ้าวันอาทิตย์ เลื่อนไปวันจันทร์
                            if ($i == 0) {
                                if ($dueDate->isSunday()) {
                                    $dueDate = $dueDate->addDay();
                                    $p->payment_due_date = $dueDate->toDateString();
                                }
                                $p->amount_paid = $p->amount;
                                $p->status = 'paid';
                                $p->payment_status = 'paid';
                                $p->admin_notes = 'เงินมัดจำ';
                                $p->save();
                                Log::info('Paid มัดจำ', [
                                    'payment_id' => $p->id,
                                    'due_date' => $p->payment_due_date,
                                ]);
                                continue;
                            }

                            // paid งวดท้ายสุดตาม paidPeriods: ถ้าวันอาทิตย์ เลื่อนไปเสาร์
                            if (
                                $i != 0
                                && $i >= ($total - ($paidPeriods - 1))
                            ) {
                                if ($dueDate->isSunday()) {
                                    $dueDate = $dueDate->subDay();
                                    $p->payment_due_date = $dueDate->toDateString();
                                }
                                $p->amount_paid = $p->amount;
                                $p->status = 'paid';
                                $p->payment_status = 'paid';
                                $p->admin_notes = 'ชำระล่วงหน้า';
                                $p->save();
                                Log::info('Paid ล่วงหน้า', [
                                    'payment_id' => $p->id,
                                    'due_date' => $p->payment_due_date,
                                ]);
                                continue;
                            }

                            $p->amount_paid = 0;
                            $p->status = 'pending';
                            $p->payment_status = 'pending';
                            $p->admin_notes = null;
                            $p->save();
                        }

                        // start_date = payments ที่ไม่ใช่อาทิตย์อันแรก
                        $startPayment = $payments->first(function ($p) {
                            return !Carbon::parse($p->payment_due_date)->isSunday();
                        });
                        if ($startPayment) {
                            $record->start_date = Carbon::parse($startPayment->payment_due_date)->toDateString();
                            $record->save();
                        }

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('สำเร็จ!')
                            ->body('เชื่อมยูสเซอร์ ' . $user->phone . ' กับคำขอผ่อนทอง (paid มัดจำ + ล่วงหน้าตามงวดหลังสุด, ข้ามวันอาทิตย์)');
                    })
                    ->requiresConfirmation(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('approved_gold_price')
                ->numeric()
                ->label('ราคาทองที่อนุมัติ')
                ->required(),
            Forms\Components\TextInput::make('daily_payment_amount')
                ->numeric()
                ->label('ยอดชำระรายวัน')
                ->required(),
            Forms\Components\Select::make('status')
                ->label('สถานะ')
                ->options([
                    'approved' => 'อนุมัติแล้ว',
                    'completed' => 'ผ่อนครบแล้ว',
                ])
                ->required(),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $admin = Auth::guard('admin')->user();
        if ($admin && in_array($admin->role, ['admin', 'OAA'])) {
            return parent::getEloquentQuery()->where('status', 'approved')->with('user');
        }
        return parent::getEloquentQuery()
            ->where('status', 'approved')
            ->where('approved_by', $admin ? $admin->id : null)
            ->with('user');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApprovedInstallmentRequests::route('/'),
            'edit' => Pages\EditApprovedInstallmentRequest::route('/{record}/edit'),
            'view' => Pages\ViewApprovedInstallmentRequest::route('/{record}'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'การจัดการรายการผ่อน';
    }
}
