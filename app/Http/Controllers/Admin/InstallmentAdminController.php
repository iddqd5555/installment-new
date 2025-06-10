<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use Illuminate\Support\Facades\Auth;
use App\Notifications\InstallmentApproved;
use App\Notifications\InstallmentRequestStatusNotification;
use App\Notifications\InstallmentDueReminderNotification;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\InstallmentPayment;


class InstallmentAdminController extends Controller
{
    public function index()
    {
        $requests = InstallmentRequest::with('user')->latest()->get();
        return view('admin.installments.index', compact('requests'));
    }

    public function create()
    {
        return view('admin.installments.create');
    }

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
            'status' => 'approved',
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.installments.index')->with('success', 'เพิ่มข้อมูลสินค้าสำเร็จ!');
    }

    public function edit($id)
    {
        $installment = InstallmentRequest::findOrFail($id);
        return view('admin.installments.edit', compact('installment'));
    }

    // รวมฟังก์ชัน update ที่ซ้ำกันแล้ว ✅
    public function update(Request $request, $id)
    {
        $request->validate([
            'gold_amount' => 'required|numeric',
            'approved_gold_price' => 'required|numeric',
            'installment_period' => 'required|integer',
            'status' => 'required|string|in:pending,approved,rejected',
        ]);

        $installment = InstallmentRequest::findOrFail($id);

        $installment->update([
            'gold_amount' => $request->gold_amount,
            'approved_gold_price' => $request->approved_gold_price,
            'installment_period' => $request->installment_period,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.installments.index')->with('success', 'อัปเดตข้อมูลสำเร็จ');
    }


    public function destroy(string $id)
    {
        $installment = InstallmentRequest::findOrFail($id);
        $installment->delete();

        return redirect()->route('admin.installments.index')->with('success', 'ลบข้อมูลสำเร็จ!');
    }

    public function pendingUsers()
    {
        $users = User::where('identity_verification_status', 'pending')->get();
        return view('admin.users.pending', compact('users'));
    }

    public function verifyUser($id)
    {
        $user = User::findOrFail($id);
        $user->identity_verification_status = 'verified';
        $user->save();

        return redirect()->back()->with('success', 'ตรวจสอบผู้ใช้สำเร็จ');
    }

    public function pendingRequests()
    {
        $requests = InstallmentRequest::where('status', 'pending')
            ->where('gold_type', 'ทองรูปพรรณ')
            ->latest()
            ->get();

        return view('admin.requests.pending', compact('requests'));
    }

    public function verify($id)
    {
        $installmentRequest = InstallmentRequest::findOrFail($id);

        $installmentRequest->update([
            'status' => 'approved',
            'approved_gold_price' => $this->fetchGoldPriceFromApi(),
        ]);

        $startDate = Carbon::now();

        // สร้างตารางการผ่อนชำระล่วงหน้า
        for ($month = 1; $month <= $installmentRequest->installment_period; $month++) {
            InstallmentPayment::create([
                'installment_request_id' => $installmentRequest->id,
                'amount' => 0,
                'fine' => 0,
                'due_date' => $startDate->copy()->addMonths($month),
                'payment_date' => null,
            ]);
        }

        return redirect()->back()->with('success', 'อนุมัติคำขอและสร้างตารางผ่อนชำระเรียบร้อยแล้ว');
    }

    private function fetchGoldPriceFromApi()
    {
        // Logic การดึงราคาทองจาก API ที่คุณใช้อยู่
        return 52150.00; // สมมติราคานี้มาจาก API
    }

    // Method แจ้งเตือนใกล้ครบกำหนดชำระ (เรียกใช้จาก Cron Job หรือ Schedule)
    public function notifyDuePayments()
    {
        $requests = InstallmentRequest::whereDate('next_payment_date', now()->addDays(3))
                    ->where('status', 'approved')
                    ->get();

        foreach ($requests as $request) {
            $request->user->notify(new InstallmentDueReminderNotification($request));
        }

        return 'แจ้งเตือนการชำระสำเร็จแล้ว!';
    }

    // ใน method approvePayment()
    public function approvePayment($paymentId)
    {
        $payment = InstallmentPayment::findOrFail($paymentId);
        $lateDays = Carbon::now()->diffInDays($payment->due_date, false);
        if ($lateDays < 0) {
            $payment->fine = abs($lateDays) * 50;
        }

        $payment->payment_status = 'approved';
        $payment->payment_date = Carbon::now();
        $payment->save();

        // ✅ เพิ่มบรรทัดนี้เพื่อแจ้งเตือน
        $payment->installmentRequest->user->notify(new \App\Notifications\PaymentApprovedNotification($payment));

        return redirect()->back()->with('success', 'อนุมัติการชำระเงินเรียบร้อยแล้ว!');
    }

    // ใน method rejectPayment()
    public function rejectPayment(Request $request, $paymentId)
    {
        $payment = InstallmentPayment::findOrFail($paymentId);
        $payment->payment_status = 'rejected';
        $payment->admin_notes = $request->admin_notes;
        $payment->save();

        // ✅ เพิ่มบรรทัดนี้เพื่อแจ้งเตือน
        $payment->installmentRequest->user->notify(new \App\Notifications\PaymentRejectedNotification($payment));

        return redirect()->back()->with('success', 'ปฏิเสธหลักฐานการชำระเงินสำเร็จ!');
    }

    public function approve($id)
    {
        $installmentRequest = InstallmentRequest::findOrFail($id);

        $response = Http::get('API_URL_HERE');
        $goldPrice = $response->successful()
                        ? $response->json()['gold_price']
                        : null;

        if ($goldPrice) {
            $installmentRequest->update([
                'status' => 'approved',
                'approved_gold_price' => $goldPrice,
            ]);

            $installmentRequest->user->notify(new InstallmentRequestStatusNotification($installmentRequest));

            return redirect()->back()->with('success', 'อนุมัติสำเร็จ ราคาทองถูกเก็บแล้ว!');
        } else {
            return redirect()->back()->with('error', 'ดึงราคาทองไม่สำเร็จ ลองใหม่อีกครั้งค่ะ');
        }
    }

    public function payments()
    {
        $payments = \App\Models\InstallmentPayment::where('payment_status', 'pending')->latest()->get();
        return view('admin.payments.index', compact('payments'));
    }
}
