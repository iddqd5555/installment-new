<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserTrackingResource\Pages;
use App\Models\User;
use App\Models\UserLocationLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserTrackingResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'ศูนย์ติดตามลูกค้า';
    protected static ?string $slug = 'user-trackings';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label('ชื่อ'),
                Tables\Columns\TextColumn::make('last_name')->label('นามสกุล'),
                Tables\Columns\TextColumn::make('phone')->label('เบอร์โทร'),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('เข้าใช้งานล่าสุด')
                    ->getStateUsing(function ($record) {
                        // เอาจาก log ล่าสุดใน user_location_logs
                        $lastLog = UserLocationLog::where('user_id', $record->id)
                            ->orderByDesc('created_at')
                            ->first();
                        return $lastLog?->created_at
                            ? $lastLog->created_at->format('Y-m-d H:i:s')
                            : '-';
                    })
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('ดูประวัติ'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUserTrackings::route('/'),
            'create' => Pages\CreateUserTracking::route('/create'),
            'edit'   => Pages\EditUserTracking::route('/{record}/edit'),
            'view'   => Pages\ViewUserTracking::route('/{record}'),
        ];
    }
}
