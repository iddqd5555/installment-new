<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\InstallmentRequest;
use App\Notifications\InstallmentDueReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $reminderDate = Carbon::now()->addDays(3)->startOfDay();

            $requests = InstallmentRequest::with('user')
                ->where('status', 'approved')
                ->whereHas('payments', function ($query) use ($reminderDate) {
                    $query->where('payment_due_date', $reminderDate)
                        ->where('status', 'pending');
                })
                ->get();

            foreach ($requests as $request) {
                $request->user->notify(new InstallmentDueReminderNotification($request));
            }
        })->daily();  // เปลี่ยนกลับจาก everyMinute() เป็น daily() เมื่อใช้งานจริง
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
