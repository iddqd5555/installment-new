<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class KBankApiService
{
    protected $consumerId;
    protected $consumerSecret;
    protected $baseUrl;

    public function __construct()
    {
        $this->consumerId = env('KBANK_CONSUMER_ID');
        $this->consumerSecret = env('KBANK_CONSUMER_SECRET');
        $this->baseUrl = 'https://openapi-sandbox.kasikornbank.com/v2';
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
            ->post($this->baseUrl . '/oauth/token', [
                'grant_type' => 'client_credentials'
            ]);

        if ($response->ok()) {
            return $response->json()['access_token'] ?? null;
        }
        return null;
    }

    // ตัวอย่างฟังก์ชั่นสร้าง QR
    public function generateThaiQr($accessToken, $params = [])
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'x-test-mode' => 'true'
        ])->post($this->baseUrl . '/qr/payment', $params);

        return $response->json();
    }
    public function inquiryQrStatus($accessToken, $qrRef)
    {
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'x-test-mode' => 'true'
        ])->get($this->baseUrl . '/qr/inquiry', [
            'qrRef' => $qrRef
        ]);
        return $response->json();
    }

}
