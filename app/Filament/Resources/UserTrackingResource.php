<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserTrackingResource\Pages;
use App\Models\User;
use App\Models\UserLocationLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class UserTrackingResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'ศูนย์ติดตามลูกค้า';
    protected static ?string $pluralModelLabel = 'ศูนย์ติดตามลูกค้า';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')->label('ชื่อ'),
                TextColumn::make('last_name')->label('นามสกุล'),
                TextColumn::make('phone')->label('เบอร์โทร'),
                TextColumn::make('last_log')
                    ->label('เข้าใช้งานล่าสุด')
                    ->formatStateUsing(function ($state, $record) {
                        $log = UserLocationLog::where('user_id', $record->id)
                            ->orderByDesc('created_at')->first();
                        return $log ? $log->created_at->format('Y-m-d H:i:s') : '-';
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make('view')->label('ดูประวัติ')->icon('heroicon-o-eye'),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserTrackings::route('/'),
            'view' => Pages\ViewUserTracking::route('/{record}'),
        ];
    }
}
