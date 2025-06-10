@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h3 class="mb-4">📌 รายการคำขอผ่อนสินค้า</h3>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>ชื่อผู้ขอผ่อน</th>
                <th>เบอร์โทร</th>
                <th>จำนวนทอง (บาท)</th>
                <th>จำนวนเดือน</th>
                <th>สถานะ</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $request)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $request->fullname ?? ($request->user->name . ' ' . $request->user->surname) }}</td>
                    <td>{{ $request->phone ?? $request->user->phone }}</td>
                    <td>{{ $request->gold_amount }}</td>
                    <td>{{ $request->installment_period }}</td>
                    <td>
                        @if ($request->status == 'approved')
                            <span class="badge bg-success">อนุมัติแล้ว</span>
                        @elseif ($request->status == 'pending')
                            <span class="badge bg-warning text-dark">รออนุมัติ</span>
                        @else
                            <span class="badge bg-danger">ปฏิเสธ</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.installments.edit', $request->id) }}" class="btn btn-primary btn-sm">แก้ไข/อนุมัติ</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">ไม่มีข้อมูลคำขอผ่อน</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
