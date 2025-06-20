<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstallmentRequestResource\Pages;
use App\Filament\Resources\InstallmentRequestResource\Widgets\InstallmentSummaryWidget;
use App\Models\InstallmentRequest;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;

class InstallmentRequestResource extends Resource
{
    protected static ?string $model = InstallmentRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'คำขอผ่อนทอง';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('user.first_name')
                    ->label('ชื่อ')
                    ->sortable(query: function ($query, $direction) {
                        return $query->join('users', 'installment_requests.user_id', '=', 'users.id')
                                    ->orderBy('users.first_name', $direction);
                    }),
                TextColumn::make('user.last_name')
                    ->label('นามสกุล')
                    ->sortable(query: function ($query, $direction) {
                        return $query->join('users', 'installment_requests.user_id', '=', 'users.id')
                                    ->orderBy('users.last_name', $direction);
                    }),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('เบอร์โทรศัพท์'),

                Tables\Columns\TextColumn::make('gold_amount')
                    ->label('จำนวนทอง (บาท)'),

                Tables\Columns\TextColumn::make('approved_gold_price')
                    ->label('ราคาทองอนุมัติ'),

                Tables\Columns\TextColumn::make('installment_period')
                    ->label('จำนวนวัน'),

                Tables\Columns\TextColumn::make('daily_payment_amount')->label('ยอดชำระรายวัน'),
                Tables\Columns\TextColumn::make('penalty_amount')->label('ค่าปรับรายวัน'),

                Tables\Columns\TextColumn::make('status')
                    ->label('สถานะ')->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                // ✅ เพิ่ม Approve Action
                Tables\Actions\Action::make('approve')
                    ->label('อนุมัติ')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'approved']);

                        Notification::make()
                            ->title('อนุมัติคำขอเรียบร้อยแล้วค่ะ')
                            ->success()
                            ->send();
                    })
                    ->hidden(fn($record) => $record->status === 'approved'),

                // ✅ เพิ่ม Reject Action
                Tables\Actions\Action::make('reject')
                    ->label('ปฏิเสธ')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'rejected']);

                        Notification::make()
                            ->title('ปฏิเสธคำขอเรียบร้อยแล้วค่ะ')
                            ->danger()
                            ->send();
                    })
                    ->hidden(fn($record) => $record->status === 'rejected'),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'name')
                ->label('สมาชิก')
                ->searchable(),

            Forms\Components\TextInput::make('fullname')
                ->label('ชื่อเต็ม')
                ->required(),

            Forms\Components\TextInput::make('phone')
                ->label('เบอร์โทร')
                ->required(),

            Forms\Components\TextInput::make('id_card')
                ->label('เลขบัตรประชาชน')
                ->required(),

            Forms\Components\TextInput::make('gold_amount')
                ->label('จำนวนทอง (บาท)')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('approved_gold_price')
                ->label('ราคาทองที่อนุมัติ')
                ->numeric()
                ->required()
                ->reactive(),

            Forms\Components\Select::make('installment_period')
                ->label('จำนวนวัน')
                ->options([
                    30 => '30 วัน',
                    45 => '45 วัน',
                    60 => '60 วัน'
                ])
                ->required()
                ->reactive(),

            Forms\Components\TextInput::make('daily_payment_amount')->numeric()->label('ยอดชำระรายวัน'),
            Forms\Components\TextInput::make('penalty_amount')->numeric()->label('ค่าปรับรายวัน'),

            Forms\Components\Select::make('status')
                ->label('สถานะ')
                ->options([
                    'pending' => 'รออนุมัติ',
                    'approved' => 'อนุมัติแล้ว',
                    'rejected' => 'ปฏิเสธ'
                ])
                ->required(),

            // ✅ เพิ่มช่องคำนวณยอดรวมอัตโนมัติ (ตามสูตรคำนวณของคุณ)
            Forms\Components\TextInput::make('total_installment_amount')
                ->label('ยอดชำระรวม')
                ->numeric()
                ->readOnly()
                ->dehydrated()
                ->afterStateUpdated(function ($set, $get) {
                    if ($get('approved_gold_price') && $get('installment_period')) {
                        $result = (new InstallmentRequest)->calculateInstallment(
                            $get('approved_gold_price'),
                            $get('installment_period')
                        );
                        $set('total_installment_amount', $result['total_price']);
                    }
                }),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallmentRequests::route('/'),
            'create' => Pages\CreateInstallmentRequest::route('/create'),
            'edit' => Pages\EditInstallmentRequest::route('/{record}/edit'),
        ];
    }

    public static function getHeaderWidgets(): array
    {
        return [
            InstallmentSummaryWidget::class,
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'การจัดการรายการผ่อน';
    }
}
