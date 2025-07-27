<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('file')
                ->label('ไฟล์เอกสาร')
                ->directory('user-documents')
                ->disk('public')
                ->preserveFilenames()
                ->maxSize(5120)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                // ✅ Preview ไฟล์ (ถ้าเป็นรูป จะโชว์ thumbnail, ถ้าไม่ใช่จะโชว์ไอคอน)
                ImageColumn::make('file_url')
                    ->label('ไฟล์')
                    ->disk('public')
                    ->height(80)
                    ->width(80)
                    ->circular()
                    ->defaultImageUrl(url('/assets/img/no-image.png')), // เปลี่ยนเป็น path รูปเปล่าได้
                Tables\Columns\TextColumn::make('file')
                    ->label('ชื่อไฟล์')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('วันที่อัปโหลด')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => $record->file_url ? asset($record->file_url) : null)
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
