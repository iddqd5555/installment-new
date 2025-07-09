<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KBankApiService;
use App\Models\PaymentQrLog;
use Carbon\Carbon;

class KBankPaymentController extends Controller
{
    public function generateQr(Request $request)
    {
        $service = new KBankApiService();
        $token = $service->getAccessToken();

        $qrType = $request->input('qrType', 3);
        $envId = $qrType == 4 ? 'QR003' : 'QR002';
        $partnerTxnUid = $request->input('partnerTxnUid', $qrType == 4 ? 'PARTNERTEST0001-2' : 'PARTNERTEST0001');

        $params = [
            "partnerTxnUid" => $partnerTxnUid,
            "partnerId" => env('KBANK_PARTNER_ID', 'PTR1051673'),
            "partnerSecret" => env('KBANK_PARTNER_SECRET', 'd4bded59200547bc85903574a293831b'),
            "requestTime" => Carbon::now()->toIso8601String(),
            "merchantId" => env('KBANK_MERCHANT_ID', 'KB102057149704'),
            "qrType" => (int)$qrType,
            "amount" => $request->input('amount', '1.00'),
            "currencyCode" => "THB",
            "reference1" => "INV001",
            "reference2" => "HELLOWORLD",
            "reference3" => "INV001",
            "reference4" => "INV001"
        ];

        $result = $service->generateThaiQr($token, $params, $envId);

        \Log::info('KBank generate QR result', [$result]);

        if (empty($result) || !isset($result['qrCodeImage'])) {
            return response()->json(['success' => false, 'message' => 'สร้าง QR ไม่สำเร็จ', 'data' => $result], 500);
        }

        PaymentQrLog::create([
            'qr_ref' => $result['qrRef'] ?? null,
            'amount' => $params['amount'],
            'currency' => "THB",
            'status' => 'pending',
            'qr_image' => $result['qrCodeImage'] ?? null,
            'transaction_id' => $result['transactionId'] ?? null,
        ]);

        return response()->json(['success' => true, 'data' => $result]);
    }

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

    // ฟังก์ชันเดียว ครอบทุก exercise inquiry v4 (เช่น QR004, QR005, QR006 ...)
    public function inquiryV4Qr(Request $request)
    {
        $service = new KBankApiService();
        $token = $service->getAccessToken();

        $envId = $request->input('envId', 'QR004');
        $params = [
            "partnerTxnUid" => $request->input('partnerTxnUid', 'PARTNERTEST0002'),
            "partnerId" => env('KBANK_PARTNER_ID', 'PTR1051673'),
            "partnerSecret" => env('KBANK_PARTNER_SECRET', 'd4bded59200547bc85903574a293831b'),
            "requestTime" => Carbon::now()->toIso8601String(),
            "merchantId" => env('KBANK_MERCHANT_ID', 'KB102057149704'),
            "origPartnerTxnUid" => $request->input('origPartnerTxnUid', 'PARTNERTEST0001'),
        ];

        $result = $service->inquiryV4Qr($token, $params, $envId);

        return response()->json(['success' => true, 'data' => $result]);
    }

    // ฟังก์ชันเดียว ครอบทุก exercise Cancel QR (เช่น QR008, QR009, ...)
    public function cancelQr(Request $request)
    {
        $service = new KBankApiService();
        $token = $service->getAccessToken();

        $envId = $request->input('envId', 'QR008');
        $params = [
            "partnerTxnUid" => $request->input('partnerTxnUid', 'PARTNERTEST0006'),
            "partnerId" => env('KBANK_PARTNER_ID', 'PTR1051673'),
            "partnerSecret" => env('KBANK_PARTNER_SECRET', 'd4bded59200547bc85903574a293831b'),
            "requestTime" => Carbon::now()->toIso8601String(),
            "merchantId" => env('KBANK_MERCHANT_ID', 'KB102057149704'),
            "origPartnerTxnUid" => $request->input('origPartnerTxnUid', 'PARTNERTEST0001'),
        ];

        $result = $service->cancelQr($token, $params, $envId);

        return response()->json(['success' => true, 'data' => $result]);
    }

    // ฟังก์ชัน Void Payment (รองรับทุก Exercise: QR012, QR013, ...)
    public function voidPayment(Request $request)
    {
        $service = new KBankApiService();
        $token = $service->getAccessToken();

        $envId = $request->input('envId', 'QR012');
        $params = [
            "partnerTxnUid" => $request->input('partnerTxnUid', 'PARTNERTEST0009'),
            "partnerId" => env('KBANK_PARTNER_ID', 'PTR1051673'),
            "partnerSecret" => env('KBANK_PARTNER_SECRET', 'd4bded59200547bc85903574a293831b'),
            "requestTime" => now()->toIso8601String(),
            "merchantId" => env('KBANK_MERCHANT_ID', 'KB102057149704'),
            "origPartnerTxnUid" => $request->input('origPartnerTxnUid', 'PARTNERTEST0011'),
        ];

        $result = $service->voidPayment($token, $params, $envId);

        return response()->json(['success' => true, 'data' => $result]);
    }
}
