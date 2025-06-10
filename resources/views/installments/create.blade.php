@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto bg-white shadow p-6 rounded-lg">
    <h2 class="text-xl font-bold text-kplus-green mb-4">สมัครผ่อนสินค้า</h2>

    @if(session('success'))
        <div class="text-green-500 mb-4">{{ session('success') }}</div>
    @endif

    <form action="{{ route('installments.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="block">ชื่อสินค้า</label>
            <input type="text" name="product_name" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block">ราคาสินค้า</label>
            <input type="number" step="0.01" name="product_price" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block">จำนวนเดือนที่ต้องการผ่อน</label>
            <input type="number" name="installment_months" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block">อัปโหลดภาพบัตรประชาชน</label>
            <input type="file" name="id_card_image" class="w-full border p-2 rounded" required>
        </div>

        <button type="submit" class="bg-kplus-green text-white px-4 py-2 rounded">ส่งคำขอผ่อน</button>
    </form>
</div>
@endsection
