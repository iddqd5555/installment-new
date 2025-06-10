@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2>🔔 จัดการหลักฐานการชำระเงิน</h2>
    <table class="table table-bordered">
        <thead class="table-success">
            <tr>
                <th>ลูกค้า</th>
                <th>จำนวนเงิน</th>
                <th>วันครบกำหนด</th>
                <th>หลักฐานการชำระ</th>
                <th>สถานะ</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
        @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->installmentRequest->user->name }}</td>
                <td>{{ number_format($payment->amount, 2) }}</td>
                <td>{{ $payment->due_date }}</td>
                <td>
                    @if($payment->payment_proof)
                        <a href="{{ asset($payment->payment_proof) }}" target="_blank">ดูหลักฐาน</a>
                    @else
                        ไม่มีหลักฐาน
                    @endif
                </td>
                <td>{{ ucfirst($payment->payment_status) }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.payments.approve', $payment->id) }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <button class="btn btn-success btn-sm">อนุมัติ</button>
                    </form>
                    <form method="POST" action="{{ route('admin.payments.reject', $payment->id) }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <button class="btn btn-danger btn-sm">ปฏิเสธ</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
