<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\UserLocationController;
use App\Http\Controllers\KBankPaymentController;

Route::post('/payment/qr', [KBankPaymentController::class, 'generateQr']);
Route::get('/payment/qr-status/{qrRef}', [KBankPaymentController::class, 'checkQrStatus']);



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


