<?php $__env->startSection('content'); ?>


<div class="container-section">
    <div class="theme-bg text-white py-5 banner-curve-all">
        <div class="text-center">
            <h1 class="display-4 fw-bold">บัตรประชาชนใบเดียว ก็ผ่อนได้</h1>
            <div class="lead mb-2 fs-5">ไม่เช็คแบล็คลิสต์ ไม่เช็คบูโร ไม่ใช้คนค้ำ</div>
            <a href="<?php echo e(route('gold.index')); ?>" class="btn btn-theme btn-rounded mt-2">เริ่มต้นผ่อนทองเลย</a>
        </div>
    </div>
</div>


<div class="container-section gold-main-section">
    <div class="gold-box">
        <div class="gold-header">
            <img src="https://www.goldtraders.or.th/images/logoGT.png" style="height:40px;margin-right:8px;vertical-align:middle;">
            ราคาทองตามประกาศสมาคมค้าทองคำ
        </div>
        <table class="gold-table" id="goldPriceTable">
            <thead>
                <tr>
                    <th class="type">96.5%</th>
                    <th class="buy">รับซื้อ</th>
                    <th class="sell">ขายออก</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="type text-start">ทองคำแท่ง</td>
                    <td id="gold-bar-buy" class="price"></td>
                    <td id="gold-bar-sell" class="price"></td>
                </tr>
                <tr>
                    <td class="type text-start">ทองรูปพรรณ</td>
                    <td id="gold-jewelry-buy" class="price"></td>
                    <td id="gold-jewelry-sell" class="price"></td>
                </tr>
                <tr class="change-row">
                    <td class="type text-danger text-center" style="font-weight:bold;">
                        วันนี้ <span id="gold-today-arrow">▼</span>
                        <span id="gold-today-buy-change" class="text-danger"></span>
                    </td>
                    <td class="buy text-danger" id="gold-today-buy" style="font-weight:bold;"></td>
                    <td class="sell text-danger" id="gold-today-sell" style="font-weight:bold;"></td>
                </tr>
            </tbody>
        </table>
        <div class="gold-footer">
            <span id="goldPriceDate">-</span>
        </div>
    </div>
</div>


<div class="container-section">
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


<div class="container-section">
    <div class="section-title">รีวิวบางส่วนจากลูกค้าจริง</div>
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


<div class="container-section">
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


<div class="container-section">
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


<div class="container-section">
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


<div class="container-section">
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


<div class="container-section">
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

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('/api/gold-latest')
        .then(res => res.json())
        .then(data => {
            console.log("gold-latest data:", data); // DEBUG
            document.getElementById('gold-bar-buy').innerText = Number(data.gold_bar_buy).toLocaleString();
            document.getElementById('gold-bar-sell').innerText = Number(data.gold_bar_sell).toLocaleString();
            document.getElementById('gold-jewelry-buy').innerText = Number(data.gold_jewelry_buy).toLocaleString();
            document.getElementById('gold-jewelry-sell').innerText = Number(data.gold_jewelry_sell).toLocaleString();

            let arrow = parseFloat(data.change_buy) < 0 ? '▼' : '▲';
            let color = parseFloat(data.change_buy) < 0 ? 'red' : 'green';
            document.getElementById('gold-today-arrow').innerText = arrow;
            document.getElementById('gold-today-arrow').style.color = color;
            document.getElementById('gold-today-buy-change').innerText = data.change_buy ?? '';
            document.getElementById('gold-today-buy').innerHTML = arrow + " " + (data.change_buy ?? '');
            document.getElementById('gold-today-buy').style.color = color;
            document.getElementById('gold-today-sell').innerHTML = arrow + " " + (data.change_sell ?? '');
            document.getElementById('gold-today-sell').style.color = color;

            document.getElementById('goldPriceDate').innerText = data.last_update ?? '';
        })
        .catch(e => {
            document.getElementById('goldPriceDate').innerText = 'ไม่สามารถโหลดราคาทองได้';
            console.error("Fetch gold error:", e);
        });
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\installment-new\resources\views/welcome.blade.php ENDPATH**/ ?>