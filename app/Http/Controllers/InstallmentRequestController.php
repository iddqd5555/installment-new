<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstallmentRequest;
use App\Models\InstallmentPayment; // ✅ ต้องอยู่ตรงนี้ (บนสุดของไฟล์)
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Auth;


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
        $requests = auth()->user()->installmentRequests()->latest()->get();
        $payments = auth()->user()->payments()->latest()->get();
        $goldPrice = $this->fetchGoldPrice();
        $user = auth()->user();

        // ดึงราคาทองปัจจุบัน (ของเดิมดีแล้ว)
        try {
            $response = Http::get('https://www.goldtraders.or.th/');
            if ($response->successful()) {
                $crawler = new Crawler($response->body());
                $goldPrice = (float) str_replace(',', '', trim($crawler->filter('#DetailPlace_uc_goldprices1_lblOMBuy')->text()));
            }
        } catch (\Exception $e) {
            $goldPrice = null;
        }

        $notifications = $user->unreadNotifications()->latest()->limit(3)->get();

        $requests = InstallmentRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->with('payments') // ✅ เพิ่มตรงนี้ให้ชัดเจน
            ->get();

        // โค้ดที่มีอยู่เดิม ถูกต้องแล้วค่ะ
        foreach ($requests as $request) {
            $goldPriceApproved = $request->approved_gold_price;

            // ราคาทองและดอกเบี้ย
            $request->total_gold_price = $goldPriceApproved * $request->gold_amount;
            $request->interest_amount = ($request->interest_rate / 100) * $request->total_gold_price;
            $request->total_with_interest = $request->total_gold_price + $request->interest_amount;

            // ชำระและค่าปรับ
            $totalPaid = InstallmentPayment::where('installment_request_id', $request->id)->sum('amount');
            $totalFine = InstallmentPayment::where('installment_request_id', $request->id)->sum('fine');

            $request->total_paid = $totalPaid;
            $request->total_fine = $totalFine;

            // ยอดคงเหลือทั้งหมด
            $request->remaining_amount = ($request->total_with_interest + $totalFine) - $totalPaid;

            // เดือนที่ชำระไปแล้ว
            $paidMonths = InstallmentPayment::where('installment_request_id', $request->id)->count();
            $request->remaining_months = $request->installment_period - $paidMonths;

            // ยอดที่ต้องชำระครั้งถัดไป
            $request->next_payment_amount = $request->remaining_months > 0
                ? round($request->remaining_amount / $request->remaining_months, 2)
                : $request->remaining_amount;

            // วันชำระครั้งถัดไป (ตามกำหนดงวดถัดไป)
            $nextPayment = InstallmentPayment::where('installment_request_id', $request->id)
                ->whereNull('payment_date')
                ->orderBy('due_date', 'asc')
                ->first();

            $request->next_payment_date = $nextPayment ? $nextPayment->due_date : null;

            // ความคืบหน้า %
            $request->payment_progress = $request->total_with_interest > 0
                ? ($totalPaid / $request->total_with_interest) * 100
                : 0;
           }

        return view('dashboard', compact('requests', 'payments', 'goldPrice', 'notifications'));
    }
    protected function fetchGoldPrice()
    {
        try {
            $response = Http::get('https://www.goldtraders.or.th/');
            if ($response->successful()) {
                $crawler = new Crawler($response->body());
                return (float) str_replace(',', '', trim($crawler->filter('#DetailPlace_uc_goldprices1_lblOMBuy')->text()));
            }
        } catch (\Exception $e) {
            \Log::error("Error fetching gold price: " . $e->getMessage());
        }
        return null;
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
}
