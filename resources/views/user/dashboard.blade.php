@extends('layouts.app')
@section('content')
<div class="container py-5">
    <h2>งวดผ่อนของคุณ</h2>
    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>งวดที่</th>
                <th>ยอดที่ต้องจ่าย</th>
                <th>สถานะ</th>
                <th>ครบกำหนด</th>
                <th>QR</th>
            </tr>
        </thead>
        <tbody>
        @foreach($payments as $pay)
            <tr>
                <td>{{ $pay->id }}</td>
                <td>{{ number_format($pay->amount, 2) }}</td>
                <td>
                    @if($pay->payment_status === 'paid')
                        <span class="badge bg-success">จ่ายแล้ว</span>
                    @elseif($pay->payment_status === 'pending')
                        <span class="badge bg-warning text-dark">รอจ่าย</span>
                    @else
                        <span class="badge bg-danger">void</span>
                    @endif
                </td>
                <td>{{ $pay->due_date }}</td>
                <td>
                    @if($pay->payment_status !== 'paid')
                        <a href="{{ route('user.create_qr', $pay->id) }}" class="btn btn-primary btn-sm">สร้าง QR</a>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <a href="{{ route('user.qr_history') }}" class="btn btn-outline-info mt-4">ดูประวัติ QR Payment</a>
</div>
@endsection
