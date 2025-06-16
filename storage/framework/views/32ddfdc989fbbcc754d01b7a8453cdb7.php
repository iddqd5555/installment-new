<?php $__env->startSection('content'); ?>

<!-- Banner หลัก -->
<div class="bg-kplus-green text-white py-5">
    <div class="container text-center">
        <h1 class="display-4">ยินดีต้อนรับสู่ WISDOM GOLD</h1>
        <p class="lead">ผ่อนทองง่ายๆ อนุมัติไว ทันใจคุณ</p>
        <a href="<?php echo e(route('gold.index')); ?>" class="btn btn-light btn-rounded">เริ่มต้นผ่อนทองเลย</a>
    </div>
</div>

<!-- ส่วนแนะนำบริการ -->
<div class="container my-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm p-4">
                <h4>✅ อนุมัติง่าย รวดเร็ว</h4>
                <p>ขั้นตอนง่าย อนุมัติรวดเร็วภายใน 24 ชั่วโมง</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm p-4">
                <h4>📈 ผ่อนสบาย</h4>
                <p>เลือกแผนผ่อนชำระที่เหมาะสมกับคุณ 3, 6, 12 เดือน</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm p-4">
                <h4>🔒 ปลอดภัย ไว้ใจได้</h4>
                <p>มั่นใจ ปลอดภัย บริการโดยทีมงานมืออาชีพ</p>
            </div>
        </div>
    </div>
</div>

<!-- ส่วนขั้นตอนการสมัคร -->
<div class="container my-5">
    <h2 class="text-center mb-4">ขั้นตอนการสมัครง่ายๆ</h2>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card p-4 text-center shadow-sm">
                <h5>1. ลงทะเบียนสมาชิก</h5>
                <p>กรอกข้อมูลและยืนยันตัวตนให้ครบถ้วน</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card p-4 text-center shadow-sm">
                <h5>2. เลือกประเภททองและจำนวน</h5>
                <p>เลือกทองคำแท่งหรือทองรูปพรรณ และระบุจำนวนบาทที่ต้องการ</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card p-4 text-center shadow-sm">
                <h5>3. รอการอนุมัติ</h5>
                <p>รอผลอนุมัติภายใน 24 ชั่วโมง จากทีมงานเรา</p>
            </div>
        </div>
    </div>
</div>

<!-- ส่วนคำถามที่พบบ่อย (FAQ) -->
<div class="container my-5">
    <h2 class="text-center mb-4">คำถามที่พบบ่อย (FAQ)</h2>
    <div class="accordion" id="faq">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq1">
                    ต้องใช้เอกสารอะไรบ้างในการสมัคร?
                </button>
            </h2>
            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faq">
                <div class="accordion-body">
                    สำเนาบัตรประชาชน สำเนาทะเบียนบ้าน สำเนาสมุดบัญชีธนาคาร และหลักฐานการเงินอื่นๆ (ถ้ามี)
                </div>
            </div>
        </div>
        <!-- สามารถเพิ่ม FAQ อื่นๆ ได้อีก -->
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\installment-new\resources\views/welcome.blade.php ENDPATH**/ ?>