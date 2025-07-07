<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <?php if(session('error')): ?>
        <div class="alert alert-danger">
            <strong>❌ เกิดข้อผิดพลาด!</strong><br><?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <strong>✅ สำเร็จ!</strong><br><?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <h2 class="text-success mb-4">📊 Dashboard การผ่อนของคุณ</h2>

    <?php if(auth()->user()->unreadNotifications->count()): ?>
        <div class="alert alert-info">
            <strong>🔔 แจ้งเตือนล่าสุด:</strong>
            <ul class="mt-2">
                <?php $__currentLoopData = auth()->user()->unreadNotifications->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <?php echo e($notification->data['message'] ?? 'ไม่มีข้อความ'); ?>

                        <?php if(isset($notification->data['date'])): ?>
                            (<?php echo e(\Carbon\Carbon::parse($notification->data['date'])->format('d/m/Y')); ?>)
                        <?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php
        $installment = $installmentRequests->first();
        $today = \Carbon\Carbon::today();

        $dueToday = $installment->installmentPayments
            ->filter(fn($p) => \Carbon\Carbon::parse($p->payment_due_date)->isSameDay($today))
            ->sum('amount') ?: 0;
    ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            📌 ผ่อนทองจำนวน: <strong><?php echo e(number_format($installment->gold_amount ?? 0, 2)); ?> บาท</strong>
        </div>
        <div class="card-body">
            <div class="row text-center mb-4">
                <div class="col-md-3">
                    <div class="alert alert-primary">
                        💳 <strong>ยอดที่ต้องชำระวันนี้</strong><br><?php echo e(number_format($dueToday, 2)); ?> บาท
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success">
                        💰 <strong>ยอดชำระล่วงหน้า</strong><br><?php echo e(number_format($installment->advance_payment, 2)); ?> บาท
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        📅 <strong>วันชำระครั้งถัดไป</strong><br>
                        <?php echo e($installment->next_payment_date ? \Carbon\Carbon::parse($installment->next_payment_date)->format('d/m/Y') : 'ยังไม่กำหนด'); ?>

                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-danger">
                        ⚠️ <strong>ค่าปรับสะสม</strong><br><?php echo e(number_format($installment->total_penalty, 2)); ?> บาท
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <strong>ชำระแล้วทั้งหมด:</strong> <?php echo e(number_format($installment->total_paid, 2)); ?> / <?php echo e(number_format($installment->total_with_interest, 2)); ?> บาท
                <div class="progress mt-2">
                    <?php
                        $paymentProgress = ($installment->total_with_interest > 0)
                            ? ($installment->total_paid / $installment->total_with_interest) * 100 : 0;
                    ?>
                    <div class="progress-bar bg-success" style="width: <?php echo e($paymentProgress); ?>%;">
                        <?php echo e(number_format($paymentProgress, 2)); ?>%
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <strong>ระยะเวลาการผ่อน:</strong>
                <?php
                    $firstApprovedDate = $installment->first_approved_date ?? $installment->start_date;
                    $daysPassed = $firstApprovedDate ? \Carbon\Carbon::parse($firstApprovedDate)->diffInDays(\Carbon\Carbon::today()) : 0;
                    $installmentPeriod = $installment->installment_period ?? 0;
                    $timeProgress = ($installmentPeriod > 0) ? min(100, ($daysPassed / $installmentPeriod) * 100) : 0;
                ?>
                <?php echo e($daysPassed); ?> / <?php echo e($installmentPeriod); ?> วัน
                <div class="progress mt-2">
                    <div class="progress-bar bg-info" style="width: <?php echo e($timeProgress); ?>%;">
                        <?php echo e(number_format($timeProgress, 2)); ?>%
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-secondary text-white">
            📋 ประวัติการชำระเงินล่าสุด
        </div>
        <div class="card-body">
            <?php if($installment->payment_history->count()): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>วัน/เวลา</th>
                        <th>จำนวนเงิน</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $installment->payment_history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e(optional($payment->payment_due_date) ? \Carbon\Carbon::parse($payment->payment_due_date)->format('d/m/Y H:i') : '-'); ?></td>
                        <td><?php echo e(number_format($payment->amount_paid, 2)); ?> บาท</td>
                        <td>
                            <?php if($payment->status == 'approved'): ?>
                                <span class="badge bg-success">อนุมัติแล้ว</span>
                            <?php elseif($payment->status == 'pending'): ?>
                                <span class="badge bg-warning">รอตรวจสอบ</span>
                            <?php else: ?>
                                <span class="badge bg-danger">ถูกปฏิเสธ</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="alert alert-secondary">ยังไม่มีประวัติการชำระเงินค่ะ</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php echo $__env->make('partials.bottom-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\installment-new\resources\views/dashboard.blade.php ENDPATH**/ ?>