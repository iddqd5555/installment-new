<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\InstallmentPayment;
use App\Models\InstallmentRequest;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PaymentUploaded;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function uploadProof(Request $request, $installmentRequestId)
    {
        $request->validate([
            'amount_paid' => 'required|numeric|min:1',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $installmentRequest = InstallmentRequest::findOrFail($installmentRequestId);

        // ตรวจสอบยอดคงเหลือ
        if ($request->amount_paid > $installmentRequest->remaining_amount) {
            return redirect()->back()->with('error', '⚠️ จำนวนเงินที่ชำระเกินยอดคงเหลือที่ต้องชำระค่ะ!');
        }

        $amountPerInstallment = $installmentRequest->total_with_interest / $installmentRequest->installment_period;

        $payment = new InstallmentPayment();
        $payment->installment_request_id = $installmentRequest->id;
        $payment->amount = $amountPerInstallment;
        $payment->amount_paid = $request->amount_paid;
        $payment->status = 'pending';
        $payment->payment_status = 'pending';

        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $filename = 'payment_slips/' . now()->format('YmdHis') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            Storage::disk('public')->put($filename, file_get_contents($file));
            $payment->payment_proof = $filename;
        }

        $payment->save();

        auth()->user()->notify(new \App\Notifications\PaymentUploaded($payment));

        return redirect()->route('dashboard')->with('success', '✅ อัปโหลดสลิปเรียบร้อยแล้ว รออนุมัติจากแอดมินค่ะ!');
    }

}
