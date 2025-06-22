<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('first_name')->label('ชื่อจริง')->required(),
            TextInput::make('last_name')->label('นามสกุล')->required(),
            TextInput::make('phone')->label('เบอร์โทร')->required(),
            TextInput::make('password')
            ->label('รหัสผ่าน')
            ->password()
            ->required(),
            TextInput::make('email')
            ->label('อีเมล')
            ->email()
            ->nullable(), // ✅ ระบุชัดเจนว่า email เป็น null ได้

            TextInput::make('id_card_number')->label('เลขบัตรประชาชน')->required(),
            Select::make('identity_verification_status')->label('สถานะการยืนยันตัวตน')->options([
                'pending' => 'รอการตรวจสอบ',
                'verified' => 'ตรวจสอบแล้ว',
                'rejected' => 'ปฏิเสธ',
            ])->default('pending'),
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('ชื่อจริง')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('last_name')
                    ->label('นามสกุล')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('เบอร์โทร')
                    ->searchable(),

                Tables\Columns\TextColumn::make('id_card_number')
                    ->label('เลขบัตรประชาชน')
                    ->searchable(),

                Tables\Columns\TextColumn::make('identity_verification_status')
                    ->label('สถานะการยืนยันตัวตน')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'verified',
                        'danger' => 'rejected',
                    ]),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    public static function getNavigationGroup(): ?string
    {
        return 'การจัดการสมาชิก';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = Hash::make($data['password']);

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

    public static function getEloquentQuery(): Builder
    {
        $admin = Auth::guard('admin')->user();

        if (in_array($admin->role, ['admin', 'OAA'])) {
            return parent::getEloquentQuery();
        }

        // staff จะเห็นเฉพาะ User ที่ตนเองอนุมัติคำขอผ่อนทอง
        return parent::getEloquentQuery()
            ->whereHas('installmentRequests', function ($query) use ($admin) {
                $query->where('approved_by', $admin->id);
            });
    }
}
