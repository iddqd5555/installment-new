<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallmentRequestResource\Pages;
use App\Models\InstallmentRequest;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InstallmentRequestResource extends Resource
{
    protected static ?string $model = InstallmentRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'การจัดการรายการผ่อน';
    protected static ?string $navigationLabel = 'คำขอผ่อนทอง';

    public static function getEloquentQuery(): Builder
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) return parent::getEloquentQuery()->whereRaw('1=0');

        if ($admin->role === 'OAA') {
            return parent::getEloquentQuery()->where('status', '!=', 'approved');
        }

        if (in_array($admin->role, ['admin', 'staff'])) {
            return parent::getEloquentQuery()->where('status', '!=', 'approved');
        }

        return parent::getEloquentQuery()->whereRaw('1=0');
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_number')->label('เลขสัญญา'),
                Tables\Columns\TextColumn::make('payment_number')->label('เลข INV'),
                Tables\Columns\TextColumn::make('fullname')->label('ชื่อ-นามสกุล'),
                Tables\Columns\TextColumn::make('id_card')->label('เลขบัตรประชาชน')->default('-'),
                Tables\Columns\TextColumn::make('phone')->label('เบอร์โทรศัพท์')->default('-'),
                Tables\Columns\TextColumn::make('referrer_code')->label('รหัสผู้แนะนำ')->default('-'),
                Tables\Columns\TextColumn::make('gold_amount')->label('น้ำหนักทอง'),
                Tables\Columns\TextColumn::make('approved_gold_price')->label('ราคาทอง'),
                Tables\Columns\TextColumn::make('total_gold_price')->label('ราคาทองสุทธิ'),
                Tables\Columns\TextColumn::make('installment_period')->label('จำนวนวัน'),
                Tables\Columns\TextColumn::make('total_with_interest')->label('ยอดรวมที่ต้องผ่อน')->money('THB'),
                Tables\Columns\TextColumn::make('interest_amount')
                    ->label('ดอกเบี้ย')
                    ->money('THB'),
                Tables\Columns\TextColumn::make('daily_payment_amount')->label('ยอดผ่อน/วัน')->money('THB'),
                Tables\Columns\TextColumn::make('initial_payment')
                    ->label('ยอดชำระวันแรก')
                    ->money('THB')
                    ->getStateUsing(fn($record) => number_format($record->initial_payment, 2)),
                Tables\Columns\TextColumn::make('advance_payment')->label('เงินในกระเป๋า')->money('THB'),
                Tables\Columns\TextColumn::make('responsible_staff')->label('พนักงานดูแล')->default('-'),
                Tables\Columns\BadgeColumn::make('status')->label('สถานะ')->colors([
                    'warning' => 'pending',
                    'primary' => 'staff_approved',
                    'success' => 'approved',
                    'danger' => 'rejected',
                ]),
                Tables\Columns\TextColumn::make('approvedBy.username')->label('ผู้อนุมัติ')->default('-'),
            ])
            ->actions([
                Action::make('approveFirst')
                    ->label('อนุมัติรอบแรก')
                    ->icon('heroicon-o-check')
                    ->action(function (InstallmentRequest $record) {
                        $admin = Auth::guard('admin')->user();
                        if (!in_array($admin->role, ['admin', 'staff'])) abort(403);
                        if ($record->status !== 'pending') return;
                        $record->update([
                            'status' => 'staff_approved',
                            'first_approved_date' => now(),
                            'responsible_staff' => $admin->username,
                        ]);
                        Notification::make()->title('อนุมัติรอบแรกแล้ว')->success()->send();
                    })
                    ->visible(fn ($record) =>
                        $record->status === 'pending' &&
                        in_array(Auth::guard('admin')->user()->role, ['admin', 'staff'])
                    )
                    ->requiresConfirmation(),

                Action::make('approveOAA')
                    ->label('อนุมัติ (OAA)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (InstallmentRequest $record) {
                        $admin = Auth::guard('admin')->user();
                        if ($admin->role !== 'OAA') abort(403);
                        if (!in_array($record->status, ['pending', 'staff_approved'])) return;
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => $admin->id,
                        ]);
                        $record->generatePayments();
                        Notification::make()->title('OAA อนุมัติสำเร็จ')->success()->send();
                    })
                    ->visible(fn ($record) =>
                        in_array($record->status, ['pending', 'staff_approved']) &&
                        Auth::guard('admin')->user()->role === 'OAA'
                    )
                    ->requiresConfirmation(),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('fullname')->label('ชื่อ-นามสกุล')->required(),
            Forms\Components\TextInput::make('id_card')->label('เลขบัตรประชาชน')->nullable(),
            Forms\Components\TextInput::make('phone')->label('เบอร์โทรศัพท์')->nullable(),
            Forms\Components\TextInput::make('gold_amount')->label('น้ำหนักทอง')->numeric()->required(),
            Forms\Components\TextInput::make('approved_gold_price')->label('ราคาทอง')->numeric()->required(),
            Forms\Components\Select::make('installment_period')->label('จำนวนวัน')->options([
                30 => '30 วัน', 45 => '45 วัน', 60 => '60 วัน',
            ])->required(),
            Forms\Components\DatePicker::make('start_date')->label('วันที่เริ่มผ่อน')->required(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallmentRequests::route('/'),
            'create' => Pages\CreateInstallmentRequest::route('/create'),
            'edit' => Pages\EditInstallmentRequest::route('/{record}/edit'),
        ];
    }

    // ✅ สำคัญ! ให้คำนวณยอดหลังบันทึกทุกครั้ง
    public static function afterSave($record)
    {
        $record->calculateInstallmentAmounts();
    }
}
