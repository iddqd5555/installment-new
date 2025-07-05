@extends('layouts.app')

@section('content')
<div class="container py-5 mb-5">
    <h2>🔔 แจ้งเตือนทั้งหมด</h2>
    <ul>
        @forelse($notifications as $notification)
            <li>
                {{ $notification->data['message'] ?? 'ไม่มีข้อความ' }}
                <small class="text-muted">({{ $notification->created_at->format('d/m/Y H:i') }})</small>
            </li>
        @empty
            <li>ไม่มีแจ้งเตือนใหม่ค่ะ</li>
        @endforelse
    </ul>

    {{ $notifications->links() }}
</div>
@include('partials.bottom-nav')
@endsection
