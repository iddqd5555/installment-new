<?php $__env->startSection('content'); ?>


<div class="theme-bg text-white py-5">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">บัตรประชาชนใบเดียว ก็ผ่อนได้</h1>
        <div class="lead mb-2" style="font-size:1.2em;">ไม่เช็คแบล็คลิสต์ ไม่เช็คบูโร ไม่ใช้คนค้ำ</div>
        <a href="<?php echo e(route('gold.index')); ?>" class="btn btn-light btn-rounded mt-2">เริ่มต้นผ่อนทองเลย</a>
    </div>
</div>


<div class="container py-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-table theme-color fs-4"></i>
                <span class="fs-5 fw-bold ms-2">ราคารับจำนำประจำสัปดาห์สำหรับลูกค้า</span>
            </div>
            <table class="table table-bordered text-center">
                <thead class="theme-bg text-white">
                    <tr>
                        <th>ชนิดทอง</th>
                        <th>รับจำนำ</th>
                        <th>ขายคืน</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>ทองคำแท่ง 96.5%</td>
                        <td>35,000</td>
                        <td>36,000</td>
                    </tr>
                    <tr>
                        <td>ทองรูปพรรณ 96.5%</td>
                        <td>34,400</td>
                        <td>35,500</td>
                    </tr>
                </tbody>
            </table>
            <div class="text-muted" style="font-size: 13px;">ณ วันที่ 7 กรกฎาคม 2566</div>
        </div>
    </div>
</div>


<div class="container mb-3">
    <div class="row text-center g-3">
        <div class="col-md-3 col-6">
            <div class="feature-card"><i class="bi bi-person-badge"></i> บัตรประชาชนใบเดียว</div>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-card"><i class="bi bi-lightning"></i> สะดวก รวดเร็ว</div>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-card"><i class="bi bi-shield-check"></i> ไม่เช็คเครดิต ไม่เช็คบูโร</div>
        </div>
        <div class="col-md-3 col-6">
            <div class="feature-card"><i class="bi bi-arrow-repeat"></i> การผ่อนคืนสบาย ผ่อนได้จริง</div>
        </div>
    </div>
</div>


<div class="container">
    <div class="section-title">รีวิวบ้างส่วนจากลูกค้าจริง</div>
    <div class="row g-3">
        <?php for($i=1;$i<=4;$i++): ?>
        <div class="col-md-3 col-6">
            <div class="section-card text-center">
                <div class="mb-2"><img src="https://placehold.co/80x80/730A22/fff?text=IMG" class="rounded-circle" /></div>
                <div class="fw-bold mb-1">ลูกค้ารีวิว <?php echo e($i); ?></div>
                <div style="font-size:14px;">ได้รับทองจริง ผ่อนง่าย</div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
</div>


<div class="container my-5">
    <div class="section-title">ขั้นตอนการสมัครผ่อนทอง</div>
    <div class="row text-center mb-4">
        <div class="col-md-4 mb-3">
            <div class="step-circle">1</div>
            <div class="fw-bold">กดยื่นออนไลน์</div>
            <div style="font-size:14px;">แอดไลน์ หรือลงทะเบียนผ่านฟอร์ม</div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="step-circle">2</div>
            <div class="fw-bold">กรอกแบบฟอร์ม</div>
            <div style="font-size:14px;">กรอกข้อมูลพร้อมแนบบัตรประชาชน</div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="step-circle">3</div>
            <div class="fw-bold">รับทอง</div>
            <div style="font-size:14px;">อนุมัติไว รับทองหน้าร้าน หรือจัดส่งถึงบ้าน</div>
        </div>
    </div>
</div>


<div class="container">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="section-title">คุณสมบัติผู้สมัคร</div>
            <div class="section-card">
                <div class="fw-bold">คุณสมบัติ</div>
                <ul>
                    <li>อายุตั้งแต่ 20 ปีบริบูรณ์ขึ้นไป</li>
                    <li>มีบัตรประชาชน</li>
                    <li>มีรายได้ประจำ/อาชีพอิสระ</li>
                    <li>ไม่ต้องมีคนค้ำประกัน</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="section-title">เอกสารที่ใช้</div>
            <div class="section-card">
                <div class="fw-bold">เอกสารที่ใช้</div>
                <ul>
                    <li>บัตรประชาชน</li>
                    <li>สลิปเงินเดือน/รายการเดินบัญชี</li>
                    <li>ทะเบียนบ้าน (ถ้ามี)</li>
                </ul>
            </div>
        </div>
    </div>
</div>


<div class="container py-3">
    <div class="section-title">พื้นที่ให้บริการ</div>
    <div class="section-card">
        <ul class="mb-0">
            <li>กรุงเทพฯ</li>
            <li>ปริมณฑล</li>
            <li>ภาคกลาง</li>
            <li>ภาคตะวันออก</li>
            <li>ภาคอีสาน</li>
        </ul>
    </div>
</div>


<div class="container pb-3">
    <div class="section-title">คำถามที่พบบ่อย (FAQ)</div>
    <div class="accordion" id="faqAccordion">
        <?php
        $faqs = [
            ["q" => "คำถามเกี่ยวกับผ่อนทองทั่วไป", "a" => "สามารถผ่อนทองได้โดยใช้บัตรประชาชนใบเดียว"],
            ["q" => "ผ่อนเครื่องใช้ไฟฟ้าได้ไหม", "a" => "ได้ มีบริการผ่อนเครื่องใช้ไฟฟ้า"],
            ["q" => "เงื่อนไขในการผ่อน", "a" => "ไม่ต้องมีคนค้ำ ไม่เช็คเครดิตบูโร"],
            ["q" => "การรับทองที่ไหน", "a" => "รับทองหน้าร้านหรือจัดส่งถึงบ้าน"],
            ["q" => "ทองผ่อนได้ไหม", "a" => "ผ่อนได้โดยใช้บัตรประชาชนใบเดียว"],
        ];
        ?>
        <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="faq<?php echo e($i); ?>">
                <button class="accordion-button collapsed theme-color" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo e($i); ?>">
                    <?php echo e($faq['q']); ?>

                </button>
            </h2>
            <div id="collapse<?php echo e($i); ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                <div class="accordion-body"><?php echo e($faq['a']); ?></div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>


<div class="container pb-3">
    <div class="section-title">แบบฟอร์มขอผ่อนทอง</div>
    <div class="section-card">
        <form>
            <div class="mb-3">
                <label for="goldType" class="form-label">ประเภททองคำ</label>
                <input type="text" class="form-control" id="goldType" placeholder="เช่น ทองแท่ง">
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">จำนวน (บาท)</label>
                <input type="number" class="form-control" id="amount" placeholder="0">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="accept" checked>
                <label class="form-check-label" for="accept">ข้าพเจ้ายอมรับข้อกำหนดและเงื่อนไข</label>
            </div>
            <button type="submit" class="btn btn-theme">ถัดไป</button>
        </form>
    </div>
</div>


<footer class="footer mt-5">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-4 mb-3">
                <div class="footer-logo mb-2">WISDOM GOLD</div>
                <div>โทร: 081-816-8661</div>
                <div>LINE: @wisdom.gg</div>
                <div>FACEBOOK : Wisdom Gold Group</div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="fw-bold mb-2">บริการ</div>
                <div>ผ่อนทอง</div>
                <div>ผ่อนเครื่องใช้ไฟฟ้า</div>
                <div>ผ่อนไอโฟน</div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="fw-bold mb-2">ติดต่อเรา</div>
                <div>โทร: 081-816-8661</div>
                <div>เปิดบริการทุกวัน 10:00-18:00</div>
            </div>
        </div>
        <div class="text-center mt-4" style="font-size:12px;">© สงวนลิขสิทธิ์ บริษัท วิสดอม โกลด์ กรุ๊ป</div>
    </div>
</footer>

<?php $__env->startPush('styles'); ?>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { font-family: 'Sarabun', sans-serif; background: #f7f7f7; }
    .theme-bg { background: #730A22 !important; }
    .theme-color { color: #730A22 !important; }
    .btn-theme { background: #730A22; color: #fff; border-radius: 8px; padding: 8px 24px; font-weight: bold; }
    .feature-card, .section-card { background: #fff; border-radius: 18px; box-shadow: 0 2px 16px #730A221a; margin-bottom: 18px; padding: 22px 26px; font-weight: bold; color: #730A22; }
    .feature-card i { font-size: 1.7em; margin-right: 14px; }
    .section-title { font-size: 1.4em; font-weight: bold; color: #730A22; margin: 32px 0 16px 0; }
    .step-circle { width: 52px; height: 52px; border-radius: 50%; background: #730A22; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.5em; margin: 0 auto 10px auto; }
    .footer { background: #730A22; color: #fff; padding: 32px 0 18px 0; }
    .footer-logo { font-size: 1.2em; font-weight: bold; letter-spacing: 1px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\installment-new\resources\views/welcome.blade.php ENDPATH**/ ?>