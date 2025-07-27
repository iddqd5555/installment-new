<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Models\Notification;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'แจ้งเตือนระบบ';
    protected static ?string $navigationGroup = 'การแจ้งเตือน';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\Textarea::make('message')->required(),
            Forms\Components\Select::make('type')
                ->options([
                    Notification::TYPE_PAYMENT => 'แจ้งเตือนงวดผ่อน',
                    Notification::TYPE_SLIP => 'แจ้งเตือนสลิป',
                    Notification::TYPE_ANNOUNCE => 'ประกาศ',
                    Notification::TYPE_PAYMENT_OVERDUE_ADMIN => 'แจ้งเตือนค้างจ่าย',
                    Notification::TYPE_PAYMENT_OVERDUE_ADMIN_HIGHLIGHT => '❗ แจ้งเตือนค้างจ่ายด่วน (3 วัน+)',
                    Notification::TYPE_ADVANCE_DEDUCTED => 'หักเงินล่วงหน้า',
                    Notification::TYPE_OTHER => 'อื่นๆ',
                ])
                ->required(),
            Forms\Components\Select::make('role')
                ->options([
                    Notification::ROLE_USER => 'ลูกค้า',
                    Notification::ROLE_ADMIN => 'ผู้บริหาร',
                ])
                ->required(),
            Forms\Components\TextInput::make('user_id')
                ->label('ระบุ user_id (เว้นว่างถ้าต้องการ broadcast)')
                ->numeric()
                ->nullable(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->sortable()->searchable()->label('หัวข้อ'),
                Tables\Columns\TextColumn::make('role')->label('กลุ่มผู้รับ'),
                Tables\Columns\TextColumn::make('type')->label('ประเภท'),
                Tables\Columns\TextColumn::make('message')->label('รายละเอียด')->limit(70)->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d/m/Y H:i')->sortable()->label('วันที่'),
                Tables\Columns\IconColumn::make('read_at')
                    ->boolean()
                    ->label('อ่านแล้ว'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        Notification::ROLE_ADMIN => 'เฉพาะแอดมิน',
                        Notification::ROLE_USER => 'เฉพาะลูกค้า',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        Notification::TYPE_PAYMENT_OVERDUE_ADMIN_HIGHLIGHT => '❗ แจ้งเตือนค้างจ่ายด่วน (3 วัน+)',
                        Notification::TYPE_PAYMENT_OVERDUE_ADMIN => 'แจ้งเตือนค้างจ่าย',
                        Notification::TYPE_PAYMENT => 'แจ้งเตือนงวดผ่อน',
                        Notification::TYPE_SLIP => 'แจ้งเตือนสลิป',
                        Notification::TYPE_ANNOUNCE => 'ประกาศ',
                        Notification::TYPE_OTHER => 'อื่นๆ',
                    ]),
                Tables\Filters\TernaryFilter::make('read_at')
                    ->label('สถานะการอ่าน')
                    ->trueLabel('อ่านแล้ว')->falseLabel('ยังไม่อ่าน'),
            ])
            ->actions([
                Tables\Actions\Action::make('markAsRead')
                    ->label('อ่านแล้ว')
                    ->visible(fn ($record) => !$record->read_at)
                    ->action(fn ($record) => $record->update(['read_at' => now(), 'is_read' => true])),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_all_read')
                    ->label('อ่านทั้งหมด')
                    ->action(fn ($records) => $records->each(function ($r) {
                        if (!$r->read_at) $r->update(['read_at' => now(), 'is_read' => true]);
                    })),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }
}
