<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use Illuminate\Support\Facades\Auth;

class InstallmentController extends Controller
{
    public function create()
    {
        return view('installments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string',
            'product_price' => 'required|numeric',
            'installment_months' => 'required|integer|min:1',
            'id_card_image' => 'required|image|max:2048'
        ]);

        $imagePath = $request->file('id_card_image')->store('id_cards', 'public');

        InstallmentRequest::create([
            'user_id' => Auth::id(),
            'product_name' => $request->product_name,
            'product_price' => $request->product_price,
            'installment_months' => $request->installment_months,
            'id_card_image' => $imagePath,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'สมัครผ่อนสำเร็จ รอการอนุมัติค่ะ!');
    }

    // InstallmentController.php (แก้ไข)
    public function uploadSlip(Request $request, $installmentRequestId)
    {
        $installmentRequest = InstallmentRequest::findOrFail($installmentRequestId);
        $today = now()->toDateString();

        $paidToday = $installmentRequest->payments()
                        ->whereDate('payment_due_date', $today)
                        ->where('status', 'approved')
                        ->sum('amount_paid');

        $dueToday = max($installmentRequest->daily_payment_amount - $paidToday, 0);

        $request->validate([
            'amount_paid' => 'required|numeric|max:' . $dueToday,
            'payment_proof' => 'required|image|max:2048'
        ]);

        $filePath = $request->file('payment_proof')->store('payment-slips', 'public');

        InstallmentPayment::create([
            'installment_request_id' => $installmentRequestId,
            'amount_paid' => $request->amount_paid,
            'payment_proof' => $filePath,
            'status' => 'pending',
            'payment_due_date' => now(),
        ]);

        return back()->with('success', 'อัปโหลดสลิปสำเร็จแล้วค่ะ');
    }

}
