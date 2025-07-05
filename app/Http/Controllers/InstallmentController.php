<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InstallmentController extends Controller
{
    // ดึงข้อมูล installments ทั้งหมดของผู้ใช้ที่ล็อกอิน
    // แสดงรายการสัญญาสินเชื่อทั้งหมดของผู้ใช้งาน
    public function index()
    {
        $user = Auth::user();

        $installments = InstallmentRequest::where('user_id', $user->id)
            ->select('id', 'total_installment_amount', 'status', 'created_at')
            ->get();

        return response()->json($installments);
    }

    // แสดงรายละเอียดสัญญาสินเชื่อ (ละเอียดครบทุกข้อมูล)
    public function show($id)
    {
        $user = Auth::user();
        $installment = InstallmentRequest::with('payments')
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail([
                'id', 'contract_number', 'payment_number', 'product_name', 'product_price',
                'total_installment_amount', 'daily_payment_amount', 'installment_period',
                'start_date', 'status', 'latitude', 'longitude', 'document_image'
            ]);

        return response()->json($installment);
    }

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

    public function uploadDocuments(Request $request, $id)
    {
        $request->validate([
            'document_image' => 'required|image|max:2048',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $installment = InstallmentRequest::findOrFail($id);

        // อัปโหลดภาพและเก็บ path
        $documentPath = $request->file('document_image')->store('installment_documents', 'public');

        // บันทึกข้อมูลลงฐานข้อมูล (เพิ่มฟิลด์ใหม่ก่อนถ้ายังไม่มี)
        $installment->document_image = $documentPath;
        $installment->latitude = $request->latitude;
        $installment->longitude = $request->longitude;
        $installment->save();

        return response()->json(['message' => 'อัปโหลดเอกสารและตำแหน่งสำเร็จแล้ว'], 200);
    }
}
