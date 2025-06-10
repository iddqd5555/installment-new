<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentInfo;

class PaymentInfoController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'bank_account' => 'required',
            'account_number' => 'required'
        ]);

        PaymentInfo::create($request->only('bank_account', 'account_number'));

        return redirect()->back()->with('success', 'บันทึกข้อมูลเรียบร้อย');
    }

    public function showPaymentInfo() {
        $paymentInfo = PaymentInfo::latest()->first();
        return view('payment-info', compact('paymentInfo'));
    }
}