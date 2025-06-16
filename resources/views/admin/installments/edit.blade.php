@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h3 class="text-center">✏️ แก้ไขคำขอผ่อนทอง (Admin)</h3>

    <div class="alert alert-info">
        💎 ราคาทองวันนี้จาก API คือ: <strong>{{ number_format($goldPrices['ornament_sell'] ?? 'ไม่สามารถดึงราคาได้', 2) }}</strong> บาท
    </div>

    <form action="{{ route('admin.installments.update', $installment->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label for="gold_amount">น้ำหนักทอง (บาท)</label>
            <input type="number" step="0.01" name="gold_amount" class="form-control" value="{{ $installment->gold_amount }}" required>
        </div>

        <div class="mb-3">
            <label for="approved_gold_price">ราคาทองที่อนุมัติ (บาท)</label>
            <input type="number" step="0.01" name="approved_gold_price" class="form-control" 
            value="{{ $goldPrices['ornament_sell'] ?? $installment->approved_gold_price }}" required>
        </div>

        <div class="mb-3">
            <label for="installment_period">ระยะเวลาผ่อน (วัน)</label>
            <select name="installment_period" class="form-select">
                <option value="30" {{ $installment->installment_period == 30 ? 'selected' : '' }}>30 วัน</option>
                <option value="45" {{ $installment->installment_period == 45 ? 'selected' : '' }}>45 วัน</option>
                <option value="60" {{ $installment->installment_period == 60 ? 'selected' : '' }}>60 วัน</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="status">สถานะคำขอ</label>
            <select name="status" class="form-select">
                <option value="pending" {{ $installment->status == 'pending' ? 'selected' : '' }}>รออนุมัติ</option>
                <option value="approved" {{ $installment->status == 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                <option value="rejected" {{ $installment->status == 'rejected' ? 'selected' : '' }}>ปฏิเสธ</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
    </form>
</div>
@endsection
