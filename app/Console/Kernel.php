<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     *
     * @var array
     */
    protected $commands = [
        // คุณสามารถเพิ่ม Command ที่นี่
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Schedule แจ้งเตือนชำระเงินอัตโนมัติ
        $schedule->call(function () {
            (new \App\Http\Controllers\Admin\InstallmentAdminController())->notifyDuePayments();
        })->dailyAt('09:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
