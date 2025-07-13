<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class InstallmentPaymentController extends Controller
{
    /**
     * POST /api/installment/pay
     * ชำระเงินงวด/ค้าง/แนบสลิป/เลือกปิดวันไหน
     */
    public function pay(Request $request)
    {
        $request->validate([
            'installment_request_id' => 'required|integer',
            'amount_paid' => 'required|numeric',
            'pay_for_dates' => 'required|array',
            'slip' => 'nullable|image',
        ]);

        $installment = InstallmentRequest::findOrFail($request->installment_request_id);

        $imgPath = null;
        $imgHash = null;
        $qrText = null;
        if ($request->hasFile('slip')) {
            $file = $request->file('slip');
            $imgPath = $file->store('public/slips');
            $imgHash = md5_file($file->getRealPath());

            // ตรวจสอบ slip ซ้ำ
            $dup = InstallmentPayment::where('installment_request_id', $installment->id)
                ->where('slip_hash', $imgHash)
                ->first();
            if ($dup) {
                return response()->json(['success' => false, 'error' => 'สลิปนี้ถูกอัปโหลดไปแล้ว'], 409);
            }

            // ถ้าใช้ Python QR ให้ดึง text ตรงนี้ (comment ไว้ถ้าใช้จริง)
            // $qrText = $this->readQrFromSlip(storage_path('app/'.$imgPath));

            // ตัวอย่างตรวจบัญชีบริษัท
            // $companyAccounts = [
            //     '8651008116',
            //     '0021503541',
            // ];
            // $foundAcc = false;
            // if ($qrText) {
            //     $qrTextDigits = preg_replace('/\D/', '', $qrText);
            //     foreach ($companyAccounts as $acc) {
            //         if (strpos($qrTextDigits, $acc) !== false) {
            //             $foundAcc = true;
            //             break;
            //         }
            //     }
            // }
            // if ($qrText && !$foundAcc) {
            //     return response()->json(['success' => false, 'error' => 'QR ในสลิปไม่ใช่เลขบัญชีบริษัท'], 422);
            // }
        }

        // **[ ส่วนนี้สำคัญมาก ต้อง insert หรือ update InstallmentPayment ลง DB จริง ]**
        foreach ($request->pay_for_dates as $date) {
            $payment = InstallmentPayment::where('installment_request_id', $installment->id)
                ->whereDate('payment_due_date', $date)
                ->first();

            if ($payment) {
                $payment->amount_paid = $request->amount_paid;
                $payment->status = 'pending';
                $payment->payment_status = 'pending';
                if ($imgPath) {
                    $payment->payment_proof = str_replace('public/', '', $imgPath);
                    $payment->slip_hash = $imgHash;
                }
                $payment->save();
            }
        }

        // (option) สมมติว่ามี function updateTotalPenalty() ใน InstallmentRequest
        if (method_exists($installment, 'updateTotalPenalty')) {
            $installment->updateTotalPenalty();
            $installment->save();
        }

        return response()->json(['success' => true]);
    }

    public function overdue(Request $request)
    {
        $id = $request->query('installment_request_id');
        $installment = InstallmentRequest::findOrFail($id);

        $overdue = InstallmentPayment::where('installment_request_id', $installment->id)
            ->where('payment_status', '!=', 'paid')
            ->whereDate('payment_due_date', '<', now())
            ->orderBy('payment_due_date')
            ->get(['id','amount','amount_paid','payment_due_date','status']);

        return response()->json(['overdue' => $overdue]);
    }

    public function history(Request $request)
    {
        $id = $request->query('installment_request_id');
        $installment = InstallmentRequest::findOrFail($id);

        $payments = InstallmentPayment::where('installment_request_id', $installment->id)
            ->orderBy('payment_due_date')
            ->get([
                'id',
                'amount',
                'amount_paid',
                'payment_due_date',
                'status',
                'payment_status',
                'payment_proof'
            ]);

        return response()->json(['history' => $payments]);
    }
}
