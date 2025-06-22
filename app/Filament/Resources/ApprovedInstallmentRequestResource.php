<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovedInstallmentRequestResource\Pages;
use App\Models\InstallmentRequest;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ApprovedInstallmentRequestResource extends Resource
{
    protected static ?string $model = InstallmentRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationLabel = 'รายการผ่อนทองที่อนุมัติ';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.first_name')->label('ชื่อ'),
                Tables\Columns\TextColumn::make('user.last_name')->label('นามสกุล'),
                Tables\Columns\TextColumn::make('user.phone')->label('เบอร์โทรศัพท์'),
                Tables\Columns\TextColumn::make('gold_amount')->label('จำนวนทอง (บาท)'),
                Tables\Columns\TextColumn::make('approved_gold_price')->label('ราคาทองอนุมัติ'),
                Tables\Columns\TextColumn::make('installment_period')->label('จำนวนวัน'),
                Tables\Columns\TextColumn::make('daily_payment_amount')->label('ยอดชำระรายวัน'),
                Tables\Columns\TextColumn::make('penalty_amount')->label('ค่าปรับรายวัน'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('สถานะ')
                    ->colors(['success' => 'approved', 'primary' => 'completed']),
                Tables\Columns\TextColumn::make('approvedBy.username')->label('ผู้อนุมัติ'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
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

        if (in_array($admin->role, ['admin', 'OAA'])) {
            return parent::getEloquentQuery()->where('status', 'approved');
        }

        return parent::getEloquentQuery()
            ->where('status', 'approved')
            ->where('approved_by', $admin->id);
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
