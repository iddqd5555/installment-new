@extends('layouts.app')

@section('content')
<div class="container py-5">

    <h3 class="mb-4">📌 รายการคำขอผ่อนทอง (Member)</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>ชื่อผู้ขอผ่อน</th>
                <th>เบอร์โทร</th>
                <th>เลขบัตรประชาชน</th> <!-- ✅ เพิ่ม -->
                <th>จำนวนทอง (บาท)</th>
                <th>ราคาทอง (บาท)</th> <!-- ✅ เพิ่ม -->
                <th>จำนวนวัน</th>
                <th>สถานะ</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests->whereNotNull('user_id') as $request)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $request->user->name ?? '-' }}</td>
                    <td>{{ $request->user->phone ?? '-' }}</td>
                    <td>{{ $request->user->id_card_number ?? '-' }}</td>
                    <td>{{ number_format($request->gold_amount, 2) }}</td>
                    <td>{{ number_format($request->approved_gold_price, 2) }}</td> <!-- ✅ ใช้ราคาที่อนุมัติ -->
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
                    <td colspan="9" class="text-center">ไม่มีข้อมูลคำขอจากสมาชิก</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <hr class="my-5">

    <h3 class="mb-4">📌 รายการคำขอผ่อนทอง (Guest)</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>ชื่อผู้ขอผ่อน (Guest)</th>
                <th>เบอร์โทร</th>
                <th>เลขบัตรประชาชน</th> <!-- ✅ เพิ่ม -->
                <th>จำนวนทอง (บาท)</th>
                <th>ราคาทอง (บาท)</th> <!-- ✅ เพิ่ม -->
                <th>จำนวนวัน</th>
                <th>สถานะ</th>
                <th>จัดการสถานะ</th>
            </tr>
        </thead>
        <tbody>
        @forelse($requests->whereNull('user_id') as $request)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $request->fullname }}</td>
                <td>{{ $request->phone }}</td>
                <td>{{ $request->id_card }}</td> <!-- ✅ ดึงเลขบัตรประชาชน -->
                <td>{{ number_format($request->gold_amount, 2) }}</td>
                <td>{{ number_format($request->total_gold_price, 2) }}</td> <!-- ✅ ราคาทองที่ Guest กรอก -->
                <td>{{ $request->installment_period }}</td>
                <td>
                    @if ($request->status == 'checked')
                        <span class="badge bg-info">ตรวจสอบแล้ว</span>
                    @elseif ($request->status == 'pending')
                        <span class="badge bg-warning text-dark">รอตรวจสอบ</span>
                    @else
                        <span class="badge bg-danger">ไม่ผ่านการตรวจสอบ</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('admin.installments.updateGuestStatus', $request->id) }}" method="POST">
                        @csrf
                        <select name="status" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="pending" {{ $request->status == 'pending' ? 'selected' : '' }}>รอตรวจสอบ</option>
                            <option value="checked" {{ $request->status == 'checked' ? 'selected' : '' }}>ตรวจสอบแล้ว</option>
                            <option value="rejected" {{ $request->status == 'rejected' ? 'selected' : '' }}>ไม่ผ่านการตรวจสอบ</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">อัปเดต</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">ไม่มีข้อมูลคำขอจาก Guest</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
