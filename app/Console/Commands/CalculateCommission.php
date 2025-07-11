<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use App\Models\InstallmentPayment;
use App\Models\Commission;
use Carbon\Carbon;

class CalculateCommission extends Command
{
    protected $signature = 'commission:calculate';
    protected $description = 'คำนวณค่าคอมมิชชันให้พนักงาน';

    public function handle()
    {
        $today = Carbon::today()->format('Y-m-d');
        $staffs = Admin::where('role', 'staff')->get();

        foreach ($staffs as $staff) {
            $totalCollected = InstallmentPayment::whereHas('installmentRequest', function ($q) use ($staff) {
                    $q->where('responsible_staff', $staff->username);
                })
                ->where('payment_status', 'paid')
                ->whereDate('payment_due_date', $today)
                ->sum('amount_paid');

            if ($totalCollected > 0) {
                $rate = 3; // ค่าคอม 3% (เปลี่ยนได้)
                $commissionAmount = ($totalCollected * $rate) / 100;

                Commission::create([
                    'admin_id' => $staff->id,
                    'total_collected' => $totalCollected,
                    'commission_rate' => $rate,
                    'commission_amount' => $commissionAmount,
                    'calculation_date' => $today,
                ]);

                $this->info("ค่าคอมมิชชันของ {$staff->username}: ฿{$commissionAmount}");
            }
        }

        $this->info('คำนวณค่าคอมมิชชันสำเร็จ');
    }
}
