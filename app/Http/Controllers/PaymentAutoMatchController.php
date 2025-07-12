<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentPayment;
use Carbon\Carbon;

class PaymentAutoMatchController extends Controller
{
    /**
     * POST /api/payment/auto-match
     * ใช้สำหรับอัพเดตสถานะงวดเป็น 'approved' อัตโนมัติ จากยอดโอนเข้า (โดย script หรือ webhook)
     */
    public function autoMatch(Request $request)
    {
        $amount = $request->input('amount');
        $txn_time = $request->input('txn_time'); // Datetime: 2025-07-12 09:00:00
        $note = $request->input('note', null);

        // หา payment ที่รอชำระและตรงกับยอดเงิน+เวลา (หรือกรณีต้อง match note/ref ก็เพิ่มเข้าไป)
        $payment = InstallmentPayment::where('payment_status', 'pending')
            ->where('status', 'pending')
            ->where('amount', $amount)
            ->whereBetween('payment_due_date', [
                Carbon::parse($txn_time)->subHours(24), // ปรับช่วงเวลาได้
                Carbon::parse($txn_time)->addHours(24)
            ])
            ->first();

        if ($payment) {
            $payment->amount_paid = $amount;
            $payment->payment_status = 'paid';
            $payment->status = 'approved';
            $payment->paid_at = Carbon::parse($txn_time);
            $payment->admin_notes = 'Auto matched (' . now() . ')';
            if ($note) {
                $payment->ref = $note;
            }
            $payment->save();

            return response()->json(['success' => true, 'matched_payment_id' => $payment->id]);
        }
        return response()->json(['success' => false, 'msg' => 'No matching payment']);
    }
}
