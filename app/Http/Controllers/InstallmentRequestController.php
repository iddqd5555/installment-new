<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment; // ✅ ต้องอยู่ตรงนี้ (บนสุดของไฟล์)
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Notifications\InstallmentDueReminderNotification;
use Illuminate\Support\Facades\DB;
use App\Models\BankAccount;

class InstallmentRequestController extends Controller
{
    public function create($id)
    {
        $product = InstallmentRequest::findOrFail($id);
        return view('installments.create', compact('product'));
    }

    public function store(Request $request)
    {
        if(session()->has('submitted_installment')){
            return back()->with('error', 'คุณได้ส่งคำขอแล้ว กรุณารอสักครู่ค่ะ');
        }

        $request->validate([
            'fullname' => 'required|string',
            'id_card' => 'required|string|min:13|max:13',
            'phone' => 'required|string',
            'gold_amount' => 'required|numeric|min:0.01|max:10000',
            'gold_price' => 'required|numeric|min:1000|max:1000000',
            'installment_period' => 'required|in:30,45,60',
        ]);

        InstallmentRequest::create([
            'fullname' => $request->fullname,
            'id_card' => $request->id_card,
            'phone' => $request->phone,
            'gold_type' => 'ทองรูปพรรณ',
            'gold_amount' => $request->gold_amount,
            'installment_period' => $request->installment_period,
            'approved_gold_price' => $request->gold_price,
            'total_with_interest' => $request->gold_amount * $request->gold_price * 1.035,
            'status' => 'pending',
            'interest_rate' => 3.5,
            'next_payment_date' => now()->addDays(30),
            'user_id' => auth()->id()
        ]);

        session(['submitted_installment' => true]);

        return redirect()->back()->with('success', 'ส่งคำขอผ่อนทองเรียบร้อยแล้วค่ะ');
    }

    public function submit(Request $request)
    {
        $request->validate([
            'fullname' => 'required',
            'phone' => 'required',
            'gold_type' => 'required',
            'gold_amount' => 'required|integer|min:1',
            'installment_period' => 'required|in:3,6,12'
        ]);

        InstallmentRequest::create([
            'fullname' => $request->fullname,
            'phone' => $request->phone,
            'gold_type' => $request->gold_type,
            'gold_amount' => $request->gold_amount,
            'installment_period' => $request->installment_period,
            'status' => 'pending', // ตั้งค่าเริ่มต้นรออนุมัติ
            'user_id' => auth()->id() ?? null
        ]);

        return redirect()->back()->with('success', 'ส่งข้อมูลสำเร็จ รอการติดต่อกลับจากแอดมินค่ะ!');
    }

    public function dashboard()
    {
        $user = auth()->user();

        $installmentRequests = InstallmentRequest::with('installmentPayments')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest()
            ->get();

        $bankAccounts = BankAccount::all();

        foreach ($installmentRequests as $request) {
            $dailyPayment = 0;
            if ($request->approved_gold_price) {
                $request->total_gold_price = $request->approved_gold_price * $request->gold_amount;

                switch ($request->installment_period) {
                    case 30:
                        $request->total_with_interest = $request->total_gold_price * 1.27;
                        $dailyPayment = $request->total_with_interest / 30;
                        $request->first_payment = $dailyPayment * 2;
                        break;
                    case 45:
                        $request->total_with_interest = $request->total_gold_price * 1.45;
                        $dailyPayment = $request->total_with_interest / 45;
                        $request->first_payment = $dailyPayment * 3;
                        break;
                    case 60:
                        $request->total_with_interest = $request->total_gold_price * 1.66;
                        $dailyPayment = $request->total_with_interest / 60;
                        $request->first_payment = $dailyPayment * 4;
                        break;
                    default:
                        $request->total_with_interest = $request->total_gold_price;
                        $request->first_payment = 0;
                }

                $request->interest_amount = $request->total_with_interest - $request->total_gold_price;

                $request->total_paid = collect($request->installmentPayments)
                    ->where('status', 'approved')
                    ->sum('amount_paid');

                $request->remaining_amount = $request->total_with_interest - $request->total_paid;

                $request->daily_payment_amount = round($dailyPayment, 2);

                // คำนวณเดือนที่เหลือ
                $request->remaining_months = ceil(($request->installment_period - 
                    Carbon::parse($request->start_date)->diffInDays(now())) / 30);

                // คำนวณวันชำระครั้งถัดไป
                $nextPayment = $request->installmentPayments()
                    ->where('status', 'pending')
                    ->whereDate('payment_due_date', '>=', now())
                    ->orderBy('payment_due_date')
                    ->first();

                $request->next_payment_date = $nextPayment ? $nextPayment->payment_due_date : null;
            }
        }

        $payments = InstallmentPayment::whereHas('installmentRequest', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->orderBy('created_at', 'desc')->get();

        return view('dashboard', compact('installmentRequests', 'payments', 'bankAccounts'));
    }

    public function uploadPaymentProof(Request $request, $id)
    {
        // Validate Input
        $request->validate([
            'amount_paid' => 'required|numeric|min:0|max:999999999.99',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $installment = InstallmentRequest::findOrFail($id);
        $amountPaid = floatval($request->input('amount_paid'));

        // อัปโหลดสลิปธนาคาร
        $paymentProof = $request->file('payment_proof');
        $filePath = $paymentProof->storeAs('payment_slips', Carbon::now()->format('YmdHis') . '_' . uniqid() . '.' . $paymentProof->extension(), 'public');

        // ยอดที่ควรชำระสะสมถึงวันนี้
        $daysPassed = Carbon::parse($installment->start_date)->diffInDays(today()) + 1;
        $totalShouldPay = $installment->daily_payment_amount * $daysPassed;

        // ยอดที่ชำระแล้วทั้งหมด (รวม advance payment ด้วย)
        $totalPaid = $installment->installmentPayments()
            ->where('status', 'approved')
            ->sum('amount_paid') + $installment->advance_payment;

        // ยอดรวมหลังจากบวกยอดที่ลูกค้าเพิ่งชำระเข้ามา
        $newTotalPaid = $totalPaid + $amountPaid;

        // ตรวจสอบว่ามีการชำระเกินหรือไม่
        if ($newTotalPaid > $totalShouldPay) {
            // ถ้าชำระเกินจริง ให้บันทึกยอดส่วนเกินเป็น advance_payment
            $advanceAmount = $newTotalPaid - $totalShouldPay;
            $actualPaidToday = $amountPaid - $advanceAmount;
            $installment->advance_payment = $advanceAmount;
        } else {
            // ถ้าไม่เกินให้ล้าง advance payment
            $installment->advance_payment = 0;
            $actualPaidToday = $amountPaid;
        }

        // บันทึกรายการชำระเงิน
        InstallmentPayment::create([
            'installment_request_id' => $installment->id,
            'amount' => $installment->daily_payment_amount,
            'amount_paid' => $actualPaidToday,
            'status' => 'approved',
            'payment_status' => 'paid',
            'payment_due_date' => today(),
            'payment_proof' => $filePath,
            'admin_notes' => 'ชำระเงินโดยลูกค้า'
        ]);

        // อัปเดตยอดรวมใน installment_requests
        $installment->total_paid += $actualPaidToday;
        $installment->remaining_amount -= $actualPaidToday;

        // บันทึกข้อมูลที่เปลี่ยนแปลงทั้งหมด
        $installment->save();

        return redirect()->back()->with('success', 'อัปโหลดสลิปเรียบร้อยแล้ว รอการตรวจสอบจากแอดมิน');
    }

    public function goldapi()
    {
        $goldPrices = Cache::remember('gold_prices', now()->addMinutes(30), function () {
            try {
                $response = Http::timeout(5)->get('https://www.goldtraders.or.th/');
                if ($response->successful()) {
                    $crawler = new Crawler($response->body());

                    $ornamentBuyPrice = trim($crawler->filter('#DetailPlace_uc_goldprices1_lblOMSell')->text());
                    $ornamentSellPrice = trim($crawler->filter('#DetailPlace_uc_goldprices1_lblOMBuy')->text());

                    $ornamentBuyGram = number_format((float) str_replace(',', '', $ornamentBuyPrice) / 15.244, 2);

                    return [
                        'ornament_buy' => $ornamentBuyPrice,
                        'ornament_sell' => $ornamentSellPrice,
                        'ornament_buy_gram' => $ornamentBuyGram,
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Gold price fetch error: ' . $e->getMessage());
                return [
                    'ornament_buy' => 'n/a',
                    'ornament_sell' => 'n/a',
                    'ornament_buy_gram' => 'n/a',
                ];
            }
        });

        return view('gold_guest', compact('goldPrices'));
    }

    public function orderHistory()
    {
        $user = auth()->user();

        $orders = InstallmentRequest::where('user_id', $user->id)->get();

        return view('orders.history', compact('orders'));
    }

    // ✅ Method ใหม่แสดงหน้าฟอร์มทองชัดเจน
    public function showGoldForm()
    {
        $goldPrices = Cache::remember('gold_prices_daily', now()->endOfDay(), function () {
            try {
                $response = Http::timeout(10)->get('https://www.goldtraders.or.th/');
                if ($response->successful()) {
                    $crawler = new Crawler($response->body());
                    return [
                        'ornament_sell' => str_replace(',', '', trim($crawler->filter('#DetailPlace_uc_goldprices1_lblOMBuy')->text())),
                        'ornament_buy' => str_replace(',', '', trim($crawler->filter('#DetailPlace_uc_goldprices1_lblOMSell')->text())),
                        'ornament_buy_gram' => str_replace(',', '', trim($crawler->filter('#DetailPlace_uc_goldprices1_lblOMBuyGram')->text()))
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching gold price: '.$e->getMessage());
            }
            return [
                'ornament_sell' => '0',
                'ornament_buy' => '0',
                'ornament_buy_gram' => '0'
            ];
        });

        return view('gold_member', compact('goldPrices'));
    }

    public function submitGoldGuest(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'id_card' => 'required|string|max:13',
            'gold_amount' => 'required|numeric|min:0.01|max:10000',
            'installment_period' => 'required|in:30,45,60',
            'gold_price' => 'required|numeric|min:1000|max:1000000',
        ]);

        InstallmentRequest::create([
            'fullname' => $request->fullname,
            'phone' => $request->phone,
            'id_card' => $request->id_card,
            'gold_type' => 'ทองรูปพรรณ',
            'gold_amount' => $request->gold_amount,
            'installment_period' => $request->installment_period,
            'approved_gold_price' => $request->gold_price,
            'total_gold_price' => $request->gold_price * $request->gold_amount,
            'status' => 'pending',
            'interest_rate' => 3.5,
            'next_payment_date' => now()->addDays(1),
            'user_id' => null,
            'is_guest' => 1,
        ]);
        return redirect()->back()->with('success', 'ส่งคำขอผ่อนทองเรียบร้อยแล้วค่ะ');
    }

    public function submitGoldMember(Request $request)
    {
        $request->validate([
            'gold_amount' => 'required|numeric|min:0.01|max:10000',
            'installment_period' => 'required|in:30,45,60',
            'gold_price' => 'required|numeric|min:1000|max:1000000',
        ]);

        $user = auth()->user();

        // คำนวณยอดรวมพร้อมดอกเบี้ยทันทีที่ส่งคำขอ
        $interestRates = [
            30 => 1.27,
            45 => 1.45,
            60 => 1.66
        ];

        $totalGoldPrice = $request->gold_price;
        $interestRate = $interestRates[$request->installment_period];
        $totalWithInterest = $totalGoldPrice * $interestRate;
        $dailyPaymentAmount = round($totalWithInterest / $request->installment_period, 2);

        InstallmentRequest::create([
            'fullname' => $user->first_name . ' ' . $user->last_name,
            'phone' => $user->phone,
            'id_card' => $user->id_card_number,
            'gold_type' => 'ทองรูปพรรณ',
            'gold_amount' => round($totalGoldPrice / $this->getCurrentGoldPrice(), 2),
            'installment_period' => $request->installment_period,
            'approved_gold_price' => $this->getCurrentGoldPrice(),
            'total_gold_price' => $totalGoldPrice,
            'total_with_interest' => $totalWithInterest,
            'daily_payment_amount' => $dailyPaymentAmount,
            'status' => 'pending',
            'interest_rate' => ($interestRate - 1) * 100,
            'next_payment_date' => now()->addDays(1),
            'user_id' => $user->id,
            'is_guest' => 0,
        ]);

        return redirect()->back()->with('success', 'ส่งคำขอผ่อนทองเรียบร้อยแล้ว รอการอนุมัติจากแอดมินค่ะ');
    }

    // เพิ่ม method ดึงราคาทองปัจจุบัน
    private function getCurrentGoldPrice()
    {
        return Cache::remember('current_gold_price', now()->addMinutes(30), function () {
            try {
                $response = Http::timeout(5)->get('https://www.goldtraders.or.th/');
                if ($response->successful()) {
                    $crawler = new Crawler($response->body());
                    return (float) str_replace(',', '', trim($crawler->filter('#DetailPlace_uc_goldprices1_lblOMBuy')->text()));
                }
            } catch (\Exception $e) {
                \Log::error('Gold price fetch error: ' . $e->getMessage());
            }
            return 0; // ราคาทอง default ถ้าดึงไม่ได้
        });
    }

    public function verify($id)
    {
        $installmentRequest = InstallmentRequest::findOrFail($id);

        $goldPrice = $installmentRequest->approved_gold_price;

        if (!$goldPrice) {
            return redirect()->back()->with('error', 'กรุณาระบุราคาทองที่อนุมัติก่อนค่ะ');
        }

        $totalGoldPrice = $goldPrice * $installmentRequest->gold_amount;

        switch ($installmentRequest->installment_period) {
            case 30:
                $totalWithInterest = $totalGoldPrice * 1.27;
                break;
            case 45:
                $totalWithInterest = $totalGoldPrice * 1.45;
                break;
            case 60:
                $totalWithInterest = $totalGoldPrice * 1.66;
                break;
            default:
                $totalWithInterest = $totalGoldPrice;
        }

        $dailyPayment = round($totalWithInterest / $installmentRequest->installment_period, 2);

        $installmentRequest->update([
            'status' => 'approved',
            'total_gold_price' => $totalGoldPrice,
            'total_with_interest' => $totalWithInterest,
            'daily_payment_amount' => $dailyPayment,
            'start_date' => now(),
            'next_payment_date' => now()->addDay()
        ]);

        for ($day = 1; $day <= $installmentRequest->installment_period; $day++) {
            InstallmentPayment::create([
                'installment_request_id' => $installmentRequest->id,
                'amount' => $dailyPayment,
                'payment_due_date' => now()->addDays($day),
                'status' => 'pending',
            ]);
        }

        return redirect()->back()->with('success', 'อนุมัติคำขอเรียบร้อยแล้วค่ะ');
    }

}
