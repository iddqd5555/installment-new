<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InstallmentRequestController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\NotificationController; // ✅ เพิ่มใหม่
use App\Http\Controllers\KBankTestController;

Route::get('/kbank/token', [KBankTestController::class, 'getAccessToken']);
Route::get('/kbank/create-qr', [KBankTestController::class, 'createQr']);


// หน้าแรก สำหรับผู้ใช้ที่ไม่ได้ล็อคอิน
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::middleware(['guest'])->group(function() {
    Route::get('/gold', [InstallmentRequestController::class, 'goldapi'])->name('gold.index');
    Route::post('/gold/submit-guest', [InstallmentRequestController::class, 'submitGoldGuest'])->name('gold.submit_guest');
});

Route::get('/phone', function () {
    return view(auth()->check() ? 'phone_logged_in' : 'phone');
})->name('phone');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/dashboard', [InstallmentRequestController::class, 'dashboard'])
    ->middleware('auth')->name('dashboard');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// ปิดระบบสมัครสมาชิกชั่วคราว
// Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
// Route::post('/register', [RegisteredUserController::class, 'store']);

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/gold/member', [InstallmentRequestController::class, 'showGoldForm'])->name('gold.member');
    Route::post('/gold/member/store', [InstallmentRequestController::class, 'submitGoldMember'])->name('gold.request.store');

    Route::get('/installments/request/create/{id}', [InstallmentRequestController::class, 'create'])
        ->name('installments.request.create');

    Route::get('/orders/history', [InstallmentRequestController::class, 'orderHistory'])
        ->name('orders.history');

    Route::post('payments/{id}/upload-proof', [PaymentController::class, 'uploadProof'])->name('payments.upload-proof');

    Route::get('/payment-info', [BankAccountController::class, 'index'])->name('payment-info');

    // ✅ เพิ่ม route สำหรับระบบแจ้งเตือน
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
});

// Authentication Routes
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
