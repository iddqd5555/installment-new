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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\BulkAction;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;


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

                TextColumn::make('user.phone')
                    ->label('เบอร์โทรศัพท์'),

                TextColumn::make('gold_amount')
                    ->label('จำนวนทอง (บาท)'),

                TextColumn::make('approved_gold_price')
                    ->label('ราคาทองอนุมัติ'),

                TextColumn::make('installment_period')
                    ->label('จำนวนวัน'),

                TextColumn::make('daily_payment_amount')->label('ยอดชำระรายวัน'),

                TextColumn::make('penalty_amount')->label('ค่าปรับรายวัน'),

                Tables\Columns\TextColumn::make('total_penalty')
                    ->label('ค่าปรับสะสม')
                    ->money('THB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('first_approved_date')
                    ->label('วันที่อนุมัติครั้งแรก')
                    ->date('d/m/Y')
                    ->sortable(),


                TextColumn::make('status')
                    ->label('สถานะ')->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                // ✅ วางโค้ดตรงนี้เป็น column สุดท้าย
                Tables\Columns\TextColumn::make('approvedBy.username')
                    ->label('ผู้อนุมัติ')
                    ->visible(fn () => in_array(Auth::guard('admin')->user()->role, ['admin', 'OAA'])),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('อนุมัติ')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $adminId = Auth::guard('admin')->id();

                        if ($record->approved_by === null || $record->approved_by === $adminId) {
                            $record->update([
                                'status' => 'approved',
                                'approved_by' => $adminId
                            ]);

                            Notification::make()
                                ->title('อนุมัติคำขอเรียบร้อยแล้วค่ะ')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('ไม่สามารถอนุมัติได้')
                                ->body('คำขอนี้มีพนักงานคนอื่นอนุมัติแล้ว')
                                ->danger()
                                ->send();
                        }
                    })
                    ->hidden(fn($record) => $record->status === 'approved'),

                Tables\Actions\Action::make('reject')
                    ->label('ปฏิเสธ')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $adminId = Auth::guard('admin')->id();

                        if ($record->approved_by === null || $record->approved_by === $adminId) {
                            $record->update([
                                'status' => 'rejected',
                                'approved_by' => $adminId
                            ]);

                            Notification::make()
                                ->title('ปฏิเสธคำขอเรียบร้อยแล้วค่ะ')
                                ->danger()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('ไม่สามารถปฏิเสธได้')
                                ->body('คำขอนี้มีพนักงานคนอื่นอนุมัติหรือปฏิเสธแล้ว')
                                ->danger()
                                ->send();
                        }
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
                ->relationship('user', 'id')
                ->label('ID User')
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, $set) {
                    $user = \App\Models\User::find($state);
                    if ($user) {
                        $set('fullname', $user->first_name . ' ' . $user->last_name);
                        $set('phone', $user->phone);
                        $set('id_card', $user->id_card_number);
                    }
                }),

            Forms\Components\TextInput::make('fullname')
                ->label('ชื่อ-นามสกุล')
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
                ->required()
                ->reactive()
                ->afterStateUpdated(fn ($set, $get) => self::calculateAmounts($set, $get)),

            Forms\Components\TextInput::make('approved_gold_price')
                ->label('ราคาทองที่อนุมัติ')
                ->numeric()
                ->required()
                ->reactive()
                ->afterStateUpdated(fn ($set, $get) => self::calculateAmounts($set, $get)),

            Forms\Components\Select::make('installment_period')
                ->label('จำนวนวัน')
                ->options([
                    30 => '30 วัน',
                    45 => '45 วัน',
                    60 => '60 วัน'
                ])
                ->required()
                ->reactive()
                ->afterStateUpdated(fn ($set, $get) => self::calculateAmounts($set, $get)),

            Forms\Components\TextInput::make('total_gold_price')
                ->label('ราคารวมทองคำ (จำนวนทอง × ราคาทอง)')
                ->numeric()
                ->readOnly()
                ->dehydrated(),

            Forms\Components\TextInput::make('interest_rate')
                ->label('ดอกเบี้ย (%)')
                ->numeric()
                ->readOnly()
                ->dehydrated(),

            Forms\Components\TextInput::make('interest_amount')
                ->label('ดอกเบี้ย (บาท) (ยอดรวมพร้อมดอกเบี้ย - ราคารวมทองคำ)')
                ->numeric()
                ->readOnly()
                ->dehydrated(),

            Forms\Components\TextInput::make('total_with_interest')
                ->label('ยอดชำระรวมพร้อมดอกเบี้ย (ราคารวมทองคำ × อัตราดอกเบี้ย)')
                ->numeric()
                ->readOnly()
                ->dehydrated(),

            Forms\Components\TextInput::make('daily_payment_amount')
                ->label('ยอดชำระรายวัน (ยอดรวม ÷ จำนวนวัน)')
                ->numeric()
                ->required()
                ->readOnly()
                ->dehydrated(),

            Forms\Components\TextInput::make('penalty_amount')
                ->label('ค่าปรับรายวัน')
                ->numeric()
                ->default(0),

            Forms\Components\Select::make('status')
                ->label('สถานะ')
                ->options([
                    'pending' => 'รออนุมัติ',
                    'approved' => 'อนุมัติแล้ว',
                    'rejected' => 'ปฏิเสธ'
                ])
                ->required(),
        ]);
    }

    // สร้าง method คำนวณไว้ใช้ในทุก afterStateUpdated
    protected static function calculateAmounts($set, $get)
    {
        $goldAmount = (float) $get('gold_amount');
        $goldPrice = (float) $get('approved_gold_price');
        $period = (int) $get('installment_period');

        if ($goldAmount > 0 && $goldPrice > 0 && $period > 0) {
            $totalGoldPrice = round($goldAmount * $goldPrice, 2);

            $rates = [30 => 1.27, 45 => 1.45, 60 => 1.66];
            $interestRate = $rates[$period] ?? 1;

            $totalWithInterest = round($totalGoldPrice * $interestRate, 2);
            $interestAmount = round($totalWithInterest - $totalGoldPrice, 2);
            $dailyPayment = round($totalWithInterest / $period, 2);

            $set('total_gold_price', $totalGoldPrice);
            $set('interest_rate', ($interestRate - 1) * 100);
            $set('interest_amount', $interestAmount);
            $set('total_with_interest', $totalWithInterest);
            $set('daily_payment_amount', $dailyPayment);
        }
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

    public static function getEloquentQuery(): Builder
    {
        $admin = Auth::guard('admin')->user();

        if (in_array($admin->role, ['admin', 'OAA'])) {
            // Admin และ OAA จะเห็นรายการที่ยังไม่อนุมัติทั้งหมด
            return parent::getEloquentQuery()->where('status', 'pending');
        }

        // สำหรับ staff เห็นเฉพาะที่ยังไม่มีคนรับผิดชอบ หรือที่ตัวเองรับผิดชอบแล้วเท่านั้น
        return parent::getEloquentQuery()
            ->where('status', 'pending')
            ->where(function ($query) use ($admin) {
                $query->whereNull('approved_by')
                    ->orWhere('approved_by', $admin->id);
            })
            ->whereDoesntHave('user.installmentRequests', function ($query) use ($admin) {
                $query->where('approved_by', '!=', $admin->id)
                    ->whereNotNull('approved_by');
            });
    }

    protected static function afterFill($record, $set): void
    {
        if ($record) {
            $totalGoldPrice = $record->gold_amount * $record->approved_gold_price;

            $rates = [30 => 1.27, 45 => 1.45, 60 => 1.66];
            $interestRate = $rates[$record->installment_period] ?? 1;
            $totalWithInterest = round($totalGoldPrice * $interestRate, 2);

            // คำนวณดอกเบี้ย (บาท)
            $interestAmount = round($totalWithInterest - $totalGoldPrice, 2);

            $set('total_gold_price', $totalGoldPrice);
            $set('total_with_interest', $totalWithInterest);
            $set('interest_rate', ($interestRate - 1) * 100); // ดอกเบี้ย (%)
            $set('interest_amount', $interestAmount); // ดอกเบี้ย (บาท)
            $set('daily_payment_amount', round($totalWithInterest / $record->installment_period, 2));
        }
    }

}
