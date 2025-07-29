<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserTrackingResource\Pages;
use App\Models\User;
use App\Models\UserLocationLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserTrackingResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'ศูนย์ติดตามลูกค้า';
    protected static ?string $slug = 'user-trackings';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                // กรองลูกค้าที่อนุมัติโดยแอดมินคนนี้ และมีงวดค้างจ่าย
                $today = now()->toDateString();
                $admin = auth()->user();

                if ($admin) {
                    $query->whereHas('installmentRequests', function($q) use ($admin, $today) {
                        $q->where('status', 'approved')
                          ->where('approved_by', $admin->id)
                          ->whereHas('installmentPayments', function($p) use ($today) {
                              $p->where('status', 'pending')
                                ->whereDate('payment_due_date', '<=', $today);
                          });
                    });
                } else {
                    $query->whereHas('installmentRequests.installmentPayments', function($q) use ($today) {
                        $q->where('status', 'pending')
                          ->whereDate('payment_due_date', '<=', $today);
                    });
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label('ชื่อ')->searchable(),
                Tables\Columns\TextColumn::make('last_name')->label('นามสกุล')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('เบอร์โทร')->copyable(),

                Tables\Columns\TextColumn::make('overdue_count')
                    ->label('จำนวนงวดค้าง')
                    ->getStateUsing(function ($record) {
                        $admin = auth()->user();
                        $installments = $record->installmentRequests()
                            ->where('status', 'approved')
                            ->when($admin, function ($q) use ($admin) {
                                $q->where('approved_by', $admin->id);
                            })
                            ->get();

                        $pendingCount = 0;
                        foreach ($installments as $req) {
                            $pendingCount += $req->installmentPayments()
                                ->where('status', 'pending')
                                ->whereDate('payment_due_date', '<=', now())
                                ->count();
                        }
                        return $pendingCount > 0 ? $pendingCount . ' งวด' : '-';
                    }),

                Tables\Columns\TextColumn::make('overdue_total')
                    ->label('ยอดค้างรวม (บาท)')
                    ->getStateUsing(function ($record) {
                        $admin = auth()->user();
                        $installments = $record->installmentRequests()
                            ->where('status', 'approved')
                            ->when($admin, function ($q) use ($admin) {
                                $q->where('approved_by', $admin->id);
                            })
                            ->get();

                        $total = 0;
                        foreach ($installments as $req) {
                            $total += $req->installmentPayments()
                                ->where('status', 'pending')
                                ->whereDate('payment_due_date', '<=', now())
                                ->sum('amount');
                        }
                        return $total > 0 ? number_format($total, 2) : '-';
                    }),

                Tables\Columns\TextColumn::make('overdue_payment_due_date')
                    ->label('วันที่ค้างงวดล่าสุด')
                    ->getStateUsing(function ($record) {
                        $admin = auth()->user();
                        $pay = $record->installmentRequests()
                            ->where('status', 'approved')
                            ->when($admin, function ($q) use ($admin) {
                                $q->where('approved_by', $admin->id);
                            })
                            ->get()
                            ->flatMap(function ($r) {
                                return $r->installmentPayments()
                                    ->where('status', 'pending')
                                    ->whereDate('payment_due_date', '<=', now())
                                    ->orderBy('payment_due_date')
                                    ->get();
                            })
                            ->sortBy('payment_due_date')
                            ->first();

                        return $pay?->payment_due_date
                            ? \Carbon\Carbon::parse($pay->payment_due_date)->format('d/m/Y')
                            : '-';
                    }),

                Tables\Columns\TextColumn::make('last_location')
                    ->label('พิกัดล่าสุด')
                    ->getStateUsing(function ($record) {
                        $log = $record->userLocationLogs()->orderByDesc('created_at')->first();
                        if (!$log) return '-';
                        return $log->latitude && $log->longitude
                            ? "{$log->latitude}, {$log->longitude}"
                            : '-';
                    })
                    ->copyable(),

                Tables\Columns\TextColumn::make('map')
                    ->label('Map')
                    ->getStateUsing(function ($record) {
                        $log = $record->userLocationLogs()->orderByDesc('created_at')->first();
                        if ($log && $log->latitude && $log->longitude) {
                            $url = "https://maps.google.com/?q={$log->latitude},{$log->longitude}";
                            return "<a href=\"$url\" target=\"_blank\">ดูบนแผนที่</a>";
                        }
                        return '-';
                    })
                    ->html(),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('เวลา GPS ล่าสุด')
                    ->getStateUsing(function ($record) {
                        $log = $record->userLocationLogs()->orderByDesc('created_at')->first();
                        return $log?->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-';
                    })
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('ดูประวัติ GPS'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUserTrackings::route('/'),
            'create' => Pages\CreateUserTracking::route('/create'),
            'edit'   => Pages\EditUserTracking::route('/{record}/edit'),
            'view'   => Pages\ViewUserTracking::route('/{record}'),
        ];
    }
}
