@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>📌 รายการสมาชิกที่รออนุมัติ</h2>

    @if($users->count())
        @foreach($users as $user)
        <div class="card mb-3 p-3 shadow-sm">
            <h5>{{ $user->name }} {{ $user->surname }}</h5>
            <p><strong>เบอร์โทรศัพท์:</strong> {{ $user->phone }}</p>
            <p><strong>เลขบัตรประชาชน:</strong> {{ $user->id_card_number }}</p>

            <form action="{{ route('admin.users.verify', $user->id) }}" method="POST">
                @csrf
                <button class="btn btn-success">✅ อนุมัติสมาชิก</button>
            </form>
        </div>
        @endforeach
    @else
        <div class="alert alert-info">📢 ยังไม่มีสมาชิกที่รออนุมัติค่ะ</div>
    @endif
</div>
@endsection
