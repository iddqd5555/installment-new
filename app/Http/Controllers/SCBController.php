<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\InstallmentRequest;

class SCBController extends Controller
{
    public function callback(Request $request)
    {
        Log::info('SCB Callback:', $request->all());

        $transactionId = $request->input('transaction_id');
        $status = $request->input('status');
        $amount = $request->input('amount');
        $installmentRequestId = $request->input('installment_request_id');

        if ($status == 'success') {
            Payment::create([
                'installment_request_id' => $installmentRequestId,
                'amount' => $amount,
                'payment_method' => 'SCB API',
            ]);

            // Update InstallmentRequest
            $installment = InstallmentRequest::find($installmentRequestId);
            if ($installment) {
                $installment->status = 'approved';
                $installment->total_paid += $amount;
                $installment->remaining_amount -= $amount;
                $installment->save();
            }
        }

        return response()->json(['status' => 'success']);
    }

    // ตัวอย่างฟังก์ชันเรียก SCB API (ถ้าในอนาคตคุณต้องเรียกใช้งานเพิ่ม)
    private function callSCBApi()
    {
        $response = Http::withHeaders([
            'API-Key' => env('SCB_API_KEY'),
            'API-Secret' => env('SCB_API_SECRET'),
        ])->post(env('SCB_CALLBACK_URL'), [
            // parameters ที่คุณต้องการส่งไป SCB API
        ]);

        return $response->json();
    }
}
