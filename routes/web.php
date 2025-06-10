<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InstallmentRequestController;
use App\Http\Controllers\Admin\InstallmentAdminController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\Admin\PaymentInfoController;

// หน้าแรก สำหรับผู้ใช้ที่ไม่ได้ล็อคอิน
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// หน้าราคาทอง ไม่ต้องล็อคอิน
Route::get('/gold', [InstallmentRequestController::class, 'goldapi'])->name('gold.index');

// ติดต่อเรา
Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// หน้า dashboard สำหรับสมาชิกที่ล็อคอินแล้ว
Route::get('/dashboard', [InstallmentRequestController::class, 'dashboard'])
    ->middleware('auth')
    ->name('dashboard');

// กลุ่ม routes สมาชิกทั่วไป (ผ่อนทอง)
Route::middleware(['auth'])->group(function () {
    Route::post('/gold/request', [InstallmentRequestController::class, 'store'])->name('gold.request.store');
    Route::get('/installments/request/create/{id}', [InstallmentRequestController::class, 'create'])
        ->name('installments.request.create');
    Route::get('/orders/history', [InstallmentRequestController::class, 'orderHistory'])
        ->name('orders.history');
    
        Route::post('/payments/{payment}/upload-proof', [PaymentProofController::class, 'store'])->name('payments.upload-proof');
});

// กลุ่ม routes สำหรับ Admin เท่านั้น (จัดการระบบทั้งหมด)
Route::middleware(['auth', 'check_admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Route เดิมที่มีอยู่แล้ว (ไม่ต้องแก้ไข)
    Route::resource('installments', \App\Http\Controllers\Admin\InstallmentAdminController::class);
    Route::patch('installments/{id}/update-status', [\App\Http\Controllers\Admin\InstallmentAdminController::class, 'updateStatus'])->name('admin.installments.updateStatus');
    Route::get('/installments', [InstallmentAdminController::class, 'index'])->name('admin.installments.index');
    Route::patch('/requests/verify/{id}', [InstallmentAdminController::class, 'verify'])->name('admin.requests.verify');
    Route::get('/installments/create', [InstallmentAdminController::class, 'create'])->name('admin.installments.create');
    Route::post('/installments', [InstallmentAdminController::class, 'store'])->name('admin.installments.store');
    Route::get('/installments/{id}/edit', [InstallmentAdminController::class, 'edit'])->name('admin.installments.edit');
    Route::patch('/installments/{id}', [InstallmentAdminController::class, 'update'])->name('admin.installments.update');
    Route::delete('/installments/{id}', [InstallmentAdminController::class, 'destroy'])->name('admin.installments.destroy');
    
    // ✅ Route ที่ต้องเพิ่มใหม่ชัดเจนที่สุด
    Route::get('/payments', [InstallmentAdminController::class, 'payments'])->name('payments.index');
    Route::patch('/payments/{paymentId}/approve', [InstallmentAdminController::class, 'approvePayment'])->name('payments.approve');
    Route::patch('/payments/{paymentId}/reject', [InstallmentAdminController::class, 'rejectPayment'])->name('payments.reject');
});

// 🚩 Route ใหม่สำหรับระบบจัดการบัญชีชำระเงิน
Route::middleware(['auth', 'check_admin'])->group(function () {
    
    Route::get('/admin/payment-settings', function () {
        return view('admin.payment-settings');
    })->name('admin.payment-settings');

    Route::post('/admin/payment-info/store', [PaymentInfoController::class, 'store'])
        ->name('admin.payment-info.store');
});

// 🚩 Route ใหม่สำหรับแสดงข้อมูลชำระเงิน (ผู้ใช้งานทั่วไป)
Route::get('/payment-info', [PaymentInfoController::class, 'showPaymentInfo'])
    ->name('payment-info');

// Authentication Routes
require __DIR__.'/auth.php';
