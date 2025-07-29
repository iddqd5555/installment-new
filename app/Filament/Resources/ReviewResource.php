<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'ศูนย์ติดตามลูกค้า';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(100)->label('ชื่อรีวิว'),
            Forms\Components\TextInput::make('text')->required()->maxLength(255)->label('ข้อความรีวิว'),
            Forms\Components\FileUpload::make('image_url')
                ->label('รูปภาพ')
                ->image()
                ->directory('reviews')
                ->maxSize(2048)
                ->nullable(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')->label('รูป')->circular(),
                Tables\Columns\TextColumn::make('name')->label('ชื่อ'),
                Tables\Columns\TextColumn::make('text')->label('ข้อความรีวิว')->limit(40),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }

    // ------- PATCH สำคัญสำหรับ shared hosting ที่ symlink ใช้ไม่ได้ ---------
    public static function afterSave($record)
    {
        // ถ้ามีการอัพโหลดรูปใหม่ จะ copy รูปจาก storage/app/public/reviews ไป public/storage/reviews ทันที
        if ($record->image_url) {
            $src = storage_path('app/public/reviews/' . basename($record->image_url));
            $dst = public_path('storage/reviews/' . basename($record->image_url));
            if (file_exists($src)) {
                @mkdir(dirname($dst), 0775, true);
                @copy($src, $dst);
            }
        }
    }
    // ------------------------------------------------------------------------
}
