<?php $logs = $getState(); ?>

<!--[if BLOCK]><![endif]--><?php if(count($logs)): ?>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left border border-gray-300 rounded-xl bg-white shadow">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-3 border-b">วันเวลา</th>
                    <th class="py-2 px-3 border-b">IP</th>
                    <th class="py-2 px-3 border-b">VPN</th>
                    <th class="py-2 px-3 border-b">Latitude</th>
                    <th class="py-2 px-3 border-b">Longitude</th>
                    <th class="py-2 px-3 border-b">Notes</th>
                    <th class="py-2 px-3 border-b">แผนที่</th>
                </tr>
            </thead>
            <tbody>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="py-2 px-3 border-b whitespace-nowrap"><?php echo e($log->created_at?->format('Y-m-d H:i:s')); ?></td>
                        <td class="py-2 px-3 border-b"><?php echo e($log->ip); ?></td>
                        <td class="py-2 px-3 border-b"><?php echo e($log->vpn_status); ?></td>
                        <td class="py-2 px-3 border-b"><?php echo e($log->latitude); ?></td>
                        <td class="py-2 px-3 border-b"><?php echo e($log->longitude); ?></td>
                        <td class="py-2 px-3 border-b"><?php echo e($log->notes); ?></td>
                        <td class="py-2 px-3 border-b">
                            <!--[if BLOCK]><![endif]--><?php if($log->latitude && $log->longitude): ?>
                                <a href="https://www.google.com/maps?q=<?php echo e($log->latitude); ?>,<?php echo e($log->longitude); ?>" target="_blank" class="inline-flex items-center px-3 py-1 rounded bg-blue-500 text-white text-xs font-bold hover:bg-blue-600">
                                    ดูแผนที่
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="text-gray-400 italic">ไม่มีประวัติการเปลี่ยนแปลง</div>
<?php endif; ?><!--[if ENDBLOCK]><![endif]-->
<?php /**PATH C:\xampp\htdocs\installment-new\resources\views/filament/infolists/user_location_logs_table.blade.php ENDPATH**/ ?>