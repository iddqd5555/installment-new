<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\Storage;

class PaymentProofController extends Controller
{
    public function store(Request $request, InstallmentPayment $payment)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'amount_paid' => 'required|numeric|min:1', // เพิ่มจำนวนเงินที่ลูกค้าระบุเอง
        ]);

        $path = $request->file('payment_proof')->store('public/payment_proofs');

        // บันทึกข้อมูลหลักฐานการชำระ
        $payment->payment_proof = Storage::url($path);
        $payment->payment_status = 'pending';
        $payment->amount_paid = $request->amount_paid; // เก็บจำนวนเงินที่ลูกค้าระบุเอง
        $payment->save();

        return redirect()->back()->with('success', 'อัปโหลดหลักฐานการชำระเงินสำเร็จ กรุณารอแอดมินตรวจสอบค่ะ');
    }
}
