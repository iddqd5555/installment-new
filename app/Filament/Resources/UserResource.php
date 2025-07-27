<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;


class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'การจัดการสมาชิก';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // 🟢 ข้อมูลพื้นฐาน
            TextInput::make('first_name')->label('ชื่อจริง')->required(),
            TextInput::make('last_name')->label('นามสกุล')->required(),
            TextInput::make('nickname')->label('ชื่อเล่น'),
            TextInput::make('phone')->label('เบอร์โทร')->required(),
            TextInput::make('email')->label('อีเมล')->email()->nullable(),
            TextInput::make('password')->label('รหัสผ่าน')->password()->required(),

            TextInput::make('id_card_number')->label('เลขบัตรประชาชน')->required(),
            Select::make('gender')->label('เพศ')->options([
                'ชาย' => 'ชาย',
                'หญิง' => 'หญิง',
                'ไม่ระบุ' => 'ไม่ระบุ',
            ]),
            DatePicker::make('date_of_birth')->label('วันเดือนปีเกิด'),

            // 🟢 สถานะ
            Select::make('marital_status')->label('สถานะภาพ')->options([
                'โสด' => 'โสด',
                'แต่งงาน' => 'แต่งงาน',
                'หม้าย' => 'หม้าย',
                'หย่าร้าง' => 'หย่าร้าง',
            ]),
            TextInput::make('relationship_with_buyer')->label('ความสัมพันธ์กับผู้ซื้อ'),
            TextInput::make('house_number')->label('บ้านเลขที่'),
            Textarea::make('address')->label('ที่อยู่'),

            TextInput::make('line_id')->label('ไอดีไลน์'),
            TextInput::make('facebook')->label('เฟสบุ๊ค'),

            // 🟢 อาชีพและที่ทำงาน
            TextInput::make('occupation')->label('ประกอบอาชีพ'),
            TextInput::make('position')->label('ตำแหน่งงาน'),
            TextInput::make('workplace')->label('สถานที่ทำงาน'),
            Textarea::make('workplace_address')->label('ที่อยู่สถานที่ทำงาน'),
            TextInput::make('work_phone')->label('เบอร์ติดต่อที่ทำงาน'),
            TextInput::make('work_duration')->label('อายุงาน'),
            TextInput::make('salary')->label('รายได้ต่อเดือน'),
            TextInput::make('daily_income')->label('รายได้ต่อวัน'),
            TextInput::make('daily_balance')->label('ยอดคงเหลือต่อวัน'),

            // 🟢 ข้อมูลคู่สมรส/แฟน
            TextInput::make('spouse_name')->label('ชื่อสามี/ภรรยา'),
            TextInput::make('spouse_phone')->label('เบอร์โทรสามี/ภรรยา'),
            TextInput::make('partner_name')->label('ชื่อคู่สมรส/แฟน'),
            TextInput::make('partner_phone')->label('เบอร์โทรคู่สมรส/แฟน'),
            TextInput::make('partner_occupation')->label('อาชีพคู่สมรส/แฟน'),
            TextInput::make('partner_salary')->label('รายได้คู่สมรส/แฟน'),

            // 🟢 ญาติฉุกเฉิน
            TextInput::make('emergency_contact_name_1')->label('ชื่อญาติฉุกเฉิน 1'),
            TextInput::make('emergency_contact_relation_1')->label('ความสัมพันธ์ 1'),
            Textarea::make('emergency_contact_address_1')->label('ที่อยู่ 1'),
            TextInput::make('emergency_contact_phone_1')->label('เบอร์โทร 1'),

            TextInput::make('emergency_contact_name_2')->label('ชื่อญาติฉุกเฉิน 2'),
            TextInput::make('emergency_contact_relation_2')->label('ความสัมพันธ์ 2'),
            Textarea::make('emergency_contact_address_2')->label('ที่อยู่ 2'),
            TextInput::make('emergency_contact_phone_2')->label('เบอร์โทร 2'),

            // 🟢 ประเภทที่อยู่อาศัย
            Select::make('residence_status')->label('ที่อยู่อาศัย')->options([
                'เจ้าบ้าน' => 'เจ้าบ้าน',
                'บ้านเช่า' => 'บ้านเช่า',
                'บ้านญาติ' => 'บ้านญาติ',
                'บ้านพักสวัสดิการ' => 'บ้านพักสวัสดิการ'
            ]),

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
                Tables\Columns\TextColumn::make('first_name')->label('ชื่อจริง')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('last_name')->label('นามสกุล')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('เบอร์โทร')->searchable(),
                Tables\Columns\TextColumn::make('id_card_number')->label('เลขบัตรประชาชน')->searchable(),
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
            \App\Filament\Resources\UserResource\RelationManagers\DocumentsRelationManager::class,
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
