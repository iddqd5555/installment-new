@extends('layouts.app')
@section('content')
<div class="container py-5">
    <h2>ประวัติ QR Payment</h2>
    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>QR Ref</th>
                <th>ยอดเงิน</th>
                <th>สถานะ</th>
                <th>เวลาสร้าง</th>
            </tr>
        </thead>
        <tbody>
        @foreach($logs as $log)
            <tr>
                <td>{{ $log->qr_ref }}</td>
                <td>{{ number_format($log->amount, 2) }}</td>
                <td>
                    @if($log->status === 'paid')
                        <span class="badge bg-success">จ่ายแล้ว</span>
                    @elseif($log->status === 'pending')
                        <span class="badge bg-warning text-dark">รอจ่าย</span>
                    @else
                        <span class="badge bg-danger">void</span>
                    @endif
                </td>
                <td>{{ $log->created_at }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
