<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommissionResource\Pages;
use App\Models\Commission;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CommissionResource extends Resource
{
    protected static ?string $model = Commission::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'ค่าคอมมิชชัน';
    protected static ?string $navigationGroup = 'รายงาน';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth('admin')->check() && in_array(auth('admin')->user()->role, ['admin', 'OAA']);
    }

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        // ไม่จำเป็น เพราะค่าคอมสร้างอัตโนมัติ
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('admin.username')->label('พนักงาน')->searchable(),
                Tables\Columns\TextColumn::make('total_collected')->label('ยอดเก็บเงิน')->money('THB'),
                Tables\Columns\TextColumn::make('commission_rate')->label('อัตรา %')->suffix('%'),
                Tables\Columns\TextColumn::make('commission_amount')->label('ยอดคอมมิชชัน')->money('THB'),
                Tables\Columns\TextColumn::make('calculation_date')->label('วันที่คำนวณ')->date('d/m/Y'),
            ])
            ->filters([])
            ->actions([]) // ไม่ให้ Edit/ลบ/สร้าง
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissions::route('/'),
        ];
    }
}
