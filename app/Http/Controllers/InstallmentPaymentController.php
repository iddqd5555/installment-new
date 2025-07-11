<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class InstallmentPaymentController extends Controller
{
    public function pay(Request $request)
    {
        $request->validate([
            'installment_request_id' => 'required|integer',
            'amount_paid' => 'required|numeric',
            'pay_for_dates' => 'required|array',
            'slip' => 'nullable|image|max:5120',
        ]);

        $installment = InstallmentRequest::findOrFail($request->installment_request_id);

        $imgPath = null;
        $imgHash = null;
        $qrText = null;
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
            $companyAcc = '8651008116';
            $foundAcc = $qrText && str_contains(preg_replace('/\D/', '', $qrText), $companyAcc);

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
                $payment->payment_status = ($payment->amount_paid >= $payment->amount) ? 'pending' : 'pending';
                $payment->status = ($payment->amount_paid >= $payment->amount) ? 'pending' : 'pending';
                $payment->payment_proof = $imgPath;
                $payment->slip_hash = $imgHash;
                $payment->slip_qr_text = $qrText;
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
        $process = new Process(['python', base_path('read_qr.py'), $filePath]);
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
