<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminResource\Pages;
use App\Models\Admin;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('prefix')->required(),
                Forms\Components\TextInput::make('username')->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->hiddenOn('edit'),
                Forms\Components\Select::make('role')
                    ->options([
                        'staff' => 'พนักงาน',
                        'admin' => 'แอดมิน',
                        'OAA' => 'One Above All',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('prefix')->label('คำนำหน้า'),
                Tables\Columns\TextColumn::make('username')->label('ชื่อผู้ใช้'),
                Tables\Columns\BadgeColumn::make('role')
                    ->label('บทบาท')
                    ->colors([
                        'primary' => 'staff',
                        'success' => 'admin',
                        'danger' => 'OAA',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->label('วันที่สร้าง')->dateTime(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(Auth::guard('admin')->user()->role, ['admin', 'OAA']);
    }

    public static function canCreate(): bool
    {
        return in_array(Auth::guard('admin')->user()->role, ['admin', 'OAA']);
    }

    public static function canEdit($record): bool
    {
        $admin = Auth::guard('admin')->user();
        return ($record->role !== 'OAA' || $admin->role === 'OAA');
    }

    public static function canDelete($record): bool
    {
        $admin = Auth::guard('admin')->user();
        return ($record->role !== 'OAA' || $admin->role === 'OAA');
    }
}
