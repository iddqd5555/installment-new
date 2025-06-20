<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\InstallmentRequest;
use App\Notifications\InstallmentDueReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // ระบบแจ้งเตือนเดิม (ไม่แก้ไข)
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
        })->daily();

        // ✅ Scheduler ดึงราคาทองคำจาก goldtraders.or.th (API เดียวกับ Controller)
        $schedule->call(function () {
            try {
                $response = Http::get('https://www.goldtraders.or.th/default.aspx?tabid=93&language=th-TH');
                preg_match_all('/<span id="DetailPlace_uc_goldprices1_lblBLBuy">([^<]+)<\/span>/', $response, $buyMatches);
                preg_match_all('/<span id="DetailPlace_uc_goldprices1_lblBLSell">([^<]+)<\/span>/', $response, $sellMatches);

                $buyPrice = isset($buyMatches[1][0]) ? floatval(str_replace(',', '', $buyMatches[1][0])) : null;
                $sellPrice = isset($sellMatches[1][0]) ? floatval(str_replace(',', '', $sellMatches[1][0])) : null;

                if ($buyPrice && $sellPrice) {
                    DB::table('daily_gold_prices')->updateOrInsert(
                        ['date' => Carbon::now()->toDateString()],
                        [
                            'buy' => $buyPrice,
                            'sell' => $sellPrice,
                            'updated_at' => Carbon::now()
                        ]
                    );
                    Log::info('ดึงข้อมูลราคาทองคำจาก goldtraders.or.th สำเร็จ');
                } else {
                    Log::error('ไม่พบข้อมูลราคาทองคำจาก goldtraders.or.th');
                }
            } catch (\Exception $e) {
                Log::error('ข้อผิดพลาดในการดึงข้อมูลราคาทองคำ: ' . $e->getMessage());
            }
        })->dailyAt('09:00');
    }

    $schedule->call(function () {
        $installments = InstallmentRequest::with('payments')
            ->where('status', 'approved')
            ->get();

        foreach ($installments as $installment) {
            $todayPayment = $installment->daily_payment_amount;
            $paidToday = $installment->payments()
                            ->whereDate('payment_due_date', now()->toDateString())
                            ->where('status', 'approved')
                            ->sum('amount_paid');

            if ($paidToday < $todayPayment) {
                $penaltyAmount = ($todayPayment - $paidToday) * ($installment->penalty_rate / 100);
                InstallmentPayment::create([
                    'installment_request_id' => $installment->id,
                    'amount_paid' => 0,
                    'status' => 'penalty',
                    'payment_due_date' => now(),
                    'penalty_amount' => $penaltyAmount,
                ]);
            }
        }
    })->dailyAt('23:59');

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
