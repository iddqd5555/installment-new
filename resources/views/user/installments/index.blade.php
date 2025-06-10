@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4 text-success">📱 คำขอผ่อนของฉัน</h2>

    <div class="mb-4">
        <a href="{{ route('user.installments.create') }}" class="btn-rounded">➕ ส่งคำขอผ่อนใหม่</a>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-success">
            <tr>
                <th>ID</th>
                <th>สินค้า</th>
                <th>ราคา</th>
                <th>จำนวนเดือน</th>
                <th>สถานะ</th>
                <th class="text-center">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requests as $request)
                <tr>
                    <td>{{ $request->id }}</td>
                    <td>{{ $request->product_name }}</td>
                    <td>{{ number_format($request->price, 2) }} บาท</td>
                    <td>{{ $request->installment_months }}</td>
                    <td>
                        <span class="badge {{ $request->status == 'approved' ? 'bg-success' : ($request->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('user.installments.edit', $request->id) }}" class="btn-rounded">แก้ไข</a>
                            <form action="{{ route('user.installments.destroy', $request->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-rounded bg-danger">ลบ</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach

            @if($requests->isEmpty())
                <tr>
                    <td colspan="6" class="text-center">ไม่มีคำขอผ่อนของคุณในระบบ</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection
