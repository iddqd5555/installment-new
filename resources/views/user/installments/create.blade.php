@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card p-4">
        <h2 class="mb-4 text-success">➕ สร้างคำขอผ่อนใหม่</h2>

        <form method="POST" action="{{ route('user.installments.store') }}" enctype="multipart/form-data">
            @csrf

        <div class="mb-3">
            <label class="form-label">ชื่อสินค้า</label>
            <input type="text" name="product_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">ราคา (บาท)</label>
            <input type="number" name="price" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">จำนวนเดือนที่ต้องการผ่อน</label>
            <input type="number" name="installment_months" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">รูปสินค้า</label>
            <input type="file" name="product_image" class="form-control" required>
        </div>

        <button type="submit" class="btn-rounded mt-3">ส่งคำขอ</button>
        </form>
    </div>
</div>
@endsection
