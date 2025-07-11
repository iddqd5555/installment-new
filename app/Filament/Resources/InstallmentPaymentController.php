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
            $imgPath = $request->file('slip')->store('slips');
        }

        $totalPaid = $request->amount_paid;
        $dates = $request->pay_for_dates; // array เช่น ['2025-07-09', '2025-07-10']

        foreach ($dates as $date) {
            $payment = InstallmentPayment::where('installment_request_id', $installment->id)
                ->whereDate('payment_due_date', $date)
                ->first();

            if ($payment && $payment->status !== 'paid') {
                $pay = min($payment->amount, $totalPaid);
                $payment->amount_paid += $pay;
                $payment->payment_status = ($payment->amount_paid >= $payment->amount) ? 'paid' : 'pending';
                $payment->status = ($payment->amount_paid >= $payment->amount) ? 'approved' : 'pending';
                $payment->payment_proof = $imgPath;
                $payment->save();

                $totalPaid -= $pay;
                if ($totalPaid <= 0) break;
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
