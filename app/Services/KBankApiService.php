<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class KBankApiService
{
    protected $consumerId;
    protected $consumerSecret;
    protected $baseUrl;
    protected $partnerId;
    protected $partnerSecret;
    protected $merchantId;

    public function __construct()
    {
        $this->consumerId = env('KBANK_CONSUMER_ID');
        $this->consumerSecret = env('KBANK_CONSUMER_SECRET');
        $this->baseUrl = env('KBANK_BASE_URL', 'https://openapi-sandbox.kasikornbank.com');
        $this->partnerId = env('KBANK_PARTNER_ID', 'PTR1051673');
        $this->partnerSecret = env('KBANK_PARTNER_SECRET', 'd4bded59200547bc85903574a293831b');
        $this->merchantId = env('KBANK_MERCHANT_ID', 'KB102057149704');
    }

    public function getAccessToken()
    {
        $basicAuth = base64_encode($this->consumerId . ':' . $this->consumerSecret);

        $response = Http::asForm()
            ->withHeaders([
                'Authorization' => 'Basic ' . $basicAuth,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'x-test-mode' => 'true',
                'env-id' => 'OAUTH2'
            ])
            ->post($this->baseUrl . '/v2/oauth/token', [
                'grant_type' => 'client_credentials'
            ]);

        if ($response->ok()) {
            return $response->json()['access_token'] ?? null;
        }
        return null;
    }

    public function generateThaiQr($accessToken, $params = [], $envId = 'QR002')
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
            'x-test-mode' => 'true',
            'env-id' => $envId,
        ])->post($this->baseUrl . '/v1/qrpayment/request', $params);

        \Log::info('KBank generateThaiQr response', [$response->body()]);

        return $response->json();
    }

    public function inquiryQrStatus($accessToken, $qrRef)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'x-test-mode' => 'true'
        ])->get($this->baseUrl . '/v1/qrpayment/inquiry', [
            'qrRef' => $qrRef
        ]);
        return $response->json();
    }

    // ฟังก์ชันสอบถามสถานะ QR (ทุก Exercise: QR004, QR005, QR006 ...)
    public function inquiryV4Qr($accessToken, $params = [], $envId = 'QR004')
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
            'x-test-mode' => 'true',
            'env-id' => $envId
        ])->post($this->baseUrl . '/v1/qrpayment/v4/inquiry', $params);

        \Log::info('KBank inquiryV4Qr response', [$response->body()]);

        return $response->json();
    }

    // ฟังก์ชัน Cancel QR (ทุก Exercise: QR008, QR009, ...)
    public function cancelQr($accessToken, $params = [], $envId = 'QR008')
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
            'x-test-mode' => 'true',
            'env-id' => $envId
        ])->post($this->baseUrl . '/v1/qrpayment/cancel', $params);

        \Log::info('KBank cancelQr response', [$response->body()]);

        return $response->json();
    }

    // ฟังก์ชัน Void Payment (รองรับทุก Exercise: QR012, QR013, ...)
    public function voidPayment($accessToken, $params = [], $envId = 'QR012')
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
            'x-test-mode' => 'true',
            'env-id' => $envId
        ])->post($this->baseUrl . '/v1/qrpayment/void', $params);

        \Log::info('KBank voidPayment response', [$response->body()]);

        return $response->json();
    }
}
