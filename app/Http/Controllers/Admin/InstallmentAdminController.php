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
use App\Notifications\PaymentStatusUpdated;
use Illuminate\Support\Facades\Cache;


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

    public function edit($id) {
        $installment = InstallmentRequest::findOrFail($id);
        $goldPrices = Cache::get('gold_prices_daily', [
            'ornament_sell' => 0, 
            'ornament_buy' => 0, 
            'ornament_buy_gram' => 0
        ]);

        // ✅ แปลง string เป็น float ก่อนส่งให้ view
        $goldPrices['ornament_sell'] = (float) str_replace(',', '', $goldPrices['ornament_sell']);
        $goldPrices['ornament_buy'] = (float) str_replace(',', '', $goldPrices['ornament_buy']);
        $goldPrices['ornament_buy_gram'] = (float) str_replace(',', '', $goldPrices['ornament_buy_gram']);

        return view('admin.installments.edit', compact('installment', 'goldPrices'));
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

    public function approve($id)
    {
        $request = InstallmentRequest::findOrFail($id);
        $request->status = 'approved';
        $request->save();

        return redirect()->route('installments.index')->with('success', 'อนุมัติคำขอเรียบร้อยแล้วค่ะ!');
    }

    // ปฏิเสธคำขอผ่อนทอง
    public function reject($id)
    {
        $request = InstallmentRequest::findOrFail($id);
        $request->status = 'rejected';
        $request->save();

        return redirect()->route('installments.index')->with('error', 'ปฏิเสธคำขอเรียบร้อยค่ะ!');
    }
    public function verify($id)
    {
        $installmentRequest = InstallmentRequest::findOrFail($id);

        $totalGoldPrice = $installmentRequest->approved_gold_price * $installmentRequest->gold_amount;
        $interestAmount = $totalGoldPrice * ($installmentRequest->interest_rate / 100);
        $totalWithInterest = $totalGoldPrice + $interestAmount;

        $installmentRequest->update([
            'status' => 'approved',
            'total_with_interest' => $totalWithInterest,
            'remaining_amount' => $totalWithInterest,
            'remaining_months' => $installmentRequest->installment_period,
            'next_payment_date' => now()->addMonth()->startOfMonth(), // ทุกวันที่ 1 ของเดือน
        ]);

        // สร้างตารางการชำระรายเดือน
        $monthlyPayment = $totalWithInterest / $installmentRequest->installment_period;

        for ($month = 1; $month <= $installmentRequest->installment_period; $month++) {
            InstallmentPayment::create([
                'installment_request_id' => $installmentRequest->id,
                'amount' => $monthlyPayment,
                'due_date' => now()->addMonths($month)->startOfMonth(),
                'status' => 'pending',
            ]);
        }

        return back()->with('success', 'อนุมัติและสร้างตารางผ่อนชำระสำเร็จ');
    }

    // ✅ ฟังก์ชันจัดการสถานะคำขอของ Guest
    public function updateGuestStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,checked,rejected',
        ]);

        $installmentRequest = InstallmentRequest::findOrFail($id);

        // ตรวจสอบว่าคำขอนี้มาจาก Guest จริงหรือไม่ (ไม่มี user_id)
        if ($installmentRequest->user_id !== null) {
            return redirect()->back()->with('error', '❌ คำขอนี้ไม่ใช่คำขอจาก Guest');
        }

        // อัปเดตสถานะของ Guest ตามที่เลือก
        $installmentRequest->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', '✅ อัปเดตสถานะของ Guest สำเร็จ!');
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

    public function payments()
    {
        $pendingPayments = InstallmentPayment::with('installmentRequest.user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('admin.payments.index', compact('pendingPayments'));
    }

    // ในฟังก์ชัน approvePayment
    public function approvePayment($paymentId)
    {
        $payment = InstallmentPayment::findOrFail($paymentId);
        $payment->status = 'approved';
        $payment->save();

        $installmentRequest = $payment->installmentRequest;

        $installmentRequest->total_paid = $installmentRequest->installmentPayments()->where('status', 'approved')->sum('amount_paid');
        
        //คำนวณยอดคงเหลือ
        $installmentRequest->remaining_amount = $installmentRequest->total_with_interest - $installmentRequest->total_paid;

        $currentMonth = now()->format('Y-m');
        $lastReducedMonth = optional($installmentRequest->last_month_reduced)->format('Y-m');

        //เช็คว่าเดือนนี้ลดหรือยัง
        if ($currentMonth !== $lastReducedMonth) {
            $installmentRequest->remaining_months -= 1;
            $installmentRequest->last_month_reduced = now();

            //อัปเดตวันชำระครั้งถัดไป
            $nextPayment = $installmentRequest->installmentPayments()
                ->where('status', 'pending')
                ->orderBy('payment_due_date', 'asc')
                ->first();

            if ($nextPayment) {
                $installmentRequest->due_date = $nextPayment->payment_due_date;
            }
        }

        $installmentRequest->save();

        return redirect()->back()->with('success', 'อนุมัติการชำระเงินสำเร็จ');
    }

    public function rejectPayment(Request $request, $paymentId)
    {
        $payment = InstallmentPayment::findOrFail($paymentId);

        $payment->status = 'rejected';
        $payment->payment_status = 'rejected';
        $payment->admin_notes = $request->admin_notes ?? null;
        $payment->save();

        $payment->installmentRequest->user->notify(new \App\Notifications\PaymentRejectedNotification($payment));

        return redirect()->back()->with('success', 'ปฏิเสธหลักฐานการชำระเงินสำเร็จ!');
    }

    // method อื่นๆ ที่คุณมีอยู่ก่อนหน้านี้ ให้เก็บไว้ทั้งหมด ไม่ต้องลบ

}
