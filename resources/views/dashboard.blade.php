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

    @php
        $installment = $installmentRequests->first();
        $today = \Carbon\Carbon::today()->format('Y-m-d');

        $dueToday = $installment->installmentPayments
            ->where('payment_due_date', $today)
            ->sum('amount') ?: 0;

        $totalPaid = $installment->installmentPayments
            ->where('status', 'approved')
            ->sum('amount_paid') ?: 0;

        $penaltyPerDay = $installment->daily_penalty ?? 0;
        $overdue = $installment->installmentPayments
            ->where('status', 'pending')
            ->where('payment_due_date', '<', $today)
            ->count();
        $totalPenalty = $overdue * $penaltyPerDay;

        $advancePayment = $installment->advance_payment ?? 0;

        $paymentHistory = $installment->installmentPayments
            ->sortByDesc('payment_due_date')
            ->take(20);

        $firstApprovedDate = $installment->first_approved_date ?? $installment->start_date;
        $daysPassed = $firstApprovedDate ? \Carbon\Carbon::parse($firstApprovedDate)->diffInDays(\Carbon\Carbon::today()) : 0;
        $installmentPeriod = $installment->installment_period ?? 0;

        $nextPayment = $installment->installmentPayments
            ->where('status', 'pending')
            ->where('payment_due_date', '>=', $today)
            ->sortBy('payment_due_date')
            ->first();
        $nextPaymentDate = $nextPayment ? $nextPayment->payment_due_date : '-';
    @endphp

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            📌 ผ่อนทองจำนวน: <strong>{{ number_format($installment->gold_amount ?? 0, 2) }} บาท</strong>
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
                        💰 <strong>ยอดชำระล่วงหน้า</strong><br>{{ number_format($advancePayment, 2) }} บาท
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        📅 <strong>วันชำระครั้งถัดไป</strong><br>
                        {{ $nextPaymentDate !== '-' ? \Carbon\Carbon::parse($nextPaymentDate)->format('d/m/Y') : 'ยังไม่กำหนด' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-danger">
                        ⚠️ <strong>ค่าปรับสะสม</strong><br>{{ number_format($totalPenalty, 2) }} บาท
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <strong>ชำระแล้วทั้งหมด:</strong> {{ number_format($totalPaid, 2) }} / {{ number_format($installment->total_with_interest, 2) }} บาท
                <div class="progress mt-2">
                    @php
                        $paymentProgress = ($installment->total_with_interest > 0)
                            ? ($totalPaid / $installment->total_with_interest) * 100 : 0;
                    @endphp
                    <div class="progress-bar bg-success" style="width: {{ $paymentProgress }}%;">
                        {{ number_format($paymentProgress, 2) }}%
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <strong>ระยะเวลาการผ่อน:</strong>
                {{ $daysPassed }} / {{ $installmentPeriod }} วัน
                <div class="progress mt-2">
                    @php
                        $timeProgress = ($installmentPeriod > 0)
                            ? min(100, ($daysPassed / $installmentPeriod) * 100) : 0;
                    @endphp
                    <div class="progress-bar bg-info" style="width: {{ $timeProgress }}%;">
                        {{ number_format($timeProgress, 2) }}%
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ประวัติการชำระเงิน --}}
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-secondary text-white">
            📋 ประวัติการชำระเงินล่าสุด
        </div>
        <div class="card-body">
            @if($paymentHistory->count())
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>วัน/เวลา</th>
                        <th>จำนวนเงิน</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentHistory as $payment)
                    <tr>
                        <td>{{ optional($payment->payment_due_date) ? \Carbon\Carbon::parse($payment->payment_due_date)->format('d/m/Y H:i') : '-' }}</td>
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
@include('partials.bottom-nav')
@endsection
