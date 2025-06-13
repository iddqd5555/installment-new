

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <h3 class="mb-4">📌 รายการคำขอผ่อนสินค้า</h3>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>ชื่อผู้ขอผ่อน</th>
                <th>เบอร์โทร</th>
                <th>จำนวนทอง (บาท)</th>
                <th>จำนวนเดือน</th>
                <th>สถานะ</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($request->fullname ?? ($request->user->name . ' ' . $request->user->surname)); ?></td>
                    <td><?php echo e($request->phone ?? $request->user->phone); ?></td>
                    <td><?php echo e($request->gold_amount); ?></td>
                    <td><?php echo e($request->installment_period); ?></td>
                    <td>
                        <?php if($request->status == 'approved'): ?>
                            <span class="badge bg-success">อนุมัติแล้ว</span>
                        <?php elseif($request->status == 'pending'): ?>
                            <span class="badge bg-warning text-dark">รออนุมัติ</span>
                        <?php else: ?>
                            <span class="badge bg-danger">ปฏิเสธ</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo e(route('admin.installments.edit', $request->id)); ?>" class="btn btn-primary btn-sm">แก้ไข/อนุมัติ</a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="text-center">ไม่มีข้อมูลคำขอผ่อน</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\installment-new\resources\views/admin/installments/index.blade.php ENDPATH**/ ?>