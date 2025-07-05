<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <h2 class="mb-4">📜 ประวัติคำสั่งซื้อของคุณ</h2>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>สินค้า</th>
                <th>ราคา</th>
                <th>สถานะ</th>
                <th>วันที่ขอผ่อน</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($order->id); ?></td>
                <td><?php echo e($order->product_name ?? 'ทองรูปพรรณ'); ?></td>
                <td><?php echo e(number_format($order->price ?? $order->gold_amount, 2)); ?> บาท</td>
                <td><?php echo e(ucfirst($order->status)); ?></td>
                <td><?php echo e($order->created_at->format('d/m/Y')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\installment-new\resources\views/orders/history.blade.php ENDPATH**/ ?>