<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\User\InstallmentController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\InstallmentRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\KBankTestController;
use App\Http\Controllers\SlipPaymentController;

Route::post('/slip-payment', [SlipPaymentController::class, 'upload']);


// ------------------- Dashboard User (แก้ route dashboard ให้ชี้ controller ใหม่) -------------------
Route::middleware(['auth'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// ------------------- หน้าแรก/Guest -------------------
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Guest access: ดูราคาทอง/ขอผ่อนแบบ guest
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

// ------------------- Auth / Profile -------------------
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
    Route::get('/payment-info', [BankAccountController::class, 'index'])->name('payment-info');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
});

// ------------------- User: คำขอผ่อนทอง/งวดผ่อน/จ่ายเงิน -------------------
Route::middleware(['auth'])->group(function() {
    // CRUD คำขอผ่อนทอง (user)
    Route::get('/installments', [InstallmentController::class, 'index'])->name('user.installments.index');
    Route::get('/installments/create', [InstallmentController::class, 'create'])->name('user.installments.create');
    Route::post('/installments', [InstallmentController::class, 'store'])->name('user.installments.store');
    Route::get('/installments/{id}', [InstallmentController::class, 'show'])->name('user.installments.show');

    // หน้า QR + ประวัติ QR
    Route::get('/qr-history', [InstallmentController::class, 'qrHistory'])->name('user.qr_history');
    Route::get('/installment/{id}/create-qr', [InstallmentController::class, 'createQr'])->name('user.create_qr');
});

// ------------------- KBank Test -------------------
Route::get('/kbank/token', [KBankTestController::class, 'getAccessToken']);
Route::get('/kbank/create-qr', [KBankTestController::class, 'createQr']);

// ------------------- ฝั่ง admin/หลังบ้าน/ระบบอื่น -------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/gold/member', [InstallmentRequestController::class, 'showGoldForm'])->name('gold.member');
    Route::post('/gold/member/store', [InstallmentRequestController::class, 'submitGoldMember'])->name('gold.request.store');
    Route::get('/installments/request/create/{id}', [InstallmentRequestController::class, 'create'])->name('installments.request.create');
    Route::get('/orders/history', [InstallmentRequestController::class, 'orderHistory'])->name('orders.history');
    Route::post('payments/{id}/upload-proof', [PaymentController::class, 'uploadProof'])->name('payments.upload-proof');
});

// ------------------- Auth route, Admin route -------------------
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
