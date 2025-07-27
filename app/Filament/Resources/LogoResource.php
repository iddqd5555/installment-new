<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogoResource\Pages;
use App\Models\Logo;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class LogoResource extends Resource
{
    protected static ?string $model = Logo::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'ศูนย์ติดตามลูกค้า';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('type')->required()->unique()->label('ประเภทโลโก้ (main/footer/gold ฯลฯ)'),
            Forms\Components\FileUpload::make('image_url')
                ->label('โลโก้')
                ->image()
                ->directory('logos')
                ->maxSize(2048)
                ->required(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')->label('ประเภท'),
                Tables\Columns\ImageColumn::make('image_url')->label('โลโก้'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogos::route('/'),
            'create' => Pages\CreateLogo::route('/create'),
            'edit' => Pages\EditLogo::route('/{record}/edit'),
        ];
    }
}
