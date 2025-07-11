<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use App\Models\PaymentQrLog;
use Illuminate\Support\Facades\Auth;

class InstallmentController extends Controller
{
    // 1. รายการคำขอผ่อนทอง (ของตัวเอง)
    public function index()
    {
        $requests = InstallmentRequest::where('user_id', Auth::id())
            ->orderByDesc('created_at')->get();

        return view('user.installments.index', compact('requests'));
    }

    // 2. แบบฟอร์มขอผ่อนทอง
    public function create()
    {
        return view('user.installments.create');
    }

    // 3. บันทึกคำขอผ่อนทอง (user submit)
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string',
            'price' => 'required|numeric',
            'installment_months' => 'required|integer',
            'product_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageName = time().'.'.$request->product_image->extension();
        $request->product_image->storeAs('public/products', $imageName);

        InstallmentRequest::create([
            'product_name' => $request->product_name,
            'price' => $request->price,
            'installment_months' => $request->installment_months,
            'product_image' => $imageName,
            'status' => 'pending',
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('user.installments.index')->with('success', 'ส่งคำขอผ่อนสำเร็จ! กรุณารอแอดมินตรวจสอบ');
    }

    // 4. ดูรายละเอียดคำขอ
    public function show($id)
    {
        $request = InstallmentRequest::where('user_id', Auth::id())->findOrFail($id);
        return view('user.installments.show', compact('request'));
    }

    // 5. (ไม่ต้องให้ user แก้ไข/ลบเองหลังจาก submit)
    // public function edit() ... (ตัดออก)
    // public function update() ... (ตัดออก)
    // public function destroy() ... (ตัดออก)

    // 6. Dashboard: แสดง “งวดผ่อน” (ของตัวเอง)
    public function dashboard()
    {
        $user = Auth::user();
        $payments = InstallmentPayment::whereHas('installmentRequest', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->orderBy('payment_due_date', 'asc') // <-- เปลี่ยนตรงนี้ให้ตรงกับชื่อจริงในตาราง!
        ->get();

        return view('user.dashboard', compact('user', 'payments'));
    }

    // 7. ดูประวัติ QR Payment ของตัวเอง
    public function qrHistory()
    {
        $user = Auth::user();
        $logs = PaymentQrLog::where('customer_id', $user->id)->orderByDesc('created_at')->get();

        return view('user.qr_history', compact('logs'));
    }

    // 8. สร้าง QR สำหรับงวดที่ค้าง (รอเชื่อมกับ KBank จริง)
    public function createQr($installmentPaymentId)
    {
        $user = Auth::user();
        $installment = InstallmentPayment::findOrFail($installmentPaymentId);

        return view('user.create_qr', compact('installment'));
    }
}
