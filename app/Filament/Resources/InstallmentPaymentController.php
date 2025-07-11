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
        if ($request->hasFile('slip')) {
            $file = $request->file('slip');
            $imgPath = $file->store('slips');
            $imgHash = md5_file($file->getRealPath());

            $dup = InstallmentPayment::where('installment_request_id', $installment->id)
                ->where('slip_hash', $imgHash)
                ->first();
            if ($dup) {
                return response()->json(['success' => false, 'error' => 'สลิปนี้ถูกอัปโหลดไปแล้ว'], 409);
            }

            // --- อ่าน QR ผ่าน Python ---
            $qrText = $this->readQrFromSlip(storage_path('app/'.$imgPath));

            // ===== แก้ตรงนี้ =====
            // เพิ่ม array สำหรับ whitelist เลขบัญชีบริษัท
            $companyAccounts = [
                '8651008116', // เดิม: 865-1-00811-6 (บัญชีบริษัทหลัก)
                '0021503541', // ใหม่: 002-1-503541 (กสิกร สุรเชษฐ์ หงษ์ทอง)
                // เพิ่มบัญชีอื่นที่อนุมัติได้ในอนาคต
            ];
            $foundAcc = false;
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

        // (option) สมมติว่ามี function updateTotalPenalty() ใน InstallmentRequest
        if (method_exists($installment, 'updateTotalPenalty')) {
            $installment->updateTotalPenalty();
            $installment->save();
        }

        return response()->json(['success' => true]);
    }

    /**
     * GET /api/installment/overdue?installment_request_id=...
     * ดึงรายการยอดค้าง/ดอกเบี้ย/วันไหนบ้าง
     */
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

    /**
     * GET /api/installment/history?installment_request_id=...
     * ประวัติการจ่าย
     */
    public function history(Request $request)
    {
        $id = $request->query('installment_request_id');
        $installment = InstallmentRequest::findOrFail($id);

        $payments = InstallmentPayment::where('installment_request_id', $installment->id)
            ->orderBy('payment_due_date')
            ->get(['id','amount','amount_paid','payment_due_date','status','payment_status','payment_proof']);

        return response()->json(['history' => $payments]);
    }
}
