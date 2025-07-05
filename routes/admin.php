<?php

use App\Http\Controllers\InstallmentStaffController;
use App\Http\Controllers\PaymentStaffController;
use App\Http\Controllers\AdminAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {

    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])
            ->name('filament.admin.auth.login');

        Route::post('/login', [AdminAuthController::class, 'login'])
            ->name('filament.admin.auth.attempt');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', fn() => redirect()->route('filament.admin.pages.dashboard'))
            ->name('custom.admin.dashboard');

        Route::middleware('checkRole:staff')->group(function () {
            Route::resource('staff/installments', InstallmentStaffController::class)
                ->only(['index', 'edit', 'update'])
                ->names('custom.staff.installments');

            Route::post('staff/payments/{id}/approve', [PaymentStaffController::class, 'approve'])
                ->name('custom.staff.payments.approve');
        });
    });
});
