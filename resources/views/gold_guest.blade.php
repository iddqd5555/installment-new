@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h3 class="mb-4 text-center">💎 ราคาทองคำวันนี้ (อ้างอิงจากสมาคมค้าทองคำ)</h3>

    @if($goldPrices)
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
                    <td>{{ $goldPrices['ornament_sell'] }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <div class="alert alert-danger text-center">
            ⚠️ ไม่สามารถดึงราคาทองล่าสุดได้ กรุณารีเฟรชอีกครั้งค่ะ
        </div>
    @endif
</div>
<form>
    <div class="mb-3">
        <label for="baht_input">จำนวนเงิน (บาท)</label>
        <input type="number" id="baht_input" class="form-control" placeholder="กรอกจำนวนเงิน">
    </div>
    <div class="mb-3">
        <label for="gold_input">น้ำหนักทอง (บาททองคำ)</label>
        <input type="number" id="gold_input" class="form-control" placeholder="กรอกน้ำหนักทองคำ">
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($goldPrices && isset($goldPrices["ornament_sell"]))
        const goldPrice = parseFloat('{{ str_replace(",", "", $goldPrices["ornament_sell"]) }}');
    @else
        const goldPrice = 0;
    @endif

    const bahtInput = document.getElementById('baht_input');
    const goldInput = document.getElementById('gold_input');

    bahtInput.addEventListener('input', function() {
        const baht = parseFloat(this.value) || 0;
        if (goldPrice > 0) {
            goldInput.value = (baht / goldPrice).toFixed(2);
        } else {
            goldInput.value = '0.00';
        }
    });

    goldInput.addEventListener('input', function() {
        const goldWeight = parseFloat(this.value) || 0;
        if (goldPrice > 0) {
            bahtInput.value = (goldWeight * goldPrice).toFixed(2);
        } else {
            bahtInput.value = '0.00';
        }
    });
});
</script>

@endsection
