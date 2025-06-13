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


class InstallmentRequestController extends Controller
{
    public function create($id)
    {
        $product = InstallmentRequest::findOrFail($id);
        return view('installments.create', compact('product'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'gold_amount' => 'required|integer|min:1',
            'installment_period' => 'required|in:3,6,12',
        ]);

        // ดึงข้อมูลราคาทองจาก API
        $response = Http::get('https://www.goldtraders.or.th/api/latest-price');

        if ($response->successful()) {
            $goldPrice = $response->json()['response']['price']['gold_jewelry']['sell'];
        } else {
            return back()->with('error', 'ไม่สามารถดึงราคาทองได้ขณะนี้ กรุณาลองใหม่อีกครั้ง');
        }

        // ดึงค่า interest_rate จากฐานข้อมูล (หรือกำหนดค่าเริ่มต้นก่อน)
        $interestRate = 3.5;

        // คำนวณเงินรวมดอกเบี้ย
        $totalPrice = $request->gold_amount * $goldPrice;
        $interestAmount = ($interestRate / 100) * $totalPrice;
        $totalWithInterest = $totalPrice + $interestAmount;

        // สร้างคำขอผ่อนใหม่
        InstallmentRequest::create([
            'fullname' => auth()->user()->name,
            'phone' => auth()->user()->phone,
            'gold_type' => 'ทองรูปพรรณ',
            'gold_amount' => $request->gold_amount,
            'installment_period' => $request->installment_period,
            'status' => 'pending',
            'user_id' => auth()->id(),
            'interest_rate' => $interestRate,
            'total_with_interest' => $totalWithInterest,
            'next_payment_date' => now()->addMonth(), // กำหนดวันชำระงวดแรก
        ]);

        return redirect()->route('dashboard')->with('success', 'ส่งคำขอผ่อนทองเรียบร้อยแล้วค่ะ');
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
    public function submitGoldForm(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'gold_amount' => 'required|integer|min:1',
            'installment_period' => 'required|integer|in:3,6,12',
        ]);

        InstallmentRequest::create([
            'fullname' => $request->fullname,
            'phone' => $request->phone,
            'gold_type' => 'ทองรูปพรรณ', // กำหนดชัดเจนตายตัว
            'gold_amount' => $request->gold_amount,
            'installment_period' => $request->installment_period,
            'status' => 'pending',
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'ส่งคำขอผ่อนทองเรียบร้อยแล้วค่ะ!');
    }

    public function index()
    {
        $goldPrice = null;

        // เรียก API ดึงราคาทองล่าสุด
        $response = Http::get('https://www.goldtraders.or.th/api/latest-price');

        if ($response->successful()) {
            $data = $response->json();
            $goldPrice = [
                'type' => 'ทองรูปพรรณ',
                'buy' => $data['response']['price']['gold_jewelry']['buy'],
                'sell' => $data['response']['price']['gold_jewelry']['sell']
            ];
        }

        return view('gold.index', compact('goldPrice'));
    }

    public function dashboard()
    {
        $user = auth()->user();

        // ดึงราคาทองจาก API ที่คุณให้ไว้ (ห้ามเปลี่ยน)
        try {
            $response = Http::get('https://www.goldtraders.or.th/');
            if ($response->successful()) {
                $crawler = new Crawler($response->body());
                $goldPrice = (float) str_replace(',', '', trim($crawler->filter('#DetailPlace_uc_goldprices1_lblOMBuy')->text()));
            } else {
                $goldPrice = null;
            }
        } catch (\Exception $e) {
            $goldPrice = null;
        }

        $installmentRequests = InstallmentRequest::with('installmentPayments')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        foreach ($installmentRequests as $request) {
            if ($request->approved_gold_price) {
                $request->total_gold_price = $request->approved_gold_price * $request->gold_amount;
                $request->interest_amount = ($request->interest_rate / 100) * $request->total_gold_price;
                $request->total_with_interest = $request->total_gold_price + $request->interest_amount;

                $request->total_paid = collect($request->installmentPayments)
                    ->where('status', 'approved')
                    ->sum('amount_paid');

                $currentMonthPayments = collect($request->installmentPayments)
                    ->filter(function ($payment) {
                        $paymentDate = Carbon::parse($payment->updated_at);
                        return $payment->status === 'approved' &&
                            $paymentDate->month === Carbon::now()->month &&
                            $paymentDate->year === Carbon::now()->year;
                    })
                    ->sum('amount_paid');

                $monthlyPayment = $request->total_with_interest / $request->installment_period;
                $request->current_month_due = $monthlyPayment - $currentMonthPayments;

                $request->remaining_amount = $request->total_with_interest - $request->total_paid;

                // คำนวณ remaining_months ตามวันที่จริง
                if ($request->start_date) {
                    $startDate = Carbon::parse($request->start_date);
                    $endDate = $startDate->copy()->addMonths($request->installment_period);

                    $remainingMonths = Carbon::now()->diffInMonths($endDate, false);
                    $request->remaining_months = max($remainingMonths, 0);
                } else {
                    $request->remaining_months = $request->installment_period;
                }

                $nextPayment = collect($request->installmentPayments)
                    ->where('status', 'pending')
                    ->sortBy('payment_due_date')
                    ->first();

                $request->next_payment_date = optional($nextPayment)->payment_due_date;

                $request->next_payment_amount = ($request->remaining_months > 0)
                    ? $request->remaining_amount / $request->remaining_months
                    : 0;
            } else {
                $request->total_gold_price = 0;
                $request->interest_amount = 0;
                $request->total_with_interest = 0;
                $request->total_paid = 0;
                $request->remaining_amount = 0;

                if ($request->start_date) {
                    $startDate = Carbon::parse($request->start_date);
                    $endDate = $startDate->copy()->addMonths($request->installment_period);

                    $remainingMonths = Carbon::now()->diffInMonths($endDate, false);
                    $request->remaining_months = max($remainingMonths, 0);
                } else {
                    $request->remaining_months = $request->installment_period;
                }

                $request->next_payment_amount = 0;
                $request->next_payment_date = null;
                $request->current_month_due = 0;
            }
        }

        $payments = InstallmentPayment::whereHas('installmentRequest', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // อย่าลืมส่งตัวแปร $goldPrice ไปด้วย (ที่คุณลืมล่าสุด)
        return view('dashboard', compact('installmentRequests', 'payments', 'goldPrice'));
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

    // Function ดึงราคาทอง (ตัวอย่างที่รัดกุม)
    private function fetchGoldPrice()
    {
        return Cache::remember('latest_gold_price', now()->addMinutes(30), function () {
            try {
                $response = Http::timeout(5)->get('https://www.goldtraders.or.th/');
                if ($response->successful()) {
                    return $response->json()['response']['price']['gold_jewelry']['sell'];
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching gold price: '.$e->getMessage());
            }
            return null;
        });
    }

    public function orderHistory()
    {
        $user = auth()->user();

        $orders = InstallmentRequest::where('user_id', $user->id)->get();

        return view('orders.history', compact('orders'));
    }

    public function goldapi()
    {
        try {
            $response = Http::get('https://www.goldtraders.or.th/');
            if ($response->successful()) {
                $crawler = new Crawler($response->body());

                // ดึงราคาทองคำแท่ง (ซื้อเข้า/ขายออก)
                $barBuyPrice = trim($crawler->filter('#DetailPlace_uc_goldprices1_lblBLSell')->text());
                $barSellPrice = trim($crawler->filter('#DetailPlace_uc_goldprices1_lblBLBuy')->text());

                // ดึงราคาทองรูปพรรณ (ซื้อเข้า/ขายออก)
                $ornamentBuyPrice = trim($crawler->filter('#DetailPlace_uc_goldprices1_lblOMSell')->text());
                $ornamentSellPrice = trim($crawler->filter('#DetailPlace_uc_goldprices1_lblOMBuy')->text());

                $goldPrices = [
                    'bar_buy' => $barBuyPrice,
                    'bar_sell' => $barSellPrice,
                    'ornament_buy' => $ornamentBuyPrice,
                    'ornament_sell' => $ornamentSellPrice,
                ];

            } else {
                $goldPrices = null;
            }
        } catch (\Exception $e) {
            $goldPrices = null;
        }

        return view('gold_guest', compact('goldPrices'));
    }

    public function verify($id)
    {
        $installmentRequest = InstallmentRequest::findOrFail($id);

        // ดึงราคาทอง ณ วันอนุมัติ
        $goldPrice = $this->fetchGoldPrice();

        if ($goldPrice) {
            // คำนวณ
            $totalGoldPrice = $goldPrice * $installmentRequest->gold_amount;
            $interestAmount = ($installmentRequest->interest_rate / 100) * $totalGoldPrice;
            $totalWithInterest = $totalGoldPrice + $interestAmount;

            // อัปเดตข้อมูลในฐานข้อมูล
            $installmentRequest->update([
                'status' => 'approved',
                'approved_gold_price' => $goldPrice,
                'total_with_interest' => $totalWithInterest,
                'next_payment_date' => now()->addMonth()
            ]);

            // สร้างรายการชำระเงินแต่ละงวด
            for ($month = 1; $month <= $installmentRequest->installment_period; $month++) {
                InstallmentPayment::create([
                    'installment_request_id' => $installmentRequest->id,
                    'amount' => round($totalWithInterest / $installmentRequest->installment_period, 2),
                    'payment_due_date' => now()->addMonths($month),
                    'status' => 'pending',
                ]);
            }

            return redirect()->back()->with('success', 'อนุมัติและคำนวณยอดสำเร็จแล้ว');
        } else {
            return redirect()->back()->with('error', 'ไม่สามารถดึงราคาทองได้ กรุณาลองใหม่อีกครั้ง');
        }
    }
}
