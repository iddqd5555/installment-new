@extends('layouts.app')

@section('content')
<div class="container py-5">
    @if(Auth::check())
        <h2 class="mb-4 text-success">📱 ฟอร์มคำขอผ่อนทอง</h2>

        <div class="card shadow-sm p-4">
            <form method="POST" action="{{ route('gold.request.store') }}">
                @csrf

                <div class="form-group">
                    <label>ชื่อ-นามสกุล</label>
                    <input type="text" name="fullname" class="form-control" value="{{ auth()->user()->name }}" readonly>
                </div>

                <div class="form-group">
                    <label>เบอร์โทรศัพท์</label>
                    <input type="text" name="phone" class="form-control" value="{{ auth()->user()->phone }}" readonly>
                </div>

                <div class="form-group">
                    <label>น้ำหนักทอง (บาท)</label>
                    <input type="number" step="0.01" class="form-control" name="gold_amount" required>
                </div>

                <div class="form-group">
                    <label>ราคาทองวันนี้ (บาท)</label>
                    <input type="number" step="0.01" class="form-control" name="gold_price" value="{{ $goldPrice->sell }}" readonly>
                </div>

                <div class="form-group">
                    <label>ระยะเวลาผ่อน</label>
                    <select name="installment_period" class="form-control" required>
                        <option value="30">30 วัน</option>
                        <option value="45">45 วัน</option>
                        <option value="60">60 วัน</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">ส่งคำขอผ่อนทอง</button>
            </form>
        </div>

    @else
        <h4 class="text-center bg-warning py-2 rounded">ราคาทองรูปพรรณวันนี้ (96.5%)</h4>
        <table class="table table-bordered text-center">
            <thead class="bg-warning">
                <tr>
                    <th>รับซื้อ</th>
                    <th>ขายออก</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format($goldPrice->buy, 2) }}</td>
                    <td>{{ number_format($goldPrice->sell, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <hr>
        <p class="mt-4">
            กรุณาเข้าสู่ระบบเพื่อขอผ่อนทองรูปพรรณกับเรา<br>
            หรือติดต่อแอดมินเพื่อสมัครสมาชิกก่อนใช้งานระบบ
        </p>

        <a href="{{ route('login') }}" class="btn btn-primary">🔑 เข้าสู่ระบบ</a>
        <a href="{{ route('contact') }}" class="btn btn-secondary">📞 ติดต่อเรา</a>
    @endif
</div>
@endsection
