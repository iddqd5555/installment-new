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

        // ดึงเฉพาะข้อมูลที่กำลังดำเนินการ (approved, pending)
        $installmentRequests = InstallmentRequest::with('installmentPayments')
            ->where('user_id', $user->id)
            ->where('status', 'approved')  // ✅ ต้องมี approved เท่านั้นถึงแสดงผล
            ->latest()
            ->get();
        $payments = auth()->user()->payments()->latest()->get();
        $bankAccounts = BankAccount::all();

        foreach ($installmentRequests as $request) {
            $dailyPayment = 0; // ประกาศก่อน
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

                $request->total_paid = collect($request->installmentPayments)
                    ->where('status', 'approved')
                    ->sum('amount_paid');

                $request->remaining_amount = $request->total_with_interest - $request->total_paid;

                $request->next_payment_amount = $dailyPayment; // ป้องกัน error
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

        $paymentProof = $request->file('payment_proof');
        $filePath = $paymentProof->storeAs('payment_slips', Carbon::now()->format('YmdHis') . '_' . uniqid() . '.' . $paymentProof->extension(), 'public');

        InstallmentPayment::create([
            'installment_request_id' => $id,
            'amount' => InstallmentRequest::findOrFail($id)->total_with_interest,
            'amount_paid' => $request->input('amount_paid'),
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_proof' => $filePath,
        ]);

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

        InstallmentRequest::create([
            'fullname' => $user->name,
            'phone' => $user->phone,
            'id_card' => $user->id_card_number,
            'gold_type' => 'ทองรูปพรรณ',
            'gold_amount' => $request->gold_amount,
            'installment_period' => $request->installment_period,
            'approved_gold_price' => $request->gold_price,
            'total_gold_price' => $request->gold_price * $request->gold_amount,
            'status' => 'pending',
            'interest_rate' => 3.5,
            'next_payment_date' => now()->addDays(1),
            'user_id' => $user->id,
            'is_guest' => 0,
        ]);
        return redirect()->back()->with('success', 'ส่งคำขอผ่อนทองเรียบร้อยแล้ว รอการอนุมัติจากแอดมินค่ะ');
    }

    public function verify($id)
    {
        $installmentRequest = InstallmentRequest::findOrFail($id);

        // ดึงราคาทองจาก API (cache ต่อวัน)
        $goldPrice = $this->fetchGoldPrice();

        if (!$goldPrice) {
            return redirect()->back()->with('error', 'ไม่สามารถดึงราคาทองได้ กรุณาลองใหม่อีกครั้ง');
        }

        // ยอดทองรวม
        $totalGoldPrice = $goldPrice * $installmentRequest->gold_amount;

        // คำนวณดอกเบี้ยตามระยะเวลาผ่อน (ที่คุณกำหนดล่าสุด)
        switch ($installmentRequest->installment_period) {
            case 30:
                $totalWithInterest = $totalGoldPrice * 1.27;
                $dailyPayment = $totalWithInterest / 30;
                break;

            case 45:
                $totalWithInterest = $totalGoldPrice * 1.45;
                $dailyPayment = $totalWithInterest / 45;
                break;

            case 60:
                $totalWithInterest = $totalGoldPrice * 1.66;
                $dailyPayment = $totalWithInterest / 60;
                break;

            default:
                $totalWithInterest = $totalGoldPrice;
                $dailyPayment = $totalWithInterest;
        }

        // อัปเดตข้อมูลการอนุมัติ
        $installmentRequest->update([
            'status' => 'approved',
            'approved_gold_price' => $goldPrice,
            'total_with_interest' => $totalWithInterest,
            'start_date' => now(),
            'next_payment_date' => now()->addDay()
        ]);

        // สร้างรายการชำระเงินแต่ละวันตามระยะเวลาที่เลือก (รายวัน)
        for ($day = 1; $day <= $installmentRequest->installment_period; $day++) {
            InstallmentPayment::create([
                'installment_request_id' => $installmentRequest->id,
                'amount' => round($dailyPayment, 2),
                'payment_due_date' => now()->addDays($day),
                'status' => 'pending',
            ]);
        }

        return redirect()->back()->with('success', 'อนุมัติคำขอผ่อนทองและสร้างงวดชำระเรียบร้อยแล้วค่ะ');
    }
}
