@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h3>📌 รายการสลิปที่รออนุมัติ</h3>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead class="table-success">
            <tr>
                <th>ชื่อผู้ใช้</th>
                <th>จำนวนเงินที่โอน</th>
                <th>วันที่อัปโหลด</th>
                <th>ดูสลิป</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendingPayments as $payment)
            <tr>
                <td>{{ $payment->installmentRequest->user->name }}</td>
                <td>{{ number_format($payment->amount_paid, 2) }} บาท</td>
                <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    <a href="{{ asset('storage/'.$payment->payment_proof) }}" target="_blank">ดูสลิป</a>
                </td>
                <td>
                    <form action="{{ route('admin.payments.approve', $payment->id) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าจะอนุมัติสลิปจำนวน {{ number_format($payment->amount_paid, 2) }} บาทนี้?')">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-success btn-sm">✅ อนุมัติ</button>
                    </form>

                    <form action="{{ route('admin.payments.reject', $payment->id) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าจะปฏิเสธสลิปจำนวน {{ number_format($payment->amount_paid, 2) }} บาทนี้?')">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-danger btn-sm">❌ ปฏิเสธ</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
