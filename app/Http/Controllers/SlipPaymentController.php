<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Ocr\Ocr;
use App\Models\InstallmentPayment;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SlipPaymentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'slip' => 'required|image',
            'user_id' => 'nullable|integer',
            'installment_request_id' => 'nullable|integer',
        ]);

        $path = $request->file('slip')->store('slips');
        $ocr = new Ocr();
        $text = $ocr->scan(Storage::path($path));

        // ตรวจสอบเลขบัญชีจาก OCR ว่าตรงกับที่เปิดใช้งานหลังบ้านหรือไม่
        preg_match_all('/[0-9]{9,12}/', $text, $found_accounts);
        $ocrAccounts = collect($found_accounts[0] ?? [])->unique()->values();

        $activeBanks = BankAccount::where('is_active', 1)->get();
        $matchedBank = $activeBanks->first(function($b) use ($ocrAccounts) {
            return $ocrAccounts->contains($b->account_number);
        });

        // Parse ยอดเงิน/วันที่/Ref
        preg_match('/จำนวน(?:เงิน|เงินที่โอน)?[:\s]*([\d,\.]+) ?บาท?/u', $text, $amount);
        preg_match('/(?:วันที่|Date)[\s:]*([0-9]{1,2} [ก-ฮ.]+ [0-9]{2,4})/u', $text, $date);
        preg_match('/(?:เวลา|Time)[\s:]*([0-9]{1,2}:[0-9]{2,4})/u', $text, $time);
        preg_match('/(เลขที่รายการ|รหัสอ้างอิง|Ref No\.?)[:\s]*([A-Za-z0-9]+)/u', $text, $ref);

        $datetime = null;
        if (!empty($date[1]) && !empty($time[1])) {
            $datetime = trim($date[1]) . ' ' . trim($time[1]);
        }

        // เช็คซ้ำก่อน insert
        $exists = InstallmentPayment::where('amount_paid', $amount[1] ?? 0)
            ->where('payment_due_date', $datetime ? Carbon::parse($datetime)->format('Y-m-d H:i:00') : null)
            ->where('ref', $ref[2] ?? null)
            ->first();

        if (!$exists) {
            $pay = InstallmentPayment::create([
                'installment_request_id' => $request->input('installment_request_id', null),
                'amount' => $amount[1] ?? 0,
                'amount_paid' => $amount[1] ?? 0,
                'payment_status' => 'pending',
                'status' => 'pending',
                'payment_proof' => $path,
                'payment_due_date' => $datetime ? Carbon::parse($datetime)->format('Y-m-d H:i:00') : now(),
                'admin_notes' => 'OCR: '.($ref[2] ?? ''),
                'ref' => $ref[2] ?? null,
                // สามารถเพิ่ม 'bank_account_id' => $matchedBank?->id, หาก migration เพิ่มไว้แล้ว
            ]);
        } else {
            $pay = $exists;
        }

        return response()->json([
            'success' => true,
            'raw_text' => $text,
            'amount' => $amount[1] ?? null,
            'datetime' => $datetime,
            'ref' => $ref[2] ?? null,
            'installment_payment_id' => $pay->id,
            'img_path' => $path,
            'matched_bank' => $matchedBank ? [
                'id' => $matchedBank->id,
                'bank_name' => $matchedBank->bank_name,
                'account_number' => $matchedBank->account_number,
                'account_name' => $matchedBank->account_name,
            ] : null,
            'ocr_accounts' => $ocrAccounts,
        ]);
    }
}
