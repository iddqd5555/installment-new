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
.gold-table tbody tr:last-child td {
    border-bottom: none;
}
@media(max-width:768px){
    .highlight-note {
        display: block;
        margin-top: 4px;
    }
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

    <?php if($goldPrices && $goldPrices['ornament_sell'] != 0 && $goldPrices['ornament_buy'] != 0): ?>
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
                    <td><?php echo e($goldPrices['ornament_sell']); ?></td>
                    <td><?php echo e($goldPrices['ornament_buy']); ?></td>
                    <td><?php echo e($goldPrices['ornament_buy_gram'] ?? 'n/a'); ?></td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-danger text-center">
            ⚠️ ไม่สามารถดึงราคาทองล่าสุดได้ กรุณารีเฟรชอีกครั้งค่ะ หรือรอแอดมินอัปเดตราคาทองตอน 9 โมงเช้า
        </div>
    <?php endif; ?>

    <form id="goldForm"
        method="POST"
        action="<?php echo e(auth()->check() ? route('gold.request.store') : route('gold.submit_guest')); ?>"
        <?php if(!$goldPrices || $goldPrices['ornament_sell'] == 0 || $goldPrices['ornament_buy'] == 0): ?>
            style="opacity:0.6;pointer-events:none"
        <?php endif; ?>>
        <?php echo csrf_field(); ?>

        <?php if(auth()->check()): ?>
            <input type="hidden" name="fullname" value="<?php echo e(auth()->user()->name); ?>">
            <input type="hidden" name="phone" value="<?php echo e(auth()->user()->phone); ?>">
        <?php else: ?>
            <div class="mb-3">
                <label>ชื่อ-นามสกุลจริง <span class="text-danger highlight-note">(กรุณากรอกข้อมูลจริง)</span></label>
                <input type="text" class="form-control" name="fullname" required>
            </div>
            <div class="mb-3">
                <label>เบอร์โทรศัพท์ <span class="text-danger highlight-note">(กรุณากรอกข้อมูลจริง)</span></label>
                <input type="text" class="form-control" name="phone" required>
            </div>
            <div class="mb-3">
                <label>เลขบัตรประชาชน (กรุณากรอกข้อมูลจริง)</label>
                <input type="text" class="form-control" id="id_card" name="id_card" required>
            </div>
            <div class="mb-3">
                <label>
                    รหัสผู้แนะนำ (ถ้ามี)
                    <span class="text-muted highlight-note">
                        (เช่น <b>00123</b>, <b>00124</b>, <b>00125</b> หรือ <b>00126</b>)
                    </span>
                </label>
                <input type="text" class="form-control" name="referrer_code" maxlength="32" autocomplete="off" placeholder="00123, 00124, 00125 ...">
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label>ระยะเวลาผ่อน</label>
            <select class="form-control" name="installment_period" id="installment_period">
                <option value="30">30 วัน</option>
                <option value="45">45 วัน</option>
                <option value="60">60 วัน</option>
            </select>
        </div>

        <div class="mb-3">
            <label>จำนวนเงินที่ต้องการผ่อน (บาท)
                <span class="text-danger highlight-note">(กรุณาใส่จำนวนเงินจริงที่ต้องการผ่อน)</span>
            </label>
            <input type="number" step="0.01" class="form-control" id="baht_input" name="gold_price" required>
        </div>

        <div class="mb-3">
            <label>น้ำหนักทอง (บาททองคำ)
                <span class="text-danger highlight-note">(ไม่เกิน 10,000 บาททอง)</span>
            </label>
            <input type="number" step="0.01" class="form-control" id="gold_weight_input" name="gold_amount" required>
        </div>

        <button type="button" class="btn btn-primary" id="showModalBtn"
            <?php if(!$goldPrices || $goldPrices['ornament_sell'] == 0 || $goldPrices['ornament_buy'] == 0): ?> disabled <?php endif; ?>>
            ส่งคำขอผ่อนทอง
        </button>
    </form>
    <div class="mt-4 p-3 border rounded">
        <h5>รายละเอียดการผ่อนทองของคุณ</h5>
        <p><strong>ยอดรวมที่ต้องผ่อน:</strong> <span id="total_payment"></span> บาท</p>
        <p><strong>ยอดที่ต้องชำระต่อวัน:</strong> <span id="daily_payment"></span> บาท</p>
    </div>
</div>

<!-- Modal -->
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
                <p><strong>รหัสผู้แนะนำ:</strong> <span id="modalReferrer"></span></p>
                <p><strong>จำนวนเงินที่ผ่อน:</strong> <span id="modalGoldPrice"></span> บาท</p>
                <p><strong>ระยะเวลาผ่อน:</strong> <span id="modalPeriod"></span> วัน</p>
                <p><strong>ยอดรวมที่ต้องผ่อน:</strong> <span id="modalTotalPayment"></span> บาท</p>
                <p><strong>ยอดที่ต้องชำระต่อวัน:</strong> <span id="modalDailyPayment"></span> บาท</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" id="realSubmitBtn" class="btn btn-primary">ยืนยันส่งข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<?php if(session('success')): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({icon:'success',title:'สำเร็จ',text:'<?php echo e(session('success')); ?>'});
});
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const maxLimit = 10000;
    const goldPrice = parseFloat('<?php echo e(isset($goldPrices["ornament_sell"]) ? str_replace(",", "", $goldPrices["ornament_sell"]) : 0); ?>');
    const bahtInput = document.getElementById('baht_input');
    const goldWeightInput = document.getElementById('gold_weight_input');
    const periodInput = document.querySelector('[name="installment_period"]');
    const totalPaymentOutput = document.getElementById('total_payment');
    const dailyPaymentOutput = document.getElementById('daily_payment');
    const form = document.getElementById('goldForm');
    const showModalBtn = document.getElementById('showModalBtn');
    const realSubmitBtn = document.getElementById('realSubmitBtn');
    let isSubmitting = false;

    function setFormDisabled(disabled = true) {
        // กัน spam ด้วยการ disable แค่ปุ่ม
        showModalBtn.disabled = disabled;
        if(realSubmitBtn) realSubmitBtn.disabled = disabled;
    }

    bahtInput.addEventListener('input', function() {
        let baht = parseFloat(this.value) || 0;
        if(baht > maxLimit * goldPrice){
            alert('จำนวนเงินเกิน 10,000 บาททองคำแล้วค่ะ');
            baht = maxLimit * goldPrice;
            this.value = baht.toFixed(2);
        }
        goldWeightInput.value = goldPrice > 0 ? (baht / goldPrice).toFixed(2) : '0.00';
        calculateInstallment();
    });

    goldWeightInput.addEventListener('input', function() {
        let goldWeight = parseFloat(this.value) || 0;
        if(goldWeight > maxLimit){
            alert('น้ำหนักทองเกิน 10,000 บาททองคำแล้วค่ะ');
            goldWeight = maxLimit;
            this.value = goldWeight.toFixed(2);
        }
        bahtInput.value = goldPrice > 0 ? (goldWeight * goldPrice).toFixed(2) : '0.00';
        calculateInstallment();
    });

    periodInput.addEventListener('change', calculateInstallment);

    function calculateInstallment() {
        const multipliers = {30: 1.27, 45: 1.45, 60: 1.66};
        const days = parseInt(periodInput.value);
        const amount = parseFloat(bahtInput.value) || 0;
        const totalAmount = amount * multipliers[days];
        const dailyPayment = totalAmount / days;
        totalPaymentOutput.innerText = totalAmount.toFixed(2);
        dailyPaymentOutput.innerText = dailyPayment.toFixed(2);

        document.getElementById('modalTotalPayment').innerText = totalAmount.toFixed(2);
        document.getElementById('modalDailyPayment').innerText = dailyPayment.toFixed(2);
        document.getElementById('modalGoldPrice').innerText = amount.toFixed(2);
        document.getElementById('modalPeriod').innerText = days;
    }

    showModalBtn.addEventListener('click', function() {
        const fullName = document.querySelector('[name="fullname"]')?.value || '-';
        const idCard = document.getElementById('id_card') ? document.getElementById('id_card').value : '-';
        const phone = document.querySelector('[name="phone"]')?.value || '-';
        const referrer = document.querySelector('[name="referrer_code"]') ? document.querySelector('[name="referrer_code"]').value : '-';

        document.getElementById('modalFullName').innerText = fullName;
        document.getElementById('modalIDCard').innerText = idCard;
        document.getElementById('modalPhone').innerText = phone;
        document.getElementById('modalReferrer').innerText = referrer;
        calculateInstallment();

        setFormDisabled(false);
        isSubmitting = false;

        const myModalEl = document.getElementById('confirmModal');
        let myModal = bootstrap.Modal.getInstance(myModalEl);
        if (!myModal) {
            myModal = new bootstrap.Modal(myModalEl, { keyboard: false });
        }
        myModal.show();

        myModalEl.addEventListener('hidden.bs.modal', function () {
            document.querySelectorAll('.modal-backdrop').forEach(e => e.remove());
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '0';
            setFormDisabled(false);
            isSubmitting = false;
        }, { once: true });
    });

    realSubmitBtn.addEventListener('click', function() {
        if (isSubmitting) return;
        isSubmitting = true;
        setFormDisabled(true);

        const formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            setFormDisabled(false);
            isSubmitting = false;

            if (response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
                form.reset();
                calculateInstallment();
                Swal.fire({icon: 'success', title: 'ส่งคำขอสำเร็จ', text: 'ระบบได้รับข้อมูลของคุณแล้วค่ะ'});
            } else {
                let errMsg = 'เกิดข้อผิดพลาด';
                try {
                    const json = await response.json();
                    if(json && json.errors) {
                        let msg = '';
                        Object.values(json.errors).forEach(val => {
                            msg += (typeof val === 'string' ? val : val[0]) + '<br>';
                        });
                        errMsg = msg;
                    } else if(json && json.message) {
                        errMsg = json.message;
                    }
                } catch(e){}
                Swal.fire({icon: 'error', title: 'ผิดพลาด', html: errMsg});
            }
        })
        .catch(() => {
            setFormDisabled(false);
            isSubmitting = false;
            Swal.fire({icon: 'error', title: 'ผิดพลาด', text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์'});
        });
    });

    calculateInstallment();
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\installment-new\resources\views/gold_guest.blade.php ENDPATH**/ ?>