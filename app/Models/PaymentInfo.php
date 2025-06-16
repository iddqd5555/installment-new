<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentInfo extends Model
{
    use HasFactory;

    protected $fillable = ['bank_account', 'account_number'];

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_name')->label('ชื่อลูกค้า')->searchable(),
                TextColumn::make('amount')->label('จำนวนเงิน')->sortable(),
                TextColumn::make('status')->label('สถานะ')->badge()
                    ->colors([
                        'warning' => 'PENDING',
                        'success' => 'SUCCESS',
                        'danger' => 'FAILED',
                    ]),
                TextColumn::make('created_at')->label('วันที่ทำรายการ')->dateTime(),
            ])
            ->filters([
                Filter::make('status')
                    ->query(fn ($query) => $query->where('status', 'PENDING')),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

}
