<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InstallmentRequestController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Http\Request;

// หน้าแรก สำหรับผู้ใช้ที่ไม่ได้ล็อคอิน
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::middleware(['guest'])->group(function() {
    // หน้าราคาทอง ไม่ต้องล็อคอิน
    Route::get('/gold', [InstallmentRequestController::class, 'goldapi'])->name('gold.index');
    Route::post('/gold/submit-guest', [InstallmentRequestController::class, 'submitGoldGuest'])->name('gold.submit_guest');
});

// หน้า Phone (ล็อคอินและไม่ล็อคอิน)
Route::get('/phone', function () {
    if (auth()->check()) {
        return view('phone_logged_in');
    } else {
        return view('phone');
    }
})->name('phone');

// ติดต่อเรา
Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// หน้า dashboard สำหรับสมาชิกที่ล็อคอินแล้ว
Route::get('/dashboard', [InstallmentRequestController::class, 'dashboard'])
    ->middleware('auth')
    ->name('dashboard');

// 🔒 ระบบ Login (ใช้เบอร์โทรศัพท์)
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// ❌ ระบบสมัครสมาชิก (ปิดไว้ก่อนชั่วคราว)
// Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
// Route::post('/register', [RegisteredUserController::class, 'store']);

// กลุ่ม routes สมาชิกทั่วไป (ผ่อนทอง)
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
});

// Authentication Routes
require __DIR__.'/auth.php';
