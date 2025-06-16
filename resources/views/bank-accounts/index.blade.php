@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h3 class="mb-4">📋 ข้อมูลบัญชีธนาคารสำหรับชำระเงิน</h3>
    @foreach($bankAccounts as $account)
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <img src="{{ asset('storage/'.$account->logo) }}" width="60" class="mb-2">
                <h5>{{ $account->bank_name }}</h5>
                <p><strong>ชื่อบัญชี:</strong> {{ $account->account_holder }}</p>
                <p><strong>เลขบัญชี:</strong> {{ $account->account_number }}</p>
            </div>
        </div>
    @endforeach
</div>
@endsection
