<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function() {
    Route::get('/installments', [InstallmentController::class, 'index']);
    Route::post('/installments', [InstallmentController::class, 'store']);
    Route::get('/installments/{id}', [InstallmentController::class, 'show']);
    Route::put('/installments/{id}', [InstallmentController::class, 'update']);

    Route::post('/installments/{id}/upload-documents', [InstallmentController::class, 'uploadDocuments']);
});
