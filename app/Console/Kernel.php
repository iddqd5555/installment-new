<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use App\Notifications\InstallmentDueReminderNotification;
use App\Notifications\PenaltyNotification; // ✅ เพิ่มตรงนี้ชัดเจน
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // ✅ แจ้งเตือนก่อนครบกำหนดชำระ (ไม่ต้องแก้ไข)
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

        // ✅ ดึงราคาทองคำ (ไม่ต้องแก้ไข)
        $schedule->call(function () {
            try {
                $response = Http::get('https://www.goldtraders.or.th/default.aspx?tabid=93&language=th-TH');
                preg_match_all('/<span id="DetailPlace_uc_goldprices1_lblBLBuy">([^<]+)<\/span>/', $response, $buyMatches);
                preg_match_all('/<span id="DetailPlace_uc_goldprices1_lblBLSell">([^<]+)<\/span>/', $response, $sellMatches);

                $buyPrice = isset($buyMatches[1][0]) ? floatval(str_replace(',', '', $buyMatches[1][0])) : null;
                $sellPrice = isset($sellMatches[1][0]) ? floatval(str_replace(',', '', $sellMatches[1][0])) : null;

                if ($buyPrice && $sellPrice) {
                    DB::table('daily_gold_prices')->updateOrInsert(
                        ['date' => Carbon::today()->toDateString()],
                        ['buy' => $buyPrice, 'sell' => $sellPrice, 'updated_at' => Carbon::now()]
                    );
                    Log::info('ดึงข้อมูลราคาทองคำสำเร็จ');
                } else {
                    Log::error('ดึงข้อมูลราคาทองคำล้มเหลว');
                }
            } catch (\Exception $e) {
                Log::error('ข้อผิดพลาดในการดึงราคาทองคำ: '.$e->getMessage());
            }
        })->dailyAt('09:00');

        // 🔥 Scheduler คำนวณค่าปรับอัตโนมัติและจัดการ advance payment
        $schedule->call(function () {
            $installments = InstallmentRequest::where('status', 'approved')->get();

            foreach ($installments as $installment) {
                $dailyPayment = $installment->daily_payment_amount;
                $daysPassed = Carbon::parse($installment->start_date)->diffInDays(today()) + 1;
                $totalShouldPay = $dailyPayment * $daysPassed;

                $totalPaid = $installment->payments()
                    ->where('status', 'approved')
                    ->sum('amount_paid') + $installment->advance_payment;

                if ($totalPaid < $totalShouldPay) {
                    // สร้างรายการค่าปรับชัดเจน
                    InstallmentPayment::create([
                        'installment_request_id' => $installment->id,
                        'amount' => 100,
                        'amount_paid' => 0,
                        'status' => 'penalty',
                        'payment_status' => 'pending',
                        'payment_due_date' => today(),
                        'admin_notes' => 'ค่าปรับอัตโนมัติ (ชำระไม่ครบยอดสะสม)',
                    ]);

                    // ✅ เพิ่ม notification แจ้งเตือน penalty ให้ผู้ใช้งานทันที
                    $installment->user->notify(new PenaltyNotification(100));
                }

                if ($totalPaid > $totalShouldPay) {
                    $installment->advance_payment = $totalPaid - $totalShouldPay;
                } else {
                    $installment->advance_payment = 0;
                }

                $installment->updateTotalPenalty();
                $installment->save();
            }
        })->dailyAt('23:59');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
