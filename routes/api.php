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

Route::post('/installment/pay', [InstallmentPaymentController::class, 'pay']);
Route::get('/installment/overdue', [InstallmentPaymentController::class, 'overdue']);
Route::get('/installment/history', [InstallmentPaymentController::class, 'history']);

// API บัญชีปลายทางบริษัท
Route::get('/company-bank', function () {
    return [
        'bank' => 'กสิกรไทย',
        'number' => '865-1-00811-6',
        'name' => 'บริษัท วิสดอม โกลด์ กรุ้ป จำกัด',
    ];
});


Route::post('/payment/qr', [KBankPaymentController::class, 'generateQr']);
Route::get('/payment/qr-status/{qrRef}', [KBankPaymentController::class, 'checkQrStatus']);
Route::post('/payment/inquiry-v4', [KBankPaymentController::class, 'inquiryV4Qr']);
Route::post('/payment/cancel-qr', [KBankPaymentController::class, 'cancelQr']);
Route::post('/payment/void-payment', [KBankPaymentController::class, 'voidPayment']);

Route::get('/gold-latest', [\App\Http\Controllers\Api\GoldPriceController::class, 'latest']);


Route::middleware('auth:sanctum')->post('/user/update-location', [UserLocationController::class, 'updateLocation']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard-data', [DashboardApiController::class, 'dashboardData']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function() {
    Route::get('/installments', [InstallmentController::class, 'index']);
    Route::post('/installments', [InstallmentController::class, 'store']);
    Route::get('/installments/{id}', [InstallmentController::class, 'show']);
    Route::put('/installments/{id}', [InstallmentController::class, 'update']);
    Route::post('/installments/{id}/upload-documents', [InstallmentController::class, 'uploadDocuments']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard-data', [DashboardApiController::class, 'dashboardData']);
    Route::get('/payments', [DashboardApiController::class, 'paymentHistory']); // <== เพิ่มบรรทัดนี้!
});

Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('/user/profile', [ProfileController::class, 'show']);
    Route::post('/user/profile/update', [ProfileController::class, 'update']);
});


