@extends('layouts.app')

@section('content')
<div class="container py-5">
    @if(session('error'))
        <div class="alert alert-danger">
            <strong>❌ เกิดข้อผิดพลาด!</strong><br>{{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            <strong>✅ สำเร็จ!</strong><br>{{ session('success') }}
        </div>
    @endif

    <h2 class="text-success mb-4">📊 Dashboard การผ่อนของคุณ</h2>

    @if(auth()->user()->unreadNotifications->count())
        <div class="alert alert-info">
            <strong>🔔 แจ้งเตือนล่าสุด:</strong>
            <ul class="mt-2">
                @foreach(auth()->user()->unreadNotifications->take(3) as $notification)
                    <li>
                        {{ $notification->data['message'] ?? 'ไม่มีข้อความ' }}
                        @if(isset($notification->data['date']))
                            ({{ \Carbon\Carbon::parse($notification->data['date'])->format('d/m/Y') }})
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @forelse($installmentRequests as $request)
    @php
        $dailyPayment = $request->daily_payment_amount;
        $daysPassed = \Carbon\Carbon::parse($request->start_date)->diffInDays(today()) + 1;
        $totalShouldPay = $dailyPayment * $daysPassed;
        $totalPaid = $request->installmentPayments->where('status', 'approved')->sum('amount_paid') + $request->advance_payment;
        $dueToday = max($totalShouldPay - $totalPaid, 0);

        $overdueDays = max(0, floor(($totalShouldPay - $totalPaid) / $dailyPayment));
        $penaltyAmount = $overdueDays * 100;
    @endphp

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            📌 ผ่อนทองจำนวน: <strong>{{ number_format($request->gold_amount, 2) }} บาท</strong>
        </div>

        <div class="card-body">
            <div class="row text-center mb-4">
                <div class="col-md-3">
                    <div class="alert alert-primary">
                        💳 <strong>ยอดที่ต้องชำระวันนี้</strong><br>{{ number_format($dueToday, 2) }} บาท
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success">
                        💰 <strong>ยอดชำระล่วงหน้า</strong><br>{{ number_format($request->advance_payment, 2) }} บาท
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        📅 <strong>วันชำระครั้งถัดไป</strong><br>
                        @if($nextPayment = $request->installmentPayments()->where('status', 'pending')->orderBy('payment_due_date')->first())
                            {{ \Carbon\Carbon::parse($nextPayment->payment_due_date)->format('d/m/Y') }}
                        @else
                            ยังไม่กำหนด
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-danger">
                        ⚠️ <strong>ค่าปรับสะสม</strong><br>{{ number_format($penaltyAmount, 2) }} บาท
                    </div>
                </div>
            </div>

            {{-- หลอดความคืบหน้าจำนวนเงิน --}}
            <div class="mb-3">
                <strong>ชำระแล้วทั้งหมด:</strong> {{ number_format($request->total_paid, 2) }} / {{ number_format($request->total_with_interest, 2) }} บาท
                <div class="progress mt-2">
                    @php
                        $paymentProgress = ($request->total_paid / $request->total_with_interest) * 100;
                    @endphp
                    <div class="progress-bar bg-success" style="width: {{ $paymentProgress }}%;">
                        {{ number_format($paymentProgress, 2) }}%
                    </div>
                </div>
            </div>

            {{-- หลอดความคืบหน้าระยะเวลาผ่อน --}}
            <div class="mb-3">
                <strong>ระยะเวลาการผ่อน:</strong> {{ $daysPassed }} / {{ $request->installment_period }} วัน
                <div class="progress mt-2">
                    @php
                        $timeProgress = ($daysPassed / $request->installment_period) * 100;
                    @endphp
                    <div class="progress-bar bg-info" style="width: {{ $timeProgress }}%;">
                        {{ number_format($timeProgress, 2) }}%
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-info" data-bs-toggle="collapse" data-bs-target="#bankInfo{{ $request->id }}">🏦 ข้อมูลธนาคาร</button>
                <button class="btn btn-warning" data-bs-toggle="collapse" data-bs-target="#uploadSlip{{ $request->id }}">📤 อัพโหลดสลิป</button>
            </div>

            <div class="collapse mt-3" id="bankInfo{{ $request->id }}">
                <div class="card card-body">
                    @forelse($bankAccounts as $bank)
                        <div class="d-flex align-items-center mb-2">
                            <img src="{{ asset('storage/'.$bank->logo) }}" width="50" class="me-3">
                            <div>
                                <strong>{{ $bank->bank_name }}</strong><br>
                                {{ $bank->account_name }}<br>{{ $bank->account_number }}
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-secondary">ไม่มีข้อมูลบัญชีธนาคาร</div>
                    @endforelse
                </div>
            </div>

            <div class="collapse mt-3" id="uploadSlip{{ $request->id }}">
                <form action="{{ route('payments.upload-proof', $request->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="number" name="amount_paid" class="form-control mb-2" required placeholder="จำนวนเงินที่โอน (บาท)">
                    <input type="file" name="payment_proof" class="form-control mb-2" required>
                    <button class="btn btn-primary">✅ ส่งหลักฐาน</button>
                </form>
            </div>
        </div>
    </div>

    @empty
        <div class="alert alert-warning">⚠️ ไม่มีข้อมูลการผ่อนทองที่อนุมัติค่ะ</div>
    @endforelse

    {{-- ประวัติการชำระเงิน --}}
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-secondary text-white">
            📋 ประวัติการชำระเงินล่าสุด
        </div>
        <div class="card-body">
            @if($payments->count())
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>วัน/เวลา</th>
                        <th>จำนวนเงิน</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ number_format($payment->amount_paid, 2) }} บาท</td>
                        <td>
                            @if($payment->status == 'approved')
                                <span class="badge bg-success">อนุมัติแล้ว</span>
                            @elseif($payment->status == 'pending')
                                <span class="badge bg-warning">รอตรวจสอบ</span>
                            @else
                                <span class="badge bg-danger">ถูกปฏิเสธ</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
                <div class="alert alert-secondary">ยังไม่มีประวัติการชำระเงินค่ะ</div>
            @endif
        </div>
    </div>
</div>
<!-- Include Bottom Navigation -->
@include('partials.bottom-nav')

@endsection
