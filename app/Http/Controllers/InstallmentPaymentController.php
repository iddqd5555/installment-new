<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use App\Models\AdvancePayment;
use App\Models\BankAccount;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class InstallmentPaymentController extends Controller
{
    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slip' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'installment_request_id' => 'required|integer|exists:installment_requests,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('slip');
        $imgPath = $file->store('slips', 'public');
        $imgFullPath = storage_path('app/public/' . $imgPath);

        $cmd = 'python ' . escapeshellarg(base_path('read_slip.py')) . ' ' . escapeshellarg($imgFullPath);
        $output = shell_exec($cmd);
        Log::info("SLIP OCR OUTPUT: " . $output);
        $result = json_decode($output, true);

        $ocrAmount = floatval($result['amount'] ?? 0);
        if ($ocrAmount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'ระบบไม่สามารถอ่านยอดเงินจากสลิปได้ กรุณาอัปโหลดภาพใหม่ หรือรอแอดมินตรวจสอบ'
            ], 422);
        }

        $imgHash = $result['slip_hash'] ?? md5_file($file->getRealPath());
        $slipReference = $result['reference'] ?? null;

        // ======= ป้องกัน slip ซ้ำทั้งระบบ (ทุก user/contract/tables) =======
        $existsSlip = InstallmentPayment::where('slip_hash', $imgHash)
            ->orWhere('slip_reference', $slipReference)
            ->exists();

        $existsSlipAdvance = AdvancePayment::where('slip_hash', $imgHash)
            ->orWhere('slip_reference', $slipReference)
            ->exists();

        if ($existsSlip || $existsSlipAdvance) {
            return response()->json([
                'success' => false,
                'used' => true,
                'message' => '❌ สลิปนี้ถูกใช้แล้วในระบบ (อาจเป็น user/สัญญาอื่น) ห้ามอัพโหลดซ้ำ!',
            ], 422);
        }
        // ================================================================

        // === ตรวจสอบบัญชีบริษัท (ใช้เฉพาะบัญชีที่ is_active เท่านั้น) ===
        $accountsOCR = $result['accounts_found'] ?? [];
        $companyOCR = trim($result['company'] ?? "");
        $matchedAccount = null;
        $bankAccounts = BankAccount::where('is_active', 1)->get();
        $rawText = $result['raw_text'] ?? '';
        $allFragments = array_unique(array_merge($accountsOCR, [$result['account'] ?? '']));

        // fallback (ถ้า admin ยังไม่เคยตั้งค่าบัญชี)
        if ($bankAccounts->count() == 0) {
            $bankAccounts = collect([
                (object)[
                    'bank_name'      => 'กสิกรไทย',
                    'account_number' => '865-1-00811-6',
                    'account_name'   => 'บริษัท วิสดอม โกลด์ กรุ้ป จำกัด'
                ]
            ]);
        }

        foreach ($bankAccounts as $bank) {
            $accountDB = preg_replace('/[^0-9]/', '', $bank->account_number);
            $fragments = [];
            for ($len = min(10, strlen($accountDB)); $len >= 3; $len--) {
                for ($offset = 0; $offset <= strlen($accountDB) - $len; $offset++) {
                    $frag = substr($accountDB, $offset, $len);
                    $fragments[] = $frag;
                }
            }
            $found = false;
            foreach ($fragments as $frag) {
                if (!$frag) continue;
                foreach ($allFragments as $foundAcc) {
                    $accClean = preg_replace('/[^0-9]/', '', $foundAcc);
                    if ($frag === $accClean) {
                        $found = true;
                        break 2;
                    }
                }
                if (strpos($rawText, $frag) !== false) {
                    $found = true;
                    break;
                }
                if (!empty($result['qr_text']) && strpos($result['qr_text'], $frag) !== false) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                $normalize = function($str) {
                    return mb_strtolower(preg_replace('/[\s\.\,บจกบริษัทจำกัด]+/', '', $str));
                };
                $companyOCRNorm = $normalize($companyOCR);
                $companyDBNorm = $normalize($bank->account_name);
                similar_text($companyDBNorm, $companyOCRNorm, $perc);
                if ($perc >= 40 || empty($companyOCR)) {
                    $matchedAccount = $bank;
                    break;
                }
            }
        }
        if (!$matchedAccount) {
            Log::warning("[SLIP-OCR-MATCH] NO MATCH: accountsOCR=" . json_encode($accountsOCR) . ", companyOCR={$companyOCR}, rawText={$rawText}");
            return response()->json([
                'success' => false,
                'message' => 'เลขบัญชีปลายทางหรือชื่อบริษัทในสลิปไม่ตรงกับระบบ กรุณาตรวจสอบเลขบัญชีบริษัท หรือรอแอดมินตรวจสอบ'
            ], 422);
        }

        // ============ "หักยอดเงินตาม contract ที่เลือก" ================
        $userId = auth()->id();
        $user = auth()->user();
        $contractId = $request->input('installment_request_id');
        $installment = InstallmentRequest::where('user_id', $userId)
            ->where('id', $contractId)
            ->where('status', 'approved')
            ->firstOrFail();

        $remain = $ocrAmount;

        foreach ($installment->installmentPayments()
            ->where('status', 'pending')
            ->where('payment_due_date', '<=', Carbon::now())
            ->orderBy('payment_due_date')
            ->get() as $pay) {

            $due = $pay->amount - $pay->amount_paid;
            $payNow = min($due, $remain);
            $wasPaid = $pay->status === 'paid'; // เก็บสถานะก่อนหน้า

            $pay->amount_paid += $payNow;
            if ($pay->amount_paid >= $pay->amount) {
                $pay->status = 'paid';
                $pay->payment_status = 'paid';
            }
            // save slip ลง record แรกที่จ่าย
            if ($remain == $ocrAmount) {
                $pay->payment_proof = $imgPath;
                $pay->slip_hash = $imgHash;
                $pay->slip_reference = $slipReference;
                $pay->slip_qr_text = $result['qr_text'] ?? null;
                $pay->slip_ocr_json = json_encode($result, JSON_UNESCAPED_UNICODE);
            }
            $pay->save();

            // ==== เพิ่มแจ้งเตือนจริง ชำระค่างวดสำเร็จ ====
            if (!$wasPaid && $pay->status === 'paid') {
                $exists = Notification::where([
                    'user_id' => $installment->user_id,
                    'type' => Notification::TYPE_PAYMENT,
                    'title' => 'ชำระค่างวดสำเร็จ',
                    'message' => 'ชำระงวด ' . ($pay->payment_due_date ? Carbon::parse($pay->payment_due_date)->format('d/m/Y') : '-') .
                        ' จำนวน ' . number_format($pay->amount_paid, 2) . ' บาท',
                ])->exists();

                if (!$exists) {
                    Notification::create([
                        'user_id' => $installment->user_id,
                        'role' => 'user',
                        'type' => Notification::TYPE_PAYMENT,
                        'title' => 'ชำระค่างวดสำเร็จ',
                        'message' => 'ชำระงวด ' . ($pay->payment_due_date ? Carbon::parse($pay->payment_due_date)->format('d/m/Y') : '-') .
                            ' จำนวน ' . number_format($pay->amount_paid, 2) . ' บาท',
                        'data' => json_encode([
                            'installment_payment_id' => $pay->id
                        ]),
                    ]);
                }
            }
            // ==== จบเพิ่มแจ้งเตือนจริง ====

            Log::info('Installment paid', [
                'payment_id' => $pay->id,
                'installment_id' => $installment->id,
                'user_id' => $userId,
                'amount' => $payNow,
                'remain' => $remain,
            ]);
            $remain -= $payNow;
            if ($remain <= 0) break;
        }
        // update summary
        $installment->total_paid = $installment->installmentPayments()->where('status', 'paid')->sum('amount_paid');
        $installment->remaining_amount = $installment->total_installment_amount - $installment->total_paid;

        // เติมยอดเงินเกินใน advance_payment (เงินล่วงหน้าในงวดอนาคต)
        if ($remain > 0) {
            $installment->advance_payment = floatval($installment->advance_payment) + $remain;
            $installment->save();

            AdvancePayment::create([
                'installment_request_id' => $installment->id,
                'user_id' => $userId,
                'amount' => $remain,
                'slip_image' => $imgPath,
                'slip_hash' => $imgHash,
                'slip_reference' => $slipReference,
                'slip_ocr_json' => json_encode($result, JSON_UNESCAPED_UNICODE),
            ]);
            Log::info('Advance payment added', [
                'installment_id' => $installment->id,
                'user_id' => $userId,
                'amount' => $remain,
            ]);

            // === NEW: Auto หัก advance ที่เติมเข้าไปทันที
            $this->autoDeductFromAdvance($installment);
        } else {
            $installment->save();
        }

        Notification::create([
            'user_id' => null,
            'role' => 'admin',
            'type' => 'slip',
            'title' => 'ลูกค้าแนบสลิปใหม่',
            'message' => 'ลูกค้า ' . ($user->name ?? '-') . ' แนบสลิปโอนเงินจำนวน ' . number_format($ocrAmount, 2) . ' บาท',
            'data' => json_encode([
                'user_id' => $userId,
                'amount' => $ocrAmount,
                'slip_reference' => $slipReference,
                'imgPath' => $imgPath,
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'บันทึกสลิปและอัปเดตยอดสำเร็จ',
        ]);
    }

    private function autoDeductFromAdvance($installment)
    {
        $advance = $installment->advance_payment;
        if ($advance <= 0) return;

        $beforeAdvance = $advance;
        $payments = $installment->installmentPayments()
            ->where('status', 'pending')
            ->where('payment_due_date', '<=', Carbon::today())
            ->orderBy('payment_due_date', 'asc')
            ->get();

        foreach ($payments as $payment) {
            $remain = $payment->amount - $payment->amount_paid;
            if ($remain <= 0) continue;

            $pay = min($remain, $advance);
            $payment->amount_paid += $pay;

            if ($payment->amount_paid >= $payment->amount) {
                $payment->status = 'paid';
                $payment->payment_status = 'paid';
            }
            $payment->save();

            $advance -= $pay;
            if ($advance <= 0) break;
        }

        $installment->advance_payment = $advance;
        $installment->save();

        AdvancePayment::create([
            'installment_request_id' => $installment->id,
            'user_id' => $installment->user_id,
            'amount' => -($beforeAdvance - $advance),
            'slip_reference' => 'advance-auto-deduct',
        ]);

        Notification::create([
            'user_id' => $installment->user_id,
            'role' => 'user',
            'type' => 'advance_deducted',
            'title' => 'หักเงินล่วงหน้าอัตโนมัติ',
            'message' => 'ระบบได้หักเงินล่วงหน้าจำนวน '.number_format($beforeAdvance - $advance, 2).' บาท เรียบร้อยแล้ว',
            'data' => json_encode(['remaining_advance' => $advance]),
        ]);
    }

    public function allAdvancePayments(Request $request)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $adv = AdvancePayment::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function($pay) {
                return [
                    'amount' => $pay->amount,
                    'created_at' => $pay->created_at,
                    'slip_image' => $pay->slip_image,
                    'contract_id' => $pay->installment_request_id,
                ];
            })->values();

        return response()->json(['advance_payments' => $adv]);
    }

    public function history(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $installments = InstallmentRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->get();

        $installmentPayments = [];
        foreach ($installments as $contract) {
            $paidPayments = $contract->installmentPayments()
                ->whereIn('payment_status', ['paid'])
                ->orderBy('payment_due_date')
                ->get();

            foreach ($paidPayments as $pay) {
                $installmentPayments[] = [
                    'amount' => $pay->amount,
                    'amount_paid' => $pay->amount_paid,
                    'payment_status' => $pay->payment_status,
                    'payment_due_date' => $pay->payment_due_date,
                    'created_at' => $pay->created_at,
                    'payment_proof' => $pay->payment_proof,
                ];
            }
        }

        return response()->json([
            'installment_payments' => $installmentPayments,
        ]);
    }
}
