@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h3 class="text-center">🔒 ระบบสมาชิก (ผ่อนทอง)</h3>

    <!-- ตารางราคาทองคำจาก API -->
    @if($goldPrices)
        <h4 class="mb-4 text-center">💎 ราคาทองคำวันนี้ (อ้างอิงจากสมาคมค้าทองคำ)</h4>
        <table class="table table-bordered text-center">
            <thead class="table-success">
                <tr>
                    <th>ประเภททอง</th>
                    <th>รับซื้อ (บาท)</th>
                    <th>ขายออก (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>ทองคำแท่ง</strong></td>
                    <td>{{ $goldPrices['bar_buy'] }}</td>
                    <td>{{ $goldPrices['bar_sell'] }}</td>
                </tr>
                <tr>
                    <td><strong>ทองรูปพรรณ</strong></td>
                    <td>{{ $goldPrices['ornament_buy'] }}</td>
                    <td id="current_gold_price">{{ $goldPrices['ornament_sell'] }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <div class="alert alert-danger text-center">
            ⚠️ ไม่สามารถดึงราคาทองล่าสุดได้ กรุณารีเฟรชอีกครั้งค่ะ
        </div>
    @endif

    <!-- ฟอร์มสำหรับการผ่อนทอง -->
    <form method="POST" action="{{ route('gold.request.store') }}">
        @csrf

        <div class="mb-3">
            <label for="gold_amount">น้ำหนักทองที่ต้องการผ่อน (บาท)</label>
            <input type="number" step="0.01" name="gold_amount" id="gold_amount" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="total_price">ราคารวมทองคำ (บาท)</label>
            <input type="text" id="total_price" class="form-control" readonly>
        </div>

        <div class="mb-3">
            <label for="installment_period">ระยะเวลาผ่อน (เดือน)</label>
            <select name="installment_period" id="installment_period" class="form-select">
                <option value="6">6 เดือน</option>
                <option value="12">12 เดือน</option>
                <option value="18">18 เดือน</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">ส่งคำขอผ่อนทอง</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($goldPrices && isset($goldPrices["ornament_sell"]))
        const goldPrice = parseFloat('{{ str_replace(",", "", $goldPrices["ornament_sell"]) }}');
    @else
        const goldPrice = 0;
    @endif
    
    const goldAmountInput = document.getElementById('gold_amount');
    const totalPriceInput = document.getElementById('total_price');

    goldAmountInput.addEventListener('input', function () {
        const goldAmount = parseFloat(this.value) || 0;
        if (goldPrice > 0) {
            totalPriceInput.value = (goldAmount * goldPrice).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        } else {
            totalPriceInput.value = 'ยังไม่มีข้อมูลราคาทอง';
        }
    });
});
</script>


@endsection
