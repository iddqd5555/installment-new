<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;

class InstallmentPaymentController extends Controller
{
    /**
     * POST /api/installment/pay (ออโต้ approve ทั้งเต็มงวดและบางส่วน)
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
        $foundAcc = false;

        if ($request->hasFile('slip')) {
            $file = $request->file('slip');
            $imgPath = $file->store('slips');
            $imgHash = md5_file($file->getRealPath());

            // กันอัพสลิปซ้ำ
            $dup = InstallmentPayment::where('installment_request_id', $installment->id)
                ->where('slip_hash', $imgHash)
                ->first();
            if ($dup) {
                return response()->json(['success' => false, 'error' => 'สลิปนี้ถูกอัปโหลดไปแล้ว'], 409);
            }

            // อ่าน QR
            $qrText = $this->readQrFromSlip(storage_path('app/' . $imgPath));

            // เลขบัญชีบริษัทที่ยอมรับ
            $companyAccounts = ['8651008116', '0021503541'];

            if ($qrText) {
                $qrTextDigits = preg_replace('/\D/', '', $qrText);
                foreach ($companyAccounts as $acc) {
                    if (strpos($qrTextDigits, $acc) !== false) {
                        $foundAcc = true;
                        break;
                    }
                }
            }

            if ($qrText && !$foundAcc) {
                return response()->json(['success' => false, 'error' => 'QR ในสลิปไม่ใช่เลขบัญชีบริษัท'], 422);
            }
        }

        $totalPaid = $request->amount_paid;
        $dates = $request->pay_for_dates;

        foreach ($dates as $date) {
            $payment = InstallmentPayment::where('installment_request_id', $installment->id)
                ->whereDate('payment_due_date', $date)
                ->first();

            if ($payment && $payment->status !== 'paid') {
                $pay = min($payment->amount - $payment->amount_paid, $totalPaid);
                $payment->amount_paid += $pay;
                $payment->payment_proof = $imgPath;
                $payment->slip_hash = $imgHash;
                $payment->slip_qr_text = $qrText;

                // แก้ logic ใหม่: auto approve ทันทีถ้า QR ถูก ไม่ว่าจ่ายเต็มหรือบางส่วน
                if ($foundAcc) {
                    if ($payment->amount_paid >= $payment->amount) {
                        $payment->status = 'approved';
                        $payment->payment_status = 'paid';
                        $payment->paid_at = now();
                    } else {
                        $payment->status = 'partial_paid'; // สถานะใหม่: จ่ายบางส่วน
                        $payment->payment_status = 'partial';
                    }
                } else {
                    $payment->status = 'pending';
                    $payment->payment_status = 'pending';
                }

                $payment->save();
                $totalPaid -= $pay;
                if ($totalPaid <= 0) break;
            }
        }

        if (method_exists($installment, 'updateTotalPenalty')) {
            $installment->updateTotalPenalty();
            $installment->save();
        }

        return response()->json(['success' => true]);
    }

    private function readQrFromSlip($filePath)
    {
        $process = new \Symfony\Component\Process\Process(['python', base_path('read_qr.py'), $filePath]);
        $process->run();

        if (!$process->isSuccessful()) {
            return null;
        }

        return trim($process->getOutput());
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
            ->get(['id','amount','amount_paid','payment_due_date','status','payment_status','payment_proof','slip_qr_text']);

        return response()->json(['history' => $payments]);
    }
}
