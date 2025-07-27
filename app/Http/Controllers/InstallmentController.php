<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class InstallmentController extends Controller
{
    // Dashboard & List ผ่อนทั้งหมดของ user (logic แบบใหม่)
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->setTime(9,0,0);

        $installments = InstallmentRequest::with(['installmentPayments' => function($q) {
            $q->orderBy('payment_due_date', 'asc');
        }])
        ->where('user_id', $user->id)
        ->orderByDesc('id')
        ->get();

        $result = $installments->map(function($item) use ($today) {
            $payments = $item->installmentPayments;

            $advancePayment = floatval($item->advance_payment);

            $pendingPayments = $payments->filter(function($p) use ($today) {
                return Carbon::parse($p->payment_due_date)->lte($today)
                    && $p->status == 'pending'
                    && floatval($p->amount_paid ?? 0) < floatval($p->amount ?? 0);
            });

            $overdueCount = $pendingPayments->count();
            $outstanding = $pendingPayments->sum(function($p) {
                return floatval($p->amount ?? 0) - floatval($p->amount_paid ?? 0);
            });

            $realDue = $outstanding;

            $dailyPenalty = floatval($item->daily_penalty ?? 0);
            $overdueBills = $pendingPayments->filter(function($p) use ($today) {
                return Carbon::parse($p->payment_due_date)->lt($today);
            });
            $totalPenalty = $overdueBills->count() * $dailyPenalty;

            $nextPayment = $payments->first(function($p) use ($today) {
                return Carbon::parse($p->payment_due_date)->gt($today) && $p->status == 'pending';
            });
            $nextPaymentDate = $nextPayment ? $nextPayment->payment_due_date : '-';

            $totalPaid = $payments->where('status', 'paid')->sum(function($p) {
                return floatval($p->amount_paid ?? 0);
            });

            $totalInstallmentAmount = $payments->sum(function($p) {
                return floatval($p->amount ?? 0);
            });

            $daysPassed = 0;
            $startDate = $item->start_date ? Carbon::parse($item->start_date) : ($item->created_at ? Carbon::parse($item->created_at) : null);
            if ($startDate && $today->greaterThanOrEqualTo($startDate)) {
                $daysPassed = $startDate->diffInDays($today) + 1;
            }

            $lastPayment = $payments->last();
            $endDate = $lastPayment ? $lastPayment->payment_due_date : ($item->start_date ?? '-');

            return [
                'id' => $item->id,
                'contract_number' => $item->contract_number,
                'gold_amount' => floatval($item->gold_amount),
                'installment_period' => intval($item->installment_period ?? 0),
                'daily_payment_amount' => floatval($item->daily_payment_amount),
                'total_installment_amount' => $totalInstallmentAmount,
                'total_paid' => floatval($totalPaid),
                'outstanding' => floatval($outstanding),
                'overdue_count' => $overdueCount,
                'due_today' => floatval($realDue + $totalPenalty),
                'advance_payment' => floatval($advancePayment),
                'total_penalty' => floatval($totalPenalty),
                'next_payment_date' => $nextPaymentDate,
                'start_date' => $item->start_date,
                'end_date' => $endDate,
                'status' => $item->status,
                'days_passed' => $daysPassed,
                'installment_payments' => $payments->map(function($p) {
                    return [
                        'id' => $p->id,
                        'amount' => floatval($p->amount ?? 0),
                        'amount_paid' => floatval($p->amount_paid ?? 0),
                        'status' => $p->status,
                        'payment_due_date' => $p->payment_due_date,
                        'payment_status' => $p->payment_status,
                        'ref' => $p->ref ?? null,
                    ];
                })->values(),
            ];
        });

        return response()->json($result);
    }

    // รายละเอียดสัญญา
    public function show($id)
    {
        $user = Auth::user();
        $installment = InstallmentRequest::with(['installmentPayments' => function($q) {
            $q->orderBy('payment_due_date', 'asc');
        }])
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail([
                'id', 'contract_number', 'payment_number', 'product_name', 'product_price',
                'total_installment_amount', 'daily_payment_amount', 'installment_period',
                'start_date', 'status', 'latitude', 'longitude', 'document_image'
            ]);

        $installment->installment_payments = $installment->installmentPayments;
        unset($installment->installmentPayments);

        return response()->json($installment);
    }

    // สมัครสัญญา
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string',
            'gold_amount' => 'required|numeric',
            'approved_gold_price' => 'required|numeric',
            'installment_period' => 'required|integer|in:30,45,60',
            'start_date' => 'required|date',
            'id_card_image' => 'required|image|max:2048'
        ]);

        $imagePath = $request->file('id_card_image')->store('id_cards', 'public');

        $installment = InstallmentRequest::create([
            'user_id' => Auth::id(),
            'product_name' => $request->product_name,
            'gold_amount' => $request->gold_amount,
            'approved_gold_price' => $request->approved_gold_price,
            'installment_period' => $request->installment_period,
            'start_date' => $request->start_date,
            'id_card_image' => $imagePath,
            'status' => 'pending'
        ]);

        $installment->daily_payment_amount = round(($installment->gold_amount * $installment->approved_gold_price * $installment->interest_rate_factor) / $installment->installment_period, 2);
        $installment->total_with_interest = round($installment->daily_payment_amount * $installment->installment_period, 2);
        $installment->initial_payment = round($installment->daily_payment_amount * [30=>2,45=>3,60=>4][$installment->installment_period], 2);
        $installment->save();

        $installment->generatePayments();

        return response()->json(['message' => 'สมัครผ่อนสำเร็จ รอการอนุมัติค่ะ!'], 201);
    }

    // อัปโหลดสลิป (เติมเงินเข้ากระเป๋าสัญญา/หรือผ่อนงวด)
    public function pay(Request $request)
    {
        $request->validate([
            'slip' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // Save slip file
        $file = $request->file('slip');
        $imgPath = $file->store('slips', 'public');
        $imgFullPath = storage_path('app/public/' . $imgPath);

        // Run OCR+QR script
        $cmd = 'python ' . escapeshellarg(base_path('read_slip.py')) . ' ' . escapeshellarg($imgFullPath);
        $output = shell_exec($cmd);
        $result = json_decode($output, true);

        $ocrAmount = floatval($result['amount'] ?? 0);
        if ($ocrAmount <= 0) {
            return response()->json(['message' => 'ไม่สามารถอ่านยอดเงินจากสลิป รอแอดมินตรวจสอบ'], 422);
        }

        $imgHash = $result['slip_hash'] ?? md5_file($file->getRealPath());
        if (InstallmentPayment::where('slip_hash', $imgHash)->exists()) {
            return response()->json(['message' => 'สลิปนี้ถูกใช้งานแล้ว'], 422);
        }

        $accountNo = $result['account'] ?? null;
        $matchedAccount = BankAccount::where('account_number', 'like', "%$accountNo%")->first();
        if (!$matchedAccount) {
            return response()->json(['message' => 'เลขบัญชีปลายทางไม่ตรง รอแอดมินตรวจสอบ'], 422);
        }

        // ========== MAIN LOGIC เติมเงินเข้ากระเป๋าสัญญา ==========
        if (empty($request->installment_request_id) || empty($request->pay_for_dates)) {
            // หา contract ล่าสุดที่ approved
            $mainContract = InstallmentRequest::where('user_id', auth()->id())
                ->where('status', 'approved')
                ->latest('id')
                ->first();

            if (!$mainContract) {
                return response()->json(['message' => 'ไม่พบสัญญาที่สามารถเติมเงินได้'], 422);
            }

            // *** ใส่ comment ไว้สำหรับระบบกันสลิปย้อนหลัง (อนาคต) ***
            /*
            $minDate = Carbon::create(2025, 7, 20); // วันที่เปิดใช้งานระบบ (แก้วันตามจริง)
            $slipDate = $result['date'] ?? null;
            if ($slipDate && Carbon::parse($slipDate)->lt($minDate)) {
                return response()->json(['message' => 'ไม่อนุญาตให้อัพสลิปย้อนหลัง'], 422);
            }
            */

            // เติมเข้า advance_payment contract
            $mainContract->advance_payment += $ocrAmount;
            $mainContract->save();

            // Log ประวัติสลิปเติมเงิน
            InstallmentPayment::create([
                'installment_request_id' => $mainContract->id,
                'amount' => 0,
                'amount_paid' => 0,
                'payment_status' => 'advance',
                'status' => 'advance',
                'payment_proof' => $imgPath,
                'slip_hash' => $imgHash,
                'slip_reference' => $result['reference'] ?? null,
                'slip_qr_text' => $result['qr_text'] ?? null,
                'slip_ocr_json' => json_encode($result, JSON_UNESCAPED_UNICODE),
                'payment_due_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'เติมเงินสำเร็จ',
                'advance_payment' => $mainContract->advance_payment
            ]);
        }

        // ========== logic ผ่อนงวดจริง (มี installment_request_id + pay_for_dates) ==========
        // (วาง logic จ่ายผ่อนของคุณเดิมตรงนี้ได้เลย)
        return response()->json(['message' => 'กรณีผ่อนชำระงวด ยังไม่รองรับ'], 422);
    }

    // อัปโหลดเอกสารและ location
    public function uploadDocuments(Request $request, $id)
    {
        $request->validate([
            'document_image' => 'required|image|max:2048',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $installment = InstallmentRequest::findOrFail($id);
        $documentPath = $request->file('document_image')->store('installment_documents', 'public');

        $installment->document_image = $documentPath;
        $installment->latitude = $request->latitude;
        $installment->longitude = $request->longitude;
        $installment->save();

        return response()->json(['message' => 'อัปโหลดเอกสารและตำแหน่งสำเร็จแล้ว'], 200);
    }

    // Dashboard ย่อย (backup ไว้, เผื่อต้องใช้แยก)
    public function currentDashboard()
    {
        $user = Auth::user();
        $installment = InstallmentRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest()
            ->firstOrFail();

        $daysPassed = now()->diffInDays($installment->start_date) + 1;
        $totalShouldPay = $installment->daily_payment_amount * $daysPassed;
        $dueToday = max($totalShouldPay - $installment->total_paid, 0);

        $nextPayment = $installment->payments()
            ->where('status', 'pending')
            ->orderBy('payment_due_date')
            ->first();

        return response()->json([
            'gold_amount' => number_format($installment->gold_amount, 2),
            'due_today' => number_format($dueToday, 2),
            'advance_payment' => number_format($installment->advance_payment, 2),
            'next_payment_date' => $nextPayment ? $nextPayment->payment_due_date : '-',
            'total_penalty' => number_format($installment->total_penalty, 2),
            'total_paid' => number_format($installment->total_paid, 2),
            'total_installment_amount' => number_format($installment->total_installment_amount, 2),
            'days_passed' => $daysPassed,
            'installment_period' => $installment->installment_period,
        ]);
    }
}
