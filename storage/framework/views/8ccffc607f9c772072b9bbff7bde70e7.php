

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <h3>📌 รายการสลิปที่รออนุมัติ</h3>
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <table class="table table-bordered">
        <thead class="table-success">
            <tr>
                <th>ชื่อผู้ใช้</th>
                <th>จำนวนเงินที่โอน</th>
                <th>วันที่อัปโหลด</th>
                <th>ดูสลิป</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $pendingPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($payment->installmentRequest->user->name); ?></td>
                <td><?php echo e(number_format($payment->amount_paid, 2)); ?> บาท</td>
                <td><?php echo e($payment->created_at->format('d/m/Y H:i')); ?></td>
                <td>
                    <a href="<?php echo e(asset('storage/'.$payment->payment_proof)); ?>" target="_blank">ดูสลิป</a>
                </td>
                <td>
                    <form action="<?php echo e(route('admin.payments.approve', $payment->id)); ?>" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าจะอนุมัติสลิปจำนวน <?php echo e(number_format($payment->amount_paid, 2)); ?> บาทนี้?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <button class="btn btn-success btn-sm">✅ อนุมัติ</button>
                    </form>

                    <form action="<?php echo e(route('admin.payments.reject', $payment->id)); ?>" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าจะปฏิเสธสลิปจำนวน <?php echo e(number_format($payment->amount_paid, 2)); ?> บาทนี้?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <button class="btn btn-danger btn-sm">❌ ปฏิเสธ</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\installment-new\resources\views/admin/payments/index.blade.php ENDPATH**/ ?>