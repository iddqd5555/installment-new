<?php

namespace App\Http\Controllers;

use App\Services\KBankApiService;
use Illuminate\Http\Request;

class KBankTestController extends Controller
{
    public function getAccessToken()
    {
        $service = new KBankApiService();
        $token = $service->getAccessToken();
        if ($token) {
            return response()->json(['success' => true, 'access_token' => $token]);
        } else {
            return response()->json(['success' => false, 'message' => 'ไม่สามารถขอ access token ได้'], 500);
        }
    }

    public function createQr(Request $request)
    {
        $service = new KBankApiService();
        $token = $service->getAccessToken();
        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Token Error'], 500);
        }

        // รับค่าจาก request หรือจะใส่เองก็ได้
        $amount = $request->input('amount', '100.00');
        $currency = $request->input('currency', 'THB');
        $merchantId = $request->input('merchantId', 'testMerchant'); // เปลี่ยน merchantId เป็นของจริงตอน production

        $params = [
            "merchantId" => $merchantId,
            "amount" => $amount,
            "currency" => $currency,
            // เพิ่ม fields ตามที่ KBank ต้องการ
        ];

        $result = $service->generateThaiQr($token, $params);
        return response()->json($result);
    }
}
