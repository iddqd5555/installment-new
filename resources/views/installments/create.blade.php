@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto bg-white shadow p-6 rounded-lg">
    <h2 class="text-xl font-bold text-kplus-green mb-4">สมัครขอผ่อนทอง</h2>

    @if(session('success'))
        <div class="text-green-500 mb-4">{{ session('success') }}</div>
    @endif

    <form action="{{ route('installments.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="block">ชื่อ-นามสกุล</label>
            <input type="text" name="fullname" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block">เลขบัตรประชาชน</label>
            <input type="text" name="id_card" class="w-full border p-2 rounded" maxlength="13" required>
        </div>

        <div class="mb-4">
            <label class="block">เบอร์โทรศัพท์</label>
            <input type="text" name="phone" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block">จำนวนทอง (บาท)</label>
            <input type="number" step="0.01" name="gold_amount" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block">ราคาทอง (บาท)</label>
            <input type="number" step="0.01" name="gold_price" class="w-full border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label class="block">จำนวนวันผ่อน</label>
            <select name="installment_period" class="w-full border p-2 rounded" required>
                <option value="30">30 วัน</option>
                <option value="45">45 วัน</option>
                <option value="60">60 วัน</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block">อัปโหลดภาพบัตรประชาชน</label>
            <input type="file" name="id_card_image" class="w-full border p-2 rounded" required>
        </div>

        <button type="submit" class="bg-kplus-green text-white px-4 py-2 rounded">ส่งคำขอผ่อนทอง</button>
    </form>
</div>
@endsection
