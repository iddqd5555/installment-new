@extends('layouts.app')

@section('content')
<div class="container py-5">
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
                    <li>{{ $notification->data['message'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @forelse($requests as $request)
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">📌 ผ่อนทอง ({{ number_format($request->gold_amount, 2) }} บาท)</h5>

                {{-- หลอดความคืบหน้าจำนวนเงิน --}}
                <div class="card bg-success text-white mb-3">
                    <div class="card-body">
                        <strong>ชำระแล้ว:</strong> {{ number_format($request->total_paid, 2) }} บาท<br>
                        <strong>คงเหลือ:</strong> {{ number_format($request->remaining_amount, 2) }} บาท
                        <div class="progress mt-2">
                            @php
                                $paymentProgress = ($request->total_paid / $request->total_with_interest) * 100;
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

                <p><strong>📅 เดือนที่เหลือ:</strong> {{ $request->remaining_months }} เดือน</p>
                <p><strong>💵 ยอดที่ต้องชำระครั้งถัดไป:</strong> {{ number_format($request->next_payment_amount, 2) }} บาท</p>
                <p><strong>📆 วันชำระครั้งถัดไป:</strong> {{ optional($request->next_payment_date)->format('d/m/Y') ?? 'ยังไม่กำหนด' }}</p>

                <button class="btn btn-success" type="button" data-bs-toggle="collapse"
                    data-bs-target="#payInfo{{ $request->id }}" aria-expanded="false">
                    ชำระเงิน
                </button>

                <button class="btn btn-warning" type="button" data-bs-toggle="collapse"
                    data-bs-target="#uploadSlip{{ $request->id }}" aria-expanded="false">
                    อัพโหลดสลิป
                </button>

                <!-- ข้อมูลการชำระเงิน -->
                <div class="collapse mt-3" id="payInfo{{ $request->id }}">
                    <div class="card card-body">
                        <p><strong>บัญชีสำหรับโอนเงิน:</strong> ธนาคารที่แอดมินกำหนดไว้</p>
                        <p><strong>ยอดเงินที่ต้องชำระ:</strong> {{ number_format($request->next_payment_amount, 2) }} บาท</p>
                        @if($request->next_payment_date && now()->gt($request->next_payment_date))
                            <p class="text-danger">⚠️ เลยกำหนดชำระเงินแล้ว อาจมีค่าปรับเพิ่มเติม</p>
                        @endif
                    </div>
                </div>

                <!-- แบบฟอร์มอัพโหลดสลิป -->
                <div class="collapse mt-3" id="uploadSlip{{ $request->id }}">
                    <div class="card card-body">
                        <form action="{{ route('payments.upload-proof', $request->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">จำนวนเงินที่โอน (บาท)</label>
                                <input type="number" class="form-control" name="amount_paid" step="0.01" required>
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

                <p>🌟 ราคาทองที่อนุมัติ: <strong>{{ number_format($request->approved_gold_price, 2) }} บาท</strong></p>
                <p>💳 ราคารวมทองคำ: <strong>{{ number_format($request->total_gold_price, 2) }} บาท</strong></p>
                <p>📌 ดอกเบี้ย ({{ $request->interest_rate }}%): <strong>{{ number_format($request->interest_amount, 2) }} บาท</strong></p>
                <p>💰 เงินรวมดอกเบี้ย: <strong>{{ number_format($request->total_with_interest, 2) }} บาท</strong></p>
            </div>
        </div>
     @empty
        <div class="alert alert-warning">⚠️ ไม่มีข้อมูลการผ่อนที่อนุมัติค่ะ</div>
    @endforelse

    {{-- ประวัติการชำระเงิน ควรอยู่นอก @forelse เพื่อให้แสดงเสมอ --}}
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5>📌 ประวัติการชำระเงิน</h5>
            @if($payments->count() > 0)
                <table class="table table-bordered table-striped mt-3">
                    <thead class="table-success">
                        <tr>
                            <th>วันที่ชำระ</th>
                            <th>เวลา</th>
                            <th>ยอดเงิน</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td>{{ $payment->created_at->format('d/m/Y') }}</td>
                                <td>{{ $payment->created_at->format('H:i:s') }}</td>
                                <td>{{ number_format($payment->amount, 2) }} บาท</td>
                                <td>
                                    @if($payment->status == 'approved')
                                        <span class="text-success">✅ สำเร็จ</span>
                                    @elseif($payment->status == 'pending')
                                        <span class="text-warning">🕒 รออนุมัติ</span>
                                    @else
                                        <span class="text-danger">❌ ไม่สำเร็จ</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted mt-3">ยังไม่มีประวัติการชำระเงินค่ะ</p>
            @endif
        </div>
    </div>
</div>
@endsection