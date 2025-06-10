@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">📜 ประวัติคำสั่งซื้อของคุณ</h2>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>สินค้า</th>
                <th>ราคา</th>
                <th>สถานะ</th>
                <th>วันที่ขอผ่อน</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->product_name ?? 'ทองรูปพรรณ' }}</td>
                <td>{{ number_format($order->price ?? $order->gold_amount, 2) }} บาท</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
