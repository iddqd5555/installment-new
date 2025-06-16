<?php

namespace App\Filament\Resources;

use App\Models\BankAccount;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use App\Filament\Resources\BankAccountResource\Pages;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'จัดการบัญชีธนาคาร';
    protected static ?string $navigationGroup = 'การจัดการการเงิน';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('bank_name')
                ->required()
                ->label('ชื่อธนาคาร'),

            Forms\Components\TextInput::make('account_number')
                ->required()
                ->label('เลขบัญชี'),

            Forms\Components\TextInput::make('account_name')
                ->required()
                ->label('ชื่อบัญชี'),

            Forms\Components\FileUpload::make('logo')
                ->required()
                ->directory('bank-logos')
                ->label('โลโก้ธนาคาร'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->label('โลโก้ธนาคาร')->circular(),
                Tables\Columns\TextColumn::make('bank_name')->label('ชื่อธนาคาร'),
                Tables\Columns\TextColumn::make('account_number')->label('เลขที่บัญชี'),
                Tables\Columns\TextColumn::make('account_name')->label('ชื่อบัญชี'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            // เพิ่มตรงนี้ 👇
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankAccounts::route('/'),
            'create' => Pages\CreateBankAccount::route('/create'),
            'edit' => Pages\EditBankAccount::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'การจัดการการเงิน';
    }
}
