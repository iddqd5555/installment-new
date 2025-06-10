@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card p-4">
        <h2 class="mb-4 text-success">➕ สร้างคำขอผ่อนใหม่</h2>

        <form method="POST" action="{{ route('user.installments.store') }}" enctype="multipart/form-data">
            @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">ชื่อสินค้า</label>
            <input type="text" name="product_name" class="form-control" value="{{ $installment->product_name }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">ราคา</label>
            <input type="number" name="price" class="form-control" value="{{ $installment->price }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">จำนวนเดือนผ่อน</label>
            <input type="number" name="installment_months" class="form-control" value="{{ $installment->installment_months }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">รูปภาพสินค้า (ถ้าต้องการเปลี่ยน)</label>
            <input type="file" name="product_image" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">บันทึกการเปลี่ยนแปลง</button>
        </form>
    </div>
</div>
@endsection
