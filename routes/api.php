<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\DashboardApiController;

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


