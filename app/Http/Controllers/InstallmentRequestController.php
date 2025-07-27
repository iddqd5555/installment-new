<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Admin;
use App\Notifications\InstallmentDueReminderNotification;
use Illuminate\Support\Facades\DB;

class InstallmentRequestController extends Controller
{
    public function goldapi()
    {
        // ดึงราคาทองจาก DB เท่านั้น
        $gold = DB::table('daily_gold_prices')->where('date', Carbon::today()->toDateString())->first();
        if (!$gold) {
            $gold = DB::table('daily_gold_prices')->orderByDesc('date')->first();
        }
        $goldPrices = $gold ? [
            'ornament_sell'     => number_format($gold->sell, 2),
            'ornament_buy'      => number_format($gold->buy, 2),
            'ornament_buy_gram' => isset($gold->buy_gram) ? number_format($gold->buy_gram, 2) : 'n/a',
            'date'              => $gold->date,
        ] : null;

        return view('gold_guest', compact('goldPrices'));
    }

    public function submitGoldGuest(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'id_card' => 'required|string|max:13',
            'gold_amount' => 'required|numeric|min:0.01|max:10000',
            'installment_period' => 'required|in:30,45,60',
            'referrer_code' => 'nullable|string|max:32',
        ]);

        // ดึงราคาทองจาก DB เท่านั้น
        $gold = DB::table('daily_gold_prices')->where('date', Carbon::today()->toDateString())->first();
        if (!$gold) {
            $gold = DB::table('daily_gold_prices')->orderByDesc('date')->first();
        }
        $approvedGoldPrice = $gold ? $gold->sell : 0;

        $rates = [30 => 1.27, 45 => 1.45, 60 => 1.66];
        $period = $request->installment_period;
        $gold_price = $approvedGoldPrice * $request->gold_amount;
        $totalWithInterest = round($gold_price * $rates[$period], 2);
        $dailyPayment = round($totalWithInterest / $period, 2);

        InstallmentRequest::create([
            'fullname' => $request->fullname,
            'id_card' => $request->id_card,
            'phone' => $request->phone,
            'referrer_code' => $request->referrer_code,
            'gold_amount' => $request->gold_amount,
            'installment_period' => $period,
            'approved_gold_price' => $approvedGoldPrice,
            'total_gold_price' => $gold_price,
            'total_with_interest' => $totalWithInterest,
            'daily_payment_amount' => $dailyPayment,
            'status' => 'pending',
            'interest_rate' => ($rates[$period] - 1) * 100,
            'next_payment_date' => now()->addDays(1),
            'user_id' => null,
            'is_guest' => 1,
        ]);
        return redirect()->back()->with('success', 'ส่งคำขอผ่อนทองเรียบร้อยแล้วค่ะ');
    }

    public function submitGoldMember(Request $request)
    {
        $request->validate([
            'gold_amount' => 'required|numeric|min:0.01|max:10000',
            'installment_period' => 'required|in:30,45,60',
            'referrer_code' => 'nullable|string|max:32',
        ]);

        $user = auth()->user();
        $rates = [30 => 1.27, 45 => 1.45, 60 => 1.66];
        $period = $request->installment_period;

        // ดึงราคาทองจาก DB เท่านั้น
        $gold = DB::table('daily_gold_prices')->where('date', Carbon::today()->toDateString())->first();
        if (!$gold) {
            $gold = DB::table('daily_gold_prices')->orderByDesc('date')->first();
        }
        $approvedGoldPrice = $gold ? $gold->sell : 0;
        $gold_price = $approvedGoldPrice * $request->gold_amount;
        $totalWithInterest = $gold_price * $rates[$period];
        $dailyPayment = round($totalWithInterest / $period, 2);

        InstallmentRequest::create([
            'fullname' => $user->first_name . ' ' . $user->last_name,
            'phone' => $user->phone,
            'id_card' => $user->id_card_number,
            'gold_type' => 'ทองรูปพรรณ',
            'gold_amount' => $request->gold_amount,
            'installment_period' => $period,
            'approved_gold_price' => $approvedGoldPrice,
            'total_gold_price' => $gold_price,
            'total_with_interest' => $totalWithInterest,
            'daily_payment_amount' => $dailyPayment,
            'status' => 'pending',
            'interest_rate' => ($rates[$period] - 1) * 100,
            'next_payment_date' => now()->addDays(1),
            'user_id' => $user->id,
            'is_guest' => 0,
            'referrer_code' => $request->referrer_code,
        ]);
        return redirect()->back()->with('success', 'ส่งคำขอผ่อนทองเรียบร้อยแล้ว รอการอนุมัติจากแอดมินค่ะ');
    }

    // ================= หลังบ้าน (Filament) ===================

    public function approveByStaff($id)
    {
        $request = InstallmentRequest::findOrFail($id);

        if ($request->status !== 'pending') {
            return back()->with('error', 'อนุมัติได้เฉพาะสัญญาที่รออนุมัติ');
        }

        $request->update([
            'status' => 'staff_approved',
            'first_approved_date' => now(),
            'responsible_staff' => Auth::user()->username ?? Auth::user()->name,
        ]);

        foreach (Admin::whereIn('role', ['admin', 'OAA'])->get() as $admin) {
            $admin->notify(new \App\Notifications\InstallmentDueReminderNotification($request));
        }

        return back()->with('success', 'พนักงานอนุมัติสำเร็จ ส่งต่อรอผู้บริหารอนุมัติ');
    }

    public function approveByAdmin($id)
    {
        $request = InstallmentRequest::findOrFail($id);

        if (!in_array($request->status, ['staff_approved', 'pending'])) {
            return back()->with('error', 'อนุมัติได้เฉพาะสัญญาที่รออนุมัติ (ผ่าน staff แล้ว)');
        }

        $goldPrice = $request->approved_gold_price;
        $totalGoldPrice = $goldPrice * $request->gold_amount;
        $rates = [30 => 1.27, 45 => 1.45, 60 => 1.66];
        $interestRate = $rates[$request->installment_period] ?? 1;
        $totalWithInterest = $totalGoldPrice * $interestRate;
        $dailyPayment = round($totalWithInterest / $request->installment_period, 2);

        $request->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'total_gold_price' => $totalGoldPrice,
            'total_with_interest' => $totalWithInterest,
            'daily_payment_amount' => $dailyPayment,
            'start_date' => now(),
            'next_payment_date' => now()->addDay(),
            'contract_number' => $request->contract_number ?? ('A' . str_pad((68000 + $request->id), 5, '0', STR_PAD_LEFT)),
            'payment_number' => $request->payment_number ?? ('INV' . now()->format('ym') . str_pad($request->id, 4, '0', STR_PAD_LEFT)),
        ]);

        $request->generatePayments();

        if ($request->responsible_staff) {
            $staff = Admin::where('username', $request->responsible_staff)->first();
            if ($staff) {
                $staff->notify(new \App\Notifications\InstallmentDueReminderNotification($request));
            }
        }
        foreach (Admin::where('role', 'OAA')->get() as $boss) {
            $boss->notify(new \App\Notifications\InstallmentDueReminderNotification($request));
        }

        return back()->with('success', 'อนุมัติสัญญาสำเร็จ');
    }
}
