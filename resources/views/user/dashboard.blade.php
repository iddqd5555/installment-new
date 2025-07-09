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
        $today = \Carbon\Carbon::today();
        $dueToday = $installment && $installment->installmentPayments
            ? $installment->installmentPayments->filter(fn($p) => \Carbon\Carbon::parse($p->payment_due_date)->isSameDay($today))->sum('amount') : 0;
    @endphp

    @if($installment)
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
                        💰 <strong>ยอดชำระล่วงหน้า</strong><br>{{ number_format($installment->advance_payment, 2) }} บาท
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        📅 <strong>วันชำระครั้งถัดไป</strong><br>
                        {{ $installment->next_payment_date ? \Carbon\Carbon::parse($installment->next_payment_date)->format('d/m/Y') : 'ยังไม่กำหนด' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-danger">
                        ⚠️ <strong>ค่าปรับสะสม</strong><br>{{ number_format($installment->total_penalty, 2) }} บาท
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <strong>ชำระแล้วทั้งหมด:</strong> {{ number_format($installment->total_paid, 2) }} / {{ number_format($installment->total_with_interest, 2) }} บาท
                <div class="progress mt-2">
                    @php
                        $paymentProgress = ($installment->total_with_interest > 0)
                            ? ($installment->total_paid / $installment->total_with_interest) * 100 : 0;
                    @endphp
                    <div class="progress-bar bg-success" style="width: {{ $paymentProgress }}%;">
                        {{ number_format($paymentProgress, 2) }}%
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <strong>ระยะเวลาการผ่อน:</strong>
                @php
                    $firstApprovedDate = $installment->first_approved_date ?? $installment->start_date;
                    $daysPassed = $firstApprovedDate ? \Carbon\Carbon::parse($firstApprovedDate)->diffInDays(\Carbon\Carbon::today()) : 0;
                    $installmentPeriod = $installment->installment_period ?? 0;
                    $timeProgress = ($installmentPeriod > 0) ? min(100, ($daysPassed / $installmentPeriod) * 100) : 0;
                @endphp
                {{ $daysPassed }} / {{ $installmentPeriod }} วัน
                <div class="progress mt-2">
                    <div class="progress-bar bg-info" style="width: {{ $timeProgress }}%;">
                        {{ number_format($timeProgress, 2) }}%
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ประวัติการชำระเงิน --}}
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-secondary text-white">
            📋 ประวัติการชำระเงินล่าสุด
        </div>
        <div class="card-body">
            @if($installment && $installment->installmentPayments->count())
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>วัน/เวลา</th>
                        <th>จำนวนเงิน</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($installment->installmentPayments as $payment)
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

    {{-- เพิ่มประวัติเติมเงิน/QR Payment --}}
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-info text-white">
            💸 ประวัติเติมเงิน/จ่าย QR ล่าสุด
        </div>
        <div class="card-body">
            @if($qrLogs->count())
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>วัน/เวลา</th>
                        <th>ยอด</th>
                        <th>สถานะ</th>
                        <th>QR Ref</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($qrLogs as $log)
                    <tr>
                        <td>{{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ number_format($log->amount, 2) }} บาท</td>
                        <td>
                            @if($log->status === 'paid')
                                <span class="badge bg-success">จ่ายแล้ว</span>
                            @elseif($log->status === 'pending')
                                <span class="badge bg-warning text-dark">รอจ่าย</span>
                            @else
                                <span class="badge bg-danger">void</span>
                            @endif
                        </td>
                        <td>{{ $log->qr_ref ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
                <div class="alert alert-secondary">ยังไม่มีประวัติเติมเงิน/QR Payment</div>
            @endif
        </div>
    </div>
</div>
@include('partials.bottom-nav')
@endsection
