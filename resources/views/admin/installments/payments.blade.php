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
                    @csrf
                    @method('PATCH') <!-- ✅ ต้องมีชัดเจน -->
                    <button class="btn btn-success btn-sm">อนุมัติ</button>
                </form>

                <form action="{{ route('admin.payments.reject', $payment->id) }}" method="POST">
                    @csrf
                    @method('PATCH') <!-- ✅ ต้องมีชัดเจน -->
                    <button class="btn btn-danger btn-sm">ปฏิเสธ</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
