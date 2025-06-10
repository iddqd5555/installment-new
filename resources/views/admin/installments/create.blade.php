@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h3 class="mb-4">เพิ่มสินค้าใหม่</h3>
    <form action="{{ route('admin.installments.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label>ชื่อสินค้า</label>
            <input type="text" name="product_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>ราคา</label>
            <input type="number" name="price" step="0.01" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>จำนวนเดือนผ่อน</label>
            <input type="number" name="months" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label>รูปสินค้า</label>
            <input type="file" name="product_image" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">บันทึกสินค้า</button>
        <a href="{{ route('admin.installments.index') }}" class="btn btn-secondary">ยกเลิก</a>
    </form>
</div>
@endsection
