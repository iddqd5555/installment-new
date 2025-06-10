@extends('layouts.app')

@section('content')
<div class="container py-5">
    @if(Auth::check())
        <!-- ส่วนนี้แสดงสำหรับผู้ที่ล็อกอินแล้ว -->
        <h2 class="mb-4 text-success">📱 ฟอร์มคำขอผ่อนทอง</h2>

        <div class="card shadow-sm p-4">
            <form method="POST" action="{{ route('gold.request.store') }}">
                @csrf

                <!-- ชื่อ-นามสกุล -->
                <div class="form-group">
                    <label>ชื่อ-นามสกุล</label>
                    <input type="text" name="fullname" value="{{ auth()->user()->name }}" readonly>
                </div>

                <!-- เบอร์โทร -->
                <div class="form-group">
                    <label>เบอร์โทรศัพท์</label>
                    <input type="text" name="phone" value="{{ auth()->user()->phone }}" readonly>
                </div>

                <!-- จำนวนบาททอง -->
                <div class="form-group">
                    <label>จำนวนทอง (บาท)</label>
                    <input type="number" class="form-control" name="gold_amount" required>
                </div>

                <!-- จำนวนเดือนผ่อน -->
                <div class="form-group">
                    <label>จำนวนเดือนที่ต้องการผ่อน</label>
                    <select name="installment_period" class="form-control" required>
                        <option value="3">3 เดือน</option>
                        <option value="6">6 เดือน</option>
                        <option value="12">12 เดือน</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">ส่งคำขอผ่อนทอง</button>
            </form>
        </div>

    @else
        <!-- ส่วนนี้แสดงสำหรับผู้ที่ยังไม่ได้ล็อกอิน -->
        <h2 class="text-success">ราคาทองคำล่าสุด</h2>

        @if($goldPrice)
            <p>🔸 <strong>ทองประเภท:</strong> {{ $goldPrice['type'] }}</p>
            <p>💰 <strong>ราคาซื้อ:</strong> {{ number_format($goldPrice['buy'], 2) }} บาท</p>
            <p>💰 <strong>ราคาขาย:</strong> {{ number_format($goldPrice['sell'], 2) }} บาท</p>
        @else
            <p class="text-danger">⚠️ ไม่สามารถโหลดราคาทองคำได้</p>
        @endif

        <hr>
        <p class="mt-4">
            กรุณาเข้าสู่ระบบเพื่อขอผ่อนทองรูปพรรณกับเรา<br>
            หรือติดต่อแอดมินเพื่อทำการสมัครสมาชิกก่อนใช้งานระบบ
        </p>

        <a href="{{ route('login') }}" class="btn btn-primary">🔑 เข้าสู่ระบบ</a>
        <a href="{{ route('contact') }}" class="btn btn-secondary">📞 ติดต่อเรา</a>
    @endif
</div>
@endsection
