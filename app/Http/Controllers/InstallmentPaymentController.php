<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\DB;

class InstallmentPaymentController extends Controller
{
    /**
     * POST /api/installment/pay (Auto approve ด้วย OCR สลิป slip, กันสลิปซ้ำ)
     */
    public function pay(Request $request)
    {
        $request->validate([
            'installment_request_id' => 'required|integer',
            'amount_paid' => 'required|numeric',
            'pay_for_dates' => 'required|array',
            'slip' => 'required|file|mimes:jpeg,png,pdf',
        ]);

        DB::beginTransaction();
        try {
            $installment = InstallmentRequest::findOrFail($request->installment_request_id);

            $imgPath = $request->file('slip')->store('slips', 'public');
            $imgHash = md5_file($request->file('slip')->getRealPath());

            // === OCR ด้วย Python (read_slip.py) ===
            $output = [];
            $return_var = 0;
            $python = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'python' : 'python3'; // windows=python, linux=python3
            $script = base_path('read_slip.py');
            exec("$python $script " . escapeshellarg(storage_path('app/public/' . $imgPath)), $output, $return_var);
            $ocrData = json_decode(implode('', $output), true);

            // === เช็ค slip reference ห้ามซ้ำ ===
            $reference = $ocrData['reference'] ?? null;
            if ($reference && InstallmentPayment::where('slip_reference', $reference)->exists()) {
                return response()->json(['success' => false, 'error' => 'รหัสอ้างอิงนี้ถูกใช้ไปแล้ว (สลิปซ้ำ)'], 409);
            }

            // === ต้องเจอเลขบัญชีบริษัทหรือ x-8116 (ดัก slip ทุกแบงก์) ===
            $accountOk = false;
            $companyAccounts = ['8651008116', 'x-8116', '8116'];
            foreach ($companyAccounts as $acc) {
                if (isset($ocrData['account']) && strpos($ocrData['account'], $acc) !== false) {
                    $accountOk = true;
                    break;
                }
            }
            $companyOk = isset($ocrData['company']) && str_contains($ocrData['company'], 'วิสดอม');
            if (!$accountOk || !$companyOk) {
                return response()->json(['success' => false, 'error' => 'บัญชีปลายทางไม่ใช่บริษัท กรุณาตรวจสอบสลิปอีกครั้ง'], 422);
            }

            // ===== ดึงจำนวนเงินจาก OCR ถ้าเจอ (priority) =====
            $amountPaid = floatval($ocrData['amount'] ?? $request->amount_paid);
            $totalPaid = $amountPaid;
            $dates = $request->pay_for_dates;

            foreach ($dates as $date) {
                $payment = InstallmentPayment::where('installment_request_id', $installment->id)
                    ->whereDate('payment_due_date', $date)
                    ->first();

                if ($payment && !in_array($payment->status, ['paid', 'approved'])) {
                    $pay = min($payment->amount - $payment->amount_paid, $totalPaid);
                    $payment->amount_paid += $pay;
                    $payment->payment_proof = $imgPath;
                    $payment->slip_hash = $imgHash;
                    $payment->slip_reference = $reference;
                    $payment->slip_ocr_json = json_encode($ocrData, JSON_UNESCAPED_UNICODE);

                    if ($payment->amount_paid >= $payment->amount) {
                        $payment->status = 'approved';
                        $payment->payment_status = 'paid';
                        $payment->paid_at = now();
                    } else {
                        $payment->status = 'partial_paid';
                        $payment->payment_status = 'partial';
                    }

                    $payment->save();
                    $totalPaid -= $pay;
                    if ($totalPaid <= 0) break;
                }
            }

            // Update total_paid & remaining_amount
            $installment->total_paid = InstallmentPayment::where('installment_request_id', $installment->id)->sum('amount_paid');
            $installment->remaining_amount = InstallmentPayment::where('installment_request_id', $installment->id)
                ->where('status', '!=', 'approved')->sum(DB::raw('amount - amount_paid'));
            if (method_exists($installment, 'updateTotalPenalty')) {
                $installment->updateTotalPenalty();
            }
            $installment->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("InstallmentPayment pay error: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
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
            ->get(['id','amount','amount_paid','payment_due_date','status','payment_status','payment_proof','slip_reference','slip_ocr_json']);

        return response()->json(['history' => $payments]);
    }
}
