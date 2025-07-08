<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserLocationLogResource\Pages;
use App\Models\UserLocationLog;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class UserLocationLogResource extends Resource
{
    protected static ?string $model = UserLocationLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'ศูนย์ติดตามลูกค้า';
    protected static ?string $pluralModelLabel = 'ศูนย์ติดตามลูกค้า';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('ชื่อ'),
                TextColumn::make('phone')->label('เบอร์โทร'),
                TextColumn::make('latitude')->label('Lat'),
                TextColumn::make('longitude')->label('Lng'),
                TextColumn::make('maps')
                    ->label('Map')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->latitude && $record->longitude) {
                            $url = 'https://www.google.com/maps?q=' . $record->latitude . ',' . $record->longitude;
                            return "<a href=\"$url\" target=\"_blank\" class=\"inline-flex items-center px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700\"><i class='bi bi-geo-alt-fill me-1'></i>เปิดแผนที่</a>";
                        }
                        return '-';
                    })
                    ->html(),
                BadgeColumn::make('vpn_status')->label('สถานะ')
                    ->colors([
                        'success' => 'ok',
                        'warning' => 'foreign',
                        'danger' => 'mock',
                        'danger' => 'vpn',
                    ])->formatStateUsing(fn($state) => match($state) {
                        'ok' => 'ปกติ',
                        'foreign' => 'นอกไทย',
                        'mock' => 'Mock',
                        'vpn' => 'VPN',
                        default => $state,
                    }),
                TextColumn::make('ip')->label('IP'),
                TextColumn::make('created_at')->label('เวลา')->since(),
            ])
            ->filters([
                // เพิ่ม filters ถ้าต้องการ
            ])
            ->actions([
                Tables\Actions\ViewAction::make('view')->label('ดู log ทั้งหมด')->icon('heroicon-o-eye'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                // ไม่ให้แก้ไข log
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserLocationLogs::route('/'),
            'view' => Pages\ViewUserLocationLog::route('/{record}'),
        ];
    }
}
