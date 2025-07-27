<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use App\Models\Notification;
use App\Models\Admin;
use App\Notifications\InstallmentDueReminderNotification;
use App\Notifications\PenaltyNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\GoldFetchPrice::class,
        \App\Console\Commands\BackupDatabase::class,
        \App\Console\Commands\CommissionCalculate::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // 1. ดึงราคาทอง
        $schedule->command('gold:fetch-price')->dailyAt('09:00')->onFailure(function () {
            Log::error('[CRON] gold:fetch-price ล้มเหลว');
        });

        // 2. สำรองฐานข้อมูล
        $schedule->command('backup:db')->dailyAt('02:00');

        // 3. แจ้งเตือน "ครบกำหนดงวดวันนี้" (User)
        $schedule->call(function () {
            $today = Carbon::today();
            $due = InstallmentPayment::where('status', 'pending')
                ->whereDate('payment_due_date', '=', $today)
                ->with('installmentRequest.user')
                ->get();

            foreach ($due as $pay) {
                $user = $pay->installmentRequest->user ?? null;
                if (!$user) continue;

                $exists = Notification::where([
                    'user_id' => $user->id,
                    'type' => 'payment_due_today',
                    'message' => $pay->payment_due_date . '-' . $pay->id,
                ])->exists();

                if (!$exists) {
                    Notification::create([
                        'user_id' => $user->id,
                        'role' => 'user',
                        'type' => 'payment_due_today',
                        'title' => 'แจ้งเตือนวันครบกำหนดผ่อนทอง',
                        'message' => 'วันนี้เป็นวันครบกำหนดผ่อนทองของคุณ (' . Carbon::parse($pay->payment_due_date)->format('d/m/Y') . ')',
                        'data' => json_encode(['installment_payment_id' => $pay->id]),
                    ]);
                }
            }
        })->dailyAt('08:00');

        // 4. แจ้งเตือน "ค้างจ่ายทันที" (Admin, ทุกวัน) + "ค้างจ่าย 3 วัน+ (Highlight)"
        $schedule->call(function () {
            $today = Carbon::today();
            $overdue = InstallmentPayment::where('status', 'pending')
                ->where('payment_due_date', '<', $today)
                ->with('installmentRequest.user')
                ->get();

            foreach ($overdue as $pay) {
                $user = $pay->installmentRequest->user ?? null;
                $contract = $pay->installmentRequest ?? null;
                if (!$user || !$contract) continue;

                $days = Carbon::parse($pay->payment_due_date)->diffInDays($today);
                $type = $days >= 3 ? 'payment_overdue_admin_highlight' : 'payment_overdue_admin';
                $title = $days >= 3 ? '❗ ลูกค้าค้างชำระเกิน 3 วัน' : 'แจ้งเตือนค้างจ่าย';
                $message = 'ลูกค้า ' . ($user->first_name . ' ' . $user->last_name ?? '-') .
                    ' (สัญญา: ' . $contract->contract_number . ') ค้างงวด ' .
                    Carbon::parse($pay->payment_due_date)->format('d/m/Y') . ' จำนวน ' .
                    number_format($pay->amount - ($pay->amount_paid ?? 0), 2) . ' บาท ' .
                    ($days >= 3 ? '[ค้างมาแล้ว ' . $days . ' วัน]' : '');

                $exists = Notification::where([
                    'role' => 'admin',
                    'type' => $type,
                    'message' => $pay->payment_due_date . '-' . $pay->id,
                ])->whereDate('created_at', $today)->exists();

                if (!$exists) {
                    Notification::create([
                        'user_id' => null,
                        'role' => 'admin',
                        'type' => $type,
                        'title' => $title,
                        'message' => $message,
                        'data' => json_encode([
                            'user_id' => $user->id,
                            'contract_id' => $contract->id,
                            'payment_id' => $pay->id,
                        ]),
                    ]);
                }
            }
        })->dailyAt('09:10');

        // 5. แจ้งเตือนค้างจ่ายฝั่ง User (สร้าง Notification จริง, mark as read ได้)
        $schedule->call(function () {
            $today = Carbon::today();
            $overdue = InstallmentPayment::where('status', 'pending')
                ->where('payment_due_date', '<', $today)
                ->with('installmentRequest.user')
                ->get();

            foreach ($overdue as $pay) {
                $user = $pay->installmentRequest->user ?? null;
                if (!$user) continue;

                $exists = Notification::where([
                    'user_id' => $user->id,
                    'type' => 'payment_overdue_user',
                    'message' => $pay->payment_due_date . '-' . $pay->id,
                ])->exists();

                if (!$exists) {
                    Notification::create([
                        'user_id' => $user->id,
                        'role' => 'user',
                        'type' => 'payment_overdue_user',
                        'title' => 'แจ้งเตือนค้างชำระ',
                        'message' => 'คุณค้างชำระงวดวันที่ ' . Carbon::parse($pay->payment_due_date)->format('d/m/Y') .
                            ' จำนวน ' . number_format($pay->amount - ($pay->amount_paid ?? 0), 2) . ' บาท',
                        'data' => json_encode(['installment_payment_id' => $pay->id]),
                    ]);
                }
            }
        })->dailyAt('09:05');

        // 6. Job สร้างงวดผ่อนใหม่อัตโนมัติ (ข้ามวันอาทิตย์)
        $schedule->call(function () {
            $today = Carbon::today();
            if ($today->isSunday()) {
                Log::info('[Installment Schedule] ข้ามการสร้างงวดใหม่ เพราะเป็นวันอาทิตย์');
                return;
            }

            $requests = InstallmentRequest::where('status', 'approved')->get();
            foreach ($requests as $req) {
                $latestPayment = $req->installmentPayments()->orderByDesc('payment_due_date')->first();
                $nextDueDate = $latestPayment
                    ? Carbon::parse($latestPayment->payment_due_date)->addDay()
                    : Carbon::parse($req->start_date);

                $existingToday = $req->installmentPayments()
                    ->whereDate('payment_due_date', $today)
                    ->first();

                $paidCount = $req->installmentPayments()->where('status', 'paid')->count();
                $totalPeriod = intval($req->installment_period ?? 0);

                if (!$existingToday && $paidCount < $totalPeriod && $nextDueDate->eq($today)) {
                    InstallmentPayment::create([
                        'installment_request_id' => $req->id,
                        'amount' => $req->daily_payment_amount,
                        'amount_paid' => 0,
                        'status' => 'pending',
                        'payment_status' => 'pending',
                        'payment_due_date' => $today,
                        'admin_notes' => 'สร้างงวดใหม่อัตโนมัติ',
                    ]);
                    Log::info('[Schedule] สร้างงวดผ่อนใหม่สำหรับสัญญา: ' . $req->contract_number . ', วันที่: ' . $today->toDateString());
                }
            }
        })->dailyAt('09:00');

        // 7. Job หัก advance และแจ้งเตือน (ข้ามวันอาทิตย์)
        $schedule->call(function () {
            $today = Carbon::today();
            if ($today->isSunday()) {
                Log::info('[Installment Schedule] ข้ามการหัก advance/แจ้งเตือน เพราะเป็นวันอาทิตย์');
                return;
            }

            $todayStr = $today->format('Y-m-d');
            $requests = InstallmentRequest::where('status', 'approved')->get();

            foreach ($requests as $req) {
                $due = $req->installmentPayments()
                    ->where('payment_due_date', $todayStr)
                    ->where('status', 'pending')->first();

                if ($due && $req->advance_payment >= $due->amount) {
                    $due->amount_paid = $due->amount;
                    $due->status = 'paid';
                    $due->payment_status = 'paid';
                    $due->save();

                    $req->advance_payment -= $due->amount;
                    $req->save();

                    if ($req->user) {
                        $req->user->notify(new InstallmentDueReminderNotification($req));
                    }
                    foreach (Admin::whereIn('role', ['admin', 'OAA'])->get() as $admin) {
                        $admin->notify(new InstallmentDueReminderNotification($req));
                    }
                }
            }
        })->dailyAt('09:00');

        // 8. Job แจ้งเตือน 3 วันล่วงหน้า (ข้ามถ้าวันแจ้งเตือนเป็นอาทิตย์)
        $schedule->call(function () {
            $reminderDate = Carbon::now()->addDays(3)->startOfDay();
            if ($reminderDate->isSunday()) {
                Log::info('[Installment Schedule] ข้ามแจ้งเตือนล่วงหน้า เพราะวันที่ครบกำหนดเป็นวันอาทิตย์');
                return;
            }

            $requests = InstallmentRequest::with('user')
                ->where('status', 'approved')
                ->whereHas('installmentPayments', function ($query) use ($reminderDate) {
                    $query->where('payment_due_date', $reminderDate)
                        ->where('status', 'pending');
                })
                ->get();

            foreach ($requests as $request) {
                if ($request->user) {
                    $request->user->notify(new InstallmentDueReminderNotification($request));
                }
            }
        })->daily();

        // 9. Job สร้าง/แจ้ง penalty ถ้าผ่อนสะสมไม่ครบ (เหมือนเดิม)
        $schedule->call(function () {
            $installments = InstallmentRequest::where('status', 'approved')->get();

            foreach ($installments as $installment) {
                $dailyPayment = $installment->daily_payment_amount;
                $daysPassed = Carbon::parse($installment->start_date)->diffInDays(today()) + 1;
                $totalShouldPay = $dailyPayment * $daysPassed;

                $totalPaid = $installment->installmentPayments()->where('status', 'paid')->sum('amount_paid') + $installment->advance_payment;

                if ($totalPaid < $totalShouldPay) {
                    InstallmentPayment::create([
                        'installment_request_id' => $installment->id,
                        'amount' => 100,
                        'amount_paid' => 0,
                        'status' => 'penalty',
                        'payment_status' => 'pending',
                        'payment_due_date' => today(),
                        'admin_notes' => 'ค่าปรับอัตโนมัติ (ชำระไม่ครบยอดสะสม)',
                    ]);

                    if ($installment->user) {
                        $installment->user->notify(new PenaltyNotification(100));
                    }
                }

                if ($totalPaid > $totalShouldPay) {
                    $installment->advance_payment = $totalPaid - $totalShouldPay;
                } else {
                    $installment->advance_payment = 0;
                }

                if (method_exists($installment, 'updateTotalPenalty')) {
                    $installment->updateTotalPenalty();
                }
                $installment->save();
            }
        })->dailyAt('23:59');

        // 10. คำนวณค่าคอมมิชชั่น (เหมือนเดิม)
        $schedule->command('commission:calculate')->dailyAt('23:59');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
