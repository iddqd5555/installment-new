

<?php $__env->startSection('content'); ?>

<style>
.gold-table {
    border-collapse: collapse;
    width: 100%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 30px;
}
.gold-table th {
    background-color: #f9b115;
    color: #333;
    font-weight: bold;
    text-align: center;
    font-size: 16px;
    padding: 10px;
}
.gold-table td {
    background-color: #fff6d9;
    color: #333;
    text-align: center;
    font-size: 16px;
    padding: 12px;
    border-bottom: 1px solid #f9e4aa;
}
select.form-control {
    appearance: auto !important;
    -webkit-appearance: menulist !important;
    -moz-appearance: menulist !important;
    padding-right: 1rem !important;
    background-image: none !important;
    background-position: right 0.75rem center !important;
    background-repeat: no-repeat !important;
}
</style>

<div class="container py-5">
    <h3 class="mb-4 text-center">💎 ราคาทองรูปพรรณวันนี้ (96.5%)</h3>
        <table class="gold-table">
        <thead>
            <tr>
                <th>ประเภททองคำ</th>
                <th>ราคาขาย (บาท)</th>
                <th>ราคารับซื้อ (บาท)</th>
                <th>ราคารับซื้อ (กรัม)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>ทองรูปพรรณ 96.5%</td>
                <td><?php echo e(($goldPrices && $goldPrices['ornament_sell'] != '0') ? number_format($goldPrices['ornament_sell'], 2) : 'ระบบไม่สามารถดึงราคาทองได้'); ?></td>
                <td><?php echo e(($goldPrices && $goldPrices['ornament_buy'] != '0') ? number_format($goldPrices['ornament_buy'], 2) : '-'); ?></td>
                <td><?php echo e(($goldPrices && $goldPrices['ornament_buy_gram'] != '0') ? number_format($goldPrices['ornament_buy_gram'], 2) : '-'); ?></td>
            </tr>
        </tbody>
    </table>
    <form method="POST" action="<?php echo e(route('gold.request.store')); ?>" id="goldForm">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="fullname" value="<?php echo e(auth()->user()->name); ?>">
        <input type="hidden" name="phone" value="<?php echo e(auth()->user()->phone); ?>">
        <input type="hidden" name="id_card" value="<?php echo e(auth()->user()->id_card_number); ?>">

        <div class="mb-3">
            <label>ราคาทองต่อบาท (กรณีไม่มีราคาจากระบบ)</label>
            <input type="number" step="0.01" class="form-control" id="manual_gold_price" 
                value="<?php echo e(($goldPrices && isset($goldPrices['ornament_sell']) && $goldPrices['ornament_sell'] != '0') ? $goldPrices['ornament_sell'] : 50000); ?>" required>
        </div>

        <div class="mb-3">
            <label>ระยะเวลาผ่อน</label>
            <select class="form-control" name="installment_period" id="installment_period">
                <option value="30">30 วัน</option>
                <option value="45">45 วัน</option>
                <option value="60">60 วัน</option>
            </select>
        </div>

        <div class="mb-3">
            <label>จำนวนเงินที่ต้องการผ่อน (บาท)</label>
            <input type="number" step="0.01" class="form-control" id="baht_input" name="gold_price" required>
        </div>

        <div class="mb-3">
            <label>น้ำหนักทอง (บาททองคำ)</label>
            <input type="number" step="0.01" class="form-control" id="gold_weight_input" name="gold_amount" required>
        </div>

        <button type="submit" class="btn btn-primary">ส่งคำขอผ่อนทอง</button>
    </form>
</div>
<!-- อัปเดต Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ยืนยันการส่งข้อมูลผ่อนทอง</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>ชื่อ-นามสกุล:</strong> <span id="modalFullName"></span></p>
                <p><strong>เลขบัตรประชาชน:</strong> <span id="modalIDCard"></span></p>
                <p><strong>เบอร์โทรศัพท์:</strong> <span id="modalPhone"></span></p>
                <p><strong>จำนวนเงินที่ผ่อน:</strong> <span id="modalGoldPrice"></span> บาท</p>
                <p><strong>ระยะเวลาผ่อน:</strong> <span id="modalPeriod"></span> วัน</p>
                <p><strong>ยอดรวมที่ต้องผ่อน:</strong> <span id="modalTotalPayment"></span> บาท</p>
                <p><strong>ยอดที่ต้องชำระต่อวัน:</strong> <span id="modalDailyPayment"></span> บาท</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="submitForm()">ยืนยันส่งข้อมูล</button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bahtInput = document.getElementById('baht_input');
    const goldWeightInput = document.getElementById('gold_weight_input');
    const installmentPeriod = document.getElementById('installment_period');
    const idCard = document.querySelector('input[name="id_card"]').value;
    const maxLimit = 10000;
    const defaultGoldPrice = 50000;

    function getGoldPrice() {
        return parseFloat(document.getElementById('manual_gold_price').value) || defaultGoldPrice;
    }

    function calculateInstallment() {
        const goldPrice = getGoldPrice();
        const multipliers = {30: 1.27, 45: 1.45, 60: 1.66};
        const days = parseInt(installmentPeriod.value);
        const amount = parseFloat(bahtInput.value) || 0;
        const totalAmount = amount * multipliers[days];
        const dailyPayment = totalAmount / days;

        document.getElementById('modalFullName').innerText = '<?php echo e(auth()->user()->name); ?>';
        document.getElementById('modalIDCard').innerText = '<?php echo e(auth()->user()->id_card_number); ?>';
        document.getElementById('modalPhone').innerText = '<?php echo e(auth()->user()->phone); ?>';
        document.getElementById('modalGoldPrice').innerText = amount.toFixed(2);
        document.getElementById('modalPeriod').innerText = days;
        document.getElementById('modalTotalPayment').innerText = totalAmount.toFixed(2);
        document.getElementById('modalDailyPayment').innerText = dailyPayment.toFixed(2);
    }

    bahtInput.addEventListener('input', function() {
        let baht = parseFloat(this.value) || 0;
        let goldWeight = baht / getGoldPrice();
        goldWeightInput.value = goldWeight.toFixed(2);
        calculateInstallment();
    });

    goldWeightInput.addEventListener('input', function() {
        let goldWeight = parseFloat(this.value) || 0;
        let baht = goldWeight * getGoldPrice();
        bahtInput.value = baht.toFixed(2);
        calculateInstallment();
    });

    installmentPeriod.addEventListener('change', calculateInstallment);

    // Modal Confirm
    document.getElementById('goldForm').addEventListener('submit', function(e){
        e.preventDefault();
        calculateInstallment();
        let modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        modal.show();
    });

    // ส่งฟอร์มจริง
    window.submitForm = function(){
        document.getElementById('goldForm').submit();
    };
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\installment-new\resources\views/gold_member.blade.php ENDPATH**/ ?>