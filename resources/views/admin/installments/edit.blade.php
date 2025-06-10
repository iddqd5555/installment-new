@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h3 class="text-center">✏️ แก้ไขคำขอผ่อนทอง (Admin)</h3>

    <form action="{{ route('admin.installments.update', $installment->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="mb-3">
            <label for="gold_amount">น้ำหนักทอง (บาท)</label>
            <input type="number" step="0.01" name="gold_amount" class="form-control" value="{{ $installment->gold_amount }}" required>
        </div>

        <div class="mb-3">
            <label for="approved_gold_price">ราคาทองที่อนุมัติ (บาท)</label>
            <input type="number" step="0.01" name="approved_gold_price" class="form-control" value="{{ $installment->approved_gold_price }}" required>
        </div>

        <div class="mb-3">
            <label for="installment_period">ระยะเวลาผ่อน (เดือน)</label>
            <select name="installment_period" class="form-select">
                <option value="6" {{ $installment->installment_period == 6 ? 'selected' : '' }}>6 เดือน</option>
                <option value="12" {{ $installment->installment_period == 12 ? 'selected' : '' }}>12 เดือน</option>
                <option value="18" {{ $installment->installment_period == 18 ? 'selected' : '' }}>18 เดือน</option>
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
