<?php

use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Login Admin
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');

    Route::middleware(['check_admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroyAdmin'])->name('admin.logout');

        Route::resource('installments', InstallmentAdminController::class);
    Route::post('installments/{id}/approve', [InstallmentAdminController::class, 'approve'])->name('installments.approve');
    Route::post('installments/{id}/reject', [InstallmentAdminController::class, 'reject'])->name('installments.reject');
    });
});
