@extends('layouts.app')

@section('content')
<div class="container py-5">
    @if(session('error'))
        <div class="alert alert-danger">
            <strong>เกิดข้อผิดพลาด!</strong><br>
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            <strong>ทำรายการเรียบร้อยแล้ว!</strong><br>
            {{ session('success') }}
        </div>
    @endif
    <h2 class="text-success">Dashboard การผ่อนของคุณ</h2>
    @can('check_admin')
        <a href="{{ route('admin.payment-settings') }}" class="btn btn-info my-3">⚙️ จัดการบัญชีชำระเงิน</a>
    @endcan
    <a href="{{ route('payment-info') }}" class="btn btn-primary my-3">📋 ดูข้อมูลชำระเงิน</a>
    @if($goldPrice)
        <div class="alert alert-info">
            💎 ราคาทองคำปัจจุบัน: {{ number_format($goldPrice, 2) }} บาท
        </div>
    @else
        <div class="alert alert-warning">
            ⚠️ ไม่สามารถดึงราคาทองคำล่าสุดได้ กรุณารีเฟรชอีกครั้งค่ะ
        </div>
    @endif

    @if(auth()->user()->unreadNotifications->count())
        <div class="alert alert-info">
            <ul>
                @foreach(auth()->user()->unreadNotifications->take(3) as $notification)
                    <li>
                        {{ \Illuminate\Support\Arr::get($notification->data, 'message', 'ไม่มีข้อความ') }}
                        @if(\Illuminate\Support\Arr::get($notification->data, 'due_date'))
                            (กำหนดชำระ: {{ \Carbon\Carbon::parse(\Illuminate\Support\Arr::get($notification->data, 'due_date'))->format('d/m/Y') }})
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @forelse($installmentRequests as $request)
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">📌 ผ่อนทอง ({{ number_format($request->gold_amount, 2) }} บาท)</h5>

                {{-- หลอดความคืบหน้าจำนวนเงิน --}}
                <div class="card bg-success text-white mb-3">
                    <div class="card-body">
                        <p><strong>💵 ยอดที่ต้องชำระเดือนนี้:</strong> 
                        @php
                            // ประกาศตัวแปร monthlyPayment ก่อนใช้งาน
                            $monthlyPayment = $request->total_with_interest / $request->installment_period;

                            $paidThisMonth = $request->installmentPayments
                                ->where('status', 'approved')
                                ->filter(function($payment) {
                                    return \Carbon\Carbon::parse($payment->created_at)->isCurrentMonth();
                                })
                                ->sum('amount_paid');

                            $dueThisMonth = $monthlyPayment - $paidThisMonth;
                        @endphp

                        {{ number_format(max($dueThisMonth, 0), 2) }} บาท
                        </p>
                        <strong>ชำระแล้ว:</strong> {{ number_format($request->total_paid, 2) }} บาท<br>
                        <strong>คงเหลือ:</strong> {{ number_format($request->remaining_amount, 2) }} บาท
                        <div class="progress mt-2">
                            @php
                                $paymentProgress = $request->total_with_interest > 0
                                    ? ($request->total_paid / $request->total_with_interest) * 100
                                    : 0;
                            @endphp
                            <div class="progress-bar bg-light" style="width: {{ $paymentProgress }}%;">
                                {{ number_format($paymentProgress, 2) }}%
                            </div>
                        </div>
                    </div>
                </div>
                {{-- หลอดความคืบหน้าระยะเวลาผ่อน --}}
                <div class="card bg-info text-white mb-3">
                    <div class="card-body">
                        <strong>ระยะเวลาการผ่อน:</strong> {{ $request->installment_period }} เดือน (เหลือ {{ $request->remaining_months }} เดือน)
                        <div class="progress mt-2">
                            @php
                                $timeProgress = (($request->installment_period - $request->remaining_months) / $request->installment_period) * 100;
                            @endphp
                            <div class="progress-bar bg-light" style="width: {{ $timeProgress }}%;">
                                {{ number_format($timeProgress, 2) }}%
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $monthlyPayment = $request->total_with_interest / $request->installment_period;
                @endphp

                <p><strong>📅 เดือนที่เหลือ:</strong> {{ $request->remaining_months }} เดือน</p>
                <p><strong>💵 ยอดที่ต้องชำระครั้งถัดไป:</strong> {{ number_format($monthlyPayment, 2) }} บาท</p>
                <p><strong>📆 วันชำระครั้งถัดไป:</strong> 
                    @if($request->next_payment_date)
                        {{ \Carbon\Carbon::parse($request->next_payment_date)->format('d/m/Y') }}
                    @else
                        ยังไม่กำหนด
                    @endif
                </p>

                {{-- ส่วนชำระเงินและอัปโหลดสลิปของคุณเดิม --}}
                <button class="btn btn-success" type="button" data-bs-toggle="collapse"
                    data-bs-target="#payInfo{{ $request->id }}" aria-expanded="false">
                    ชำระเงิน
                </button>

                <button class="btn btn-warning" type="button" data-bs-toggle="collapse"
                    data-bs-target="#uploadSlip{{ $request->id }}" aria-expanded="false">
                    อัพโหลดสลิป
                </button>

                <div class="collapse mt-3" id="uploadSlip{{ $request->id }}">
                    <div class="card card-body">
                        <form id="payment-form-{{ $request->id }}" action="{{ route('payments.upload-proof', $request->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="remaining_amount_{{ $request->id }}" value="{{ $request->remaining_amount }}">

                            <div class="mb-3">
                                <label class="form-label">จำนวนเงินที่โอน (บาท)</label>
                                <input type="number" class="form-control" id="amount_paid_{{ $request->id }}" name="amount_paid" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">อัปโหลดสลิปธนาคาร</label>
                                <input type="file" class="form-control" name="payment_proof" required>
                            </div>
                            <button class="btn btn-primary" type="submit">ส่งหลักฐานการชำระเงิน</button>
                        </form>
                    </div>
                </div>
                <hr>

                🌟 ราคาทองที่อนุมัติ: <strong>{{ number_format($request->approved_gold_price, 2) }} บาท</strong><br>
                💳 ราคารวมทองคำ: <strong>{{ number_format($request->total_gold_price, 2) }} บาท</strong><br>
                📌 ดอกเบี้ย ({{ $request->interest_rate }}%): <strong>{{ number_format($request->interest_amount, 2) }} บาท</strong><br>
                💰 เงินรวมดอกเบี้ย: <strong>{{ number_format($request->total_with_interest, 2) }} บาท</strong><br>
            </div>
        </div>
     @empty
        <div class="alert alert-warning">⚠️ ไม่มีข้อมูลการผ่อนที่อนุมัติค่ะ</div>
    @endforelse

    {{-- ประวัติการชำระเงินของคุณเดิม --}}
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5>📌 ประวัติการชำระเงิน</h5>
            @if($payments->count() > 0)
                <div class="payment-history mt-3">
                    @foreach($payments as $payment)
                    <div class="payment-item d-flex align-items-center justify-content-between shadow-sm p-3 rounded mb-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-cash-stack text-success me-3" style="font-size: 2rem;"></i>
                            <div>
                                <strong>โอนเงิน</strong><br>
                                <small class="text-muted">{{ $payment->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <strong>{{ number_format($payment->amount_paid, 2) }} บาท</strong><br>
                            @if($payment->status == 'approved')
                                <span class="badge bg-success">เงินเข้า</span>
                            @elseif($payment->status == 'pending')
                                <span class="badge bg-warning text-dark">อยู่ระหว่างการตรวจสอบ</span>
                            @else
                                <span class="badge bg-danger">ผิดพลาด</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted mt-3">ยังไม่มีประวัติการชำระเงินค่ะ</p>
            @endif
        </div>
    </div>

</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @foreach($installmentRequests as $request)
    document.getElementById('payment-form-{{ $request->id }}').addEventListener('submit', function(e) {
        const amountPaid = parseFloat(document.getElementById('amount_paid_{{ $request->id }}').value);
        const remainingAmount = parseFloat(document.getElementById('remaining_amount_{{ $request->id }}').value);

        if (amountPaid > remainingAmount) {
            e.preventDefault();
            alert('⚠️ จำนวนเงินที่ชำระเกินยอดคงเหลือที่ต้องชำระค่ะ!');
        }
    });
    @endforeach
});
</script>
@endsection
