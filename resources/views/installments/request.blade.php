@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="text-success">ส่งคำขอผ่อนสินค้า</h2>

    <form action="{{ route('installments.request.store', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label">ชื่อสินค้า</label>
            <input type="text" class="form-control" value="{{ $product->product_name }}" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label">ราคาสินค้า (บาท)</label>
            <input type="text" class="form-control" value="{{ number_format($product->price, 2) }}" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label">ระยะเวลาผ่อน (เดือน)</label>
            <input type="number" name="installment_months" class="form-control" required min="1" max="{{ $product->installment_months }}">
        </div>

        <div class="mb-3">
            <label class="form-label">อัปโหลดรูปบัตรประชาชน (ยืนยันตัวตน)</label>
            <input type="file" name="id_card_image" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">ส่งคำขอ</button>
    </form>
</div>
@endsection
