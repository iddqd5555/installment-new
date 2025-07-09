<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KBankApiService;
use App\Models\PaymentQrLog;

class KBankPaymentController extends Controller
{
    // สร้าง QR และบันทึกลง DB
    public function generateQr(Request $request)
    {
        $service = new KBankApiService();
        $token = $service->getAccessToken();

        $amount = $request->input('amount', '100.00');
        $merchantId = env('KBANK_MERCHANT_ID', 'testMerchant');
        $params = [
            "merchantId" => $merchantId,
            "amount" => $amount,
            "currency" => "THB",
            // เพิ่ม param ตาม KBank doc
        ];

        $result = $service->generateThaiQr($token, $params);

        $qrRef = $result['qrRef'] ?? uniqid('qr_', true);

        PaymentQrLog::create([
            'qr_ref' => $qrRef,
            'amount' => $amount,
            'currency' => "THB",
            'status' => 'pending',
            'qr_image' => $result['qrImage'] ?? null,
            'transaction_id' => $result['transactionId'] ?? null,
        ]);
        return response()->json($result);
    }

    // เช็คสถานะ QR ว่าจ่ายหรือยัง
    public function checkQrStatus($qrRef)
    {
        $service = new KBankApiService();
        $token = $service->getAccessToken();

        $result = $service->inquiryQrStatus($token, $qrRef);

        if (($result['status'] ?? null) == 'paid') {
            PaymentQrLog::where('qr_ref', $qrRef)->update(['status' => 'paid']);
        }
        return response()->json($result);
    }
}
