<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\User\InstallmentController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\InstallmentRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\KBankTestController;
use App\Http\Controllers\SlipPaymentController;
use App\Models\Review;
use App\Models\Logo;

// ---- API ส่งสลิป (ยังใช้ได้) ----
Route::post('/slip-payment', [SlipPaymentController::class, 'upload']);

// ---- Dashboard User (ของ web user ตัดทิ้ง, ไม่ให้เข้าถึงแล้ว) ----
//Route::middleware(['auth'])->get('/dashboard', function () {
//    return redirect('/login');
//})->name('dashboard');

// ---- หน้าแรก/Guest ----
Route::get('/', function () {
    $gold = DB::table('daily_gold_prices')->where('date', Carbon::today()->toDateString())->first();
    if (!$gold) {
        $gold = DB::table('daily_gold_prices')->orderByDesc('date')->first();
    }
    $goldPrices = $gold ? [
        'ornament_sell' => $gold->sell,
        'ornament_buy'  => $gold->buy,
        'date'          => $gold->date,
    ] : null;

    // --- ดึงรีวิว 4 อันล่าสุด กับโลโก้หลัก (type=main) จากฐานข้อมูล ---
    $reviews = Review::latest()->take(4)->get();
    $logo = Logo::where('type', 'main')->first();

    return view('welcome', compact('goldPrices', 'reviews', 'logo'));
});

// ---- Profile ----
Route::get('/profile', function () {
    return view('coming-soon');
})->name('profile.edit');

// ---- Guest access: ดูราคาทอง/ขอผ่อน ----
Route::middleware(['guest'])->group(function() {
    Route::get('/gold', [InstallmentRequestController::class, 'goldapi'])->name('gold.index');
});

Route::post('/gold/submit-guest', [InstallmentRequestController::class, 'submitGoldGuest'])->name('gold.submit_guest');

Route::get('/phone', function () {
    return view('phone');
})->name('phone');
Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// ---- Login: ให้ redirect ไปหน้า Coming Soon ----
Route::get('/login', function () {
    return view('coming-soon');
})->name('login');
Route::post('/login', function () {
    abort(403, 'โปรดดาวน์โหลดแอปเพื่อเข้าใช้งาน');
});

Route::get('/register', fn() => abort(403, 'โปรดดาวน์โหลดแอปเพื่อสมัครสมาชิก'))->name('register');
Route::post('/register', fn() => abort(403, 'โปรดดาวน์โหลดแอปเพื่อสมัครสมาชิก'));

// Sunmi Print Route
Route::get('/sunmi/print/{id}', function($id) {
    $payment = InstallmentPayment::findOrFail($id);
    return view('pdf.receipt', [
        'payment' => $payment,
        'contract' => $payment->installmentRequest,
        'customer' => $payment->installmentRequest->user,
    ]);
})->name('sunmi.print');


// ---- Auth route, Admin route ----
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
