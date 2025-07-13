<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\UserLocationController;
use App\Http\Controllers\KBankPaymentController;
use App\Http\Controllers\InstallmentPaymentController;
use App\Http\Controllers\PaymentAutoMatchController;

// Public Routes (ไม่ต้องล็อกอิน)
Route::post('/login', [AuthController::class, 'login']);
Route::get('/company-bank', function () {
    return [
        'bank' => 'กสิกรไทย',
        'number' => '865-1-00811-6',
        'name' => 'บริษัท วิสดอม โกลด์ กรุ้ป จำกัด',
    ];
});
Route::get('/gold-latest', [\App\Http\Controllers\Api\GoldPriceController::class, 'latest']);

// Route ที่ต้องล็อกอินผ่าน Sanctum Token
Route::middleware('auth:sanctum')->group(function() {

    // Installment Payment Routes
    Route::post('/installment/pay', [InstallmentPaymentController::class, 'pay']);
    Route::get('/installment/overdue', [InstallmentPaymentController::class, 'overdue']);
    Route::get('/installment/history', [InstallmentPaymentController::class, 'history']);

    // Payment QR & Banking Routes
    Route::post('/payment/auto-match', [PaymentAutoMatchController::class, 'autoMatch']);
    Route::post('/payment/qr', [KBankPaymentController::class, 'generateQr']);
    Route::get('/payment/qr-status/{qrRef}', [KBankPaymentController::class, 'checkQrStatus']);
    Route::post('/payment/inquiry-v4', [KBankPaymentController::class, 'inquiryV4Qr']);
    Route::post('/payment/cancel-qr', [KBankPaymentController::class, 'cancelQr']);
    Route::post('/payment/void-payment', [KBankPaymentController::class, 'voidPayment']);

    // Dashboard Routes
    Route::get('/dashboard-data', [DashboardApiController::class, 'dashboardData']);
    Route::get('/payments', [DashboardApiController::class, 'paymentHistory']);

    // Installment Contract Routes
    Route::get('/installments', [InstallmentController::class, 'index']);
    Route::post('/installments', [InstallmentController::class, 'store']);
    Route::get('/installments/{id}', [InstallmentController::class, 'show']);
    Route::put('/installments/{id}', [InstallmentController::class, 'update']);
    Route::post('/installments/{id}/upload-documents', [InstallmentController::class, 'uploadDocuments']);

    // User Location
    Route::post('/user/update-location', [UserLocationController::class, 'updateLocation']);

    // Profile Routes
    Route::get('/user/profile', [ProfileController::class, 'show']);
    Route::post('/user/profile/update', [ProfileController::class, 'update']);
});
