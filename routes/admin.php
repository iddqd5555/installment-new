<?php

use App\Http\Controllers\InstallmentStaffController;
use App\Http\Controllers\PaymentStaffController;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

Route::prefix('admin')->group(function () {

    Route::middleware('guest.admin')->group(function () {
        // ยังไม่ได้ใช้งานระบบนี้ จึงปิดไว้ก่อน
        // Route::get('/custom-login', [AdminAuthController::class, 'showLoginForm'])->name('custom.admin.auth.login');
        // Route::post('/custom-login', [AdminAuthController::class, 'login'])->name('custom.admin.auth.login.submit');
    });

    Route::middleware('auth.admin')->group(function () {
        Route::get('/dashboard', function() {
            return redirect()->route('filament.admin.pages.dashboard');
        })->name('custom.admin.dashboard');

        // 🔴 ปิดส่วนที่ไม่ใช้ (ใช้ Filament Resource แทนหมดแล้ว)
        // Route::middleware('checkRole:admin,OAA')->group(function () {
        //     Route::resource('installments', InstallmentAdminController::class)
        //         ->names('custom.admin.installments');

        //     Route::post('installments/{id}/approve', [InstallmentAdminController::class, 'approve'])
        //         ->name('custom.admin.installments.approve');

        //     Route::post('installments/{id}/reject', [InstallmentAdminController::class, 'reject'])
        //         ->name('custom.admin.installments.reject');
        // });

        Route::middleware('checkRole:staff')->group(function () {
            Route::resource('staff/installments', InstallmentStaffController::class)
                ->only(['index', 'edit', 'update'])
                ->names('custom.staff.installments');

            Route::post('staff/payments/{id}/approve', [PaymentStaffController::class, 'approve'])
                ->name('custom.staff.payments.approve');
        });
    });
});
