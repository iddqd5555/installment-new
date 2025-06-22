<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\InstallmentPayment;

class SCBController extends Controller
{
    public function callback(Request $request)
    {
        Log::info('SCB Payment Callback:', $request->all());

        $transactionId = $request->input('transactionId');
        $paymentStatus = $request->input('status');
        $amount = $request->input('amount');

        if (!$transactionId || !$paymentStatus) {
            return response()->json(['status' => 'error', 'message' => 'ข้อมูลไม่ครบ'], 400);
        }

        $payment = InstallmentPayment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบรายการชำระเงิน'], 404);
        }

        if ($paymentStatus == 'success') {
            $payment->update([
                'status' => 'approved',
                'amount_paid' => $amount,
                'paid_at' => now(),
            ]);
        } else {
            $payment->update(['status' => 'failed']);
        }

        return response()->json(['status' => 'success'], 200);
    }
}
