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
        \Log::info('REQUEST DATA', $request->all());

        $request->validate([
            'installment_request_id' => 'required|integer',
            'amount_paid' => 'required|numeric',
            'pay_for_dates' => 'required|array',
            'slip' => 'nullable|image',
        ]);

        $installment = InstallmentRequest::findOrFail($request->installment_request_id);

        $imgPath = null;
        $imgHash = null;
        $qrData = null;

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

            // --- เรียก Python OCR ---
            $qrData = $this->readQrFromSlip(storage_path('app/' . $imgPath));
            \Log::info('Slip OCR', ['qrData' => $qrData]);
            if (!$qrData || !isset($qrData['account'])) {
                return response()->json(['success' => false, 'error' => 'อ่าน QR ไม่สำเร็จ'], 422);
            }

            // ตรวจสอบบัญชีบริษัท
            $companyAccounts = [
                '8651008116',
                '0021503541',
            ];
            $foundAcc = false;
            foreach ($companyAccounts as $acc) {
                if (strpos($qrData['account'], $acc) !== false) {
                    $foundAcc = true;
                    break;
                }
            }
            if (!$foundAcc) {
                return response()->json(['success' => false, 'error' => 'QR ในสลิปไม่ใช่เลขบัญชีบริษัท'], 422);
            }
        }

        // อัปเดตงวดที่เลือกจ่าย
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

        // ถ้ามี function updateTotalPenalty
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

    /**
     * เรียก Python OCR (อ่าน QR/เลขบัญชีจากสลิป)
     */
    private function readQrFromSlip($imgPath)
    {
        $output = null;
        $retval = null;
        $cmd = "python " . base_path('read_slip.py') . " " . escapeshellarg($imgPath);
        exec($cmd, $output, $retval);
        if ($retval !== 0) {
            \Log::error("read_slip.py failed", [
                'cmd' => $cmd,
                'retval' => $retval,
                'output' => $output
            ]);
            return null;
        }
        return json_decode(implode("\n", $output), true);
    }

}
