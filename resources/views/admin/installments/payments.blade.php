@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>อนุมัติหลักฐานการชำระเงิน</h2>

    @foreach ($payments as $payment)
        <div class="card mb-3">
            <div class="card-body">
                <p>รายการ: ผ่อนทอง ({{ $payment->installmentRequest->gold_amount }} บาท)</p>
                <img src="{{ asset('storage/payment_proofs/'.$payment->payment_proof) }}" width="200px">

                <form action="{{ route('admin.payments.approve', $payment->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <button class="btn btn-success">✅ อนุมัติ</button>
                </form>

                <form action="{{ route('admin.payments.reject', $payment->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <textarea name="admin_notes" class="form-control" placeholder="เหตุผลการปฏิเสธ"></textarea>
                    <button class="btn btn-danger">❌ ปฏิเสธ</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
