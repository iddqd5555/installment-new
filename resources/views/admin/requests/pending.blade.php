@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>📌 รายการคำขอผ่อนทอง (รออนุมัติ)</h2>

    @if($requests->count())
        @foreach($requests as $request)
        <div class="card mb-3 p-3 shadow-sm">
            <h5>{{ $request->fullname }}</h5>
            <p><strong>เบอร์โทรศัพท์:</strong> {{ $request->phone }}</p>
            <p><strong>ประเภททอง:</strong> {{ $request->gold_type }}</p>
            <p><strong>จำนวนบาททอง:</strong> {{ $request->gold_amount }} บาท</p>
            <p><strong>จำนวนเดือนผ่อน:</strong> {{ $request->installment_period }} เดือน</p>
            <p><strong>สถานะ:</strong> {{ ucfirst($request->status) }}</p>

            <form action="{{ route('admin.requests.verify', $request->id) }}" method="POST">
                @csrf
                <button class="btn btn-success mt-2">✅ อนุมัติคำขอ</button>
            </form>
        </div>
        @endforeach
    @else
        <div class="alert alert-info">📢 ยังไม่มีรายการคำขอที่รออนุมัติค่ะ</div>
    @endif
</div>
@endsection
