

<?php $__env->startSection('content'); ?>
<div class="container py-5 mb-5">
    <h2>🔔 แจ้งเตือนทั้งหมด</h2>
    <ul>
        <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li>
                <?php echo e($notification->data['message'] ?? 'ไม่มีข้อความ'); ?>

                <small class="text-muted">(<?php echo e($notification->created_at->format('d/m/Y H:i')); ?>)</small>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li>ไม่มีแจ้งเตือนใหม่ค่ะ</li>
        <?php endif; ?>
    </ul>

    <?php echo e($notifications->links()); ?>

</div>
<?php echo $__env->make('partials.bottom-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\installment-new\resources\views/notifications/index.blade.php ENDPATH**/ ?>