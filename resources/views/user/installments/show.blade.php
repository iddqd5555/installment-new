@extends('layouts.app')

@section('content')
<div class="card shadow rounded-4">
    <div class="card-header bg-kplus-green text-white rounded-top-4">
        รายละเอียดการผ่อนสินค้า
    </div>
    <div class="card-body">
        <p><strong>ชื่อสินค้า:</strong> {{ $installment->product_name }}</p>
        <p><strong>ราคา:</strong> {{ number_format($installment->price, 2) }} บาท</p>
        <p><strong>จำนวนงวด:</strong> {{ $installment->installment_months }} งวด</p>
        <p><strong>สถานะ:</strong>
            <span class="badge 
                {{ $installment->status == 'approved' ? 'bg-success' : ($installment->status == 'pending' ? 'bg-warning' : 'bg-danger') }}">
                {{ ucfirst($installment->status) }}
            </span>
        </p>
        <p><strong>วันที่ทำรายการ:</strong> {{ $installment->created_at->format('d/m/Y H:i') }}</p>
    </div>
</div>
@endsection
