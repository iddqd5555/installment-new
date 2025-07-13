<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class InstallmentPaymentController extends Controller
{
    public function pay(Request $request)
    {
        \Log::info('REQUEST DATA', $request->all());

        $request->validate([
            'installment_request_id' => 'required|integer',
            'amount_paid' => 'required|numeric',
            'pay_for_dates' => 'required|array',
            'pay_for_dates.*' => 'date',
            'slip' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $installment = InstallmentRequest::findOrFail($request->installment_request_id);

        $imgPath = null;
        $imgHash = null;

        if ($request->hasFile('slip')) {
            $file = $request->file('slip');
            $imgPath = $file->store('public/slips');
            $imgHash = md5_file($file->getRealPath());

            // ตรวจสอบสลิปซ้ำ
            $dup = InstallmentPayment::where('installment_request_id', $installment->id)
                ->where('slip_hash', $imgHash)
                ->first();
            if ($dup) {
                return response()->json(['success' => false, 'error' => 'สลิปนี้ถูกอัปโหลดไปแล้ว'], 409);
            }

            // เรียก Python OCR (เปิดใช้งานจริง)
            $qrData = $this->readQrFromSlip(storage_path('app/' . $imgPath));
            \Log::info('OCR DATA', $qrData);

            if (!$qrData || empty($qrData['account'])) {
                return response()->json(['success' => false, 'error' => 'อ่าน QR หรือเลขบัญชีไม่ได้'], 422);
            }

            // ตรวจสอบบัญชีบริษัทจาก OCR
            $companyAccounts = ['8651008116', '0021503541'];
            $foundAcc = false;
            foreach ($companyAccounts as $acc) {
                if (strpos(preg_replace('/\D/', '', $qrData['account']), $acc) !== false) {
                    $foundAcc = true;
                    break;
                }
            }
            if (!$foundAcc) {
                return response()->json(['success' => false, 'error' => 'บัญชีปลายทางในสลิปไม่ถูกต้อง'], 422);
            }
        }

        // บันทึกข้อมูลชำระเงินแต่ละงวด
        foreach ($request->pay_for_dates as $date) {
            InstallmentPayment::updateOrCreate(
                ['installment_request_id' => $installment->id, 'payment_due_date' => $date],
                [
                    'amount_paid' => $request->amount_paid,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'payment_proof' => str_replace('public/', '', $imgPath),
                    'slip_hash' => $imgHash,
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    private function readQrFromSlip($imgPath)
    {
        $output = null;
        $retval = null;
        $cmd = "python " . base_path('read_slip.py') . " " . escapeshellarg($imgPath);
        exec($cmd, $output, $retval);
        if ($retval !== 0) {
            \Log::error("read_slip.py error", [
                'cmd' => $cmd,
                'retval' => $retval,
                'output' => $output
            ]);
            return null;
        }
        return json_decode(implode("\n", $output), true);
    }

    public function overdue(Request $request)
    {
        $installment = InstallmentRequest::findOrFail($request->installment_request_id);

        $overdue = InstallmentPayment::where('installment_request_id', $installment->id)
            ->where('payment_status', '!=', 'paid')
            ->whereDate('payment_due_date', '<', now())
            ->orderBy('payment_due_date')
            ->get(['id', 'amount', 'amount_paid', 'payment_due_date', 'status']);

        return response()->json(['overdue' => $overdue]);
    }

    public function history(Request $request)
    {
        $installment = InstallmentRequest::findOrFail($request->installment_request_id);

        $payments = InstallmentPayment::where('installment_request_id', $installment->id)
            ->orderBy('payment_due_date')
            ->get(['id', 'amount', 'amount_paid', 'payment_due_date', 'status', 'payment_status', 'payment_proof']);

        return response()->json(['history' => $payments]);
    }
}
