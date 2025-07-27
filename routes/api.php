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
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\PinController;
use App\Http\Controllers\Api\UserDocumentController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\BankAccountApiController;

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/company-banks', [BankAccountApiController::class, 'active']);
Route::get('/gold-latest', [\App\Http\Controllers\Api\GoldPriceController::class, 'latest']);

// Forget Password
Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function() {

    Route::get('/advance-payments/all', [InstallmentPaymentController::class, 'allAdvancePayments']);

    Route::post('/installment/pay', [InstallmentPaymentController::class, 'pay']);
    Route::get('/installment/overdue', [InstallmentPaymentController::class, 'overdue']);
    Route::get('/installment/history', [InstallmentPaymentController::class, 'history']);

    Route::post('/payment/auto-match', [PaymentAutoMatchController::class, 'autoMatch']);
    Route::post('/payment/qr', [KBankPaymentController::class, 'generateQr']);
    Route::get('/payment/qr-status/{qrRef}', [KBankPaymentController::class, 'checkQrStatus']);
    Route::post('/payment/inquiry-v4', [KBankPaymentController::class, 'inquiryV4Qr']);
    Route::post('/payment/cancel-qr', [KBankPaymentController::class, 'cancelQr']);
    Route::post('/payment/void-payment', [KBankPaymentController::class, 'voidPayment']);

    Route::get('/dashboard-data', [DashboardApiController::class, 'dashboardData']);
    Route::get('/payments', [DashboardApiController::class, 'paymentHistory']);

    Route::get('/installments', [InstallmentController::class, 'index']);
    Route::post('/installments', [InstallmentController::class, 'store']);
    Route::get('/installments/{id}', [InstallmentController::class, 'show']);
    Route::put('/installments/{id}', [InstallmentController::class, 'update']);
    Route::post('/installments/{id}/upload-documents', [InstallmentController::class, 'uploadDocuments']);
    Route::get('/installments/dashboard/current', [InstallmentController::class, 'currentDashboard']);

    Route::get('/installment/user-history', [InstallmentPaymentController::class, 'userHistory']);

    Route::post('/user/update-location', [UserLocationController::class, 'updateLocation']);

    Route::get('/user/profile', [ProfileController::class, 'show']);
    Route::post('/user/profile/update', [ProfileController::class, 'update']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/update', [ProfileController::class, 'update']);

    // ----- แก้ตรงนี้เป็น PATCH -----
    Route::get('notifications', [NotificationApiController::class, 'index']);
    Route::patch('notifications/{id}/read', [NotificationApiController::class, 'markAsRead']);
    Route::post('notifications/mark-all-read', [NotificationApiController::class, 'markAllAsRead']);
    // --------------------------------

    Route::post('/user/upload-document', [UserDocumentController::class, 'upload']);

    Route::post('/user/set-pin', [PinController::class, 'setPin']);
    Route::post('/user/check-pin', [PinController::class, 'checkPin']);

    Route::post('/installments/{contract}/pay-from-advance', [DashboardApiController::class, 'payFromAdvance']);
});
