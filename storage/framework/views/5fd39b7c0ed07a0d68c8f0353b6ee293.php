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

    <?php $__empty_1 = true; $__currentLoopData = $installmentRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
        $dailyPayment = $request->daily_payment_amount ?? 0;
        $daysPassed = $request->start_date ? \Carbon\Carbon::parse($request->start_date)->diffInDays(today()) + 1 : 0;
        $totalShouldPay = $dailyPayment * $daysPassed;
        $totalPaid = $request->installmentPayments->where('status', 'approved')->sum('amount_paid') + $request->advance_payment;
        $dueToday = max($totalShouldPay - $totalPaid, 0);

        if ($dailyPayment > 0) {
            $overdueDays = max(0, floor(($totalShouldPay - $totalPaid) / $dailyPayment));
        } else {
            $overdueDays = 0; // ✅ ป้องกันการหารด้วย 0 ชัดเจน
        }

        $penaltyAmount = $overdueDays * 100;
    ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            📌 ผ่อนทองจำนวน: <strong><?php echo e(number_format($request->gold_amount ?? 0, 2)); ?> บาท</strong>
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
                        💰 <strong>ยอดชำระล่วงหน้า</strong><br><?php echo e(number_format($request->advance_payment, 2)); ?> บาท
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        📅 <strong>วันชำระครั้งถัดไป</strong><br>
                        <?php if($nextPayment = $request->installmentPayments()->where('status', 'pending')->orderBy('payment_due_date')->first()): ?>
                            <?php echo e(\Carbon\Carbon::parse($nextPayment->payment_due_date)->format('d/m/Y')); ?>

                        <?php else: ?>
                            ยังไม่กำหนด
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-danger">
                        ⚠️ <strong>ค่าปรับสะสม</strong><br><?php echo e(number_format($penaltyAmount, 2)); ?> บาท
                    </div>
                </div>
            </div>

            
            <div class="mb-3">
                <strong>ชำระแล้วทั้งหมด:</strong> <?php echo e(number_format($request->total_paid, 2)); ?> / <?php echo e(number_format($request->total_with_interest, 2)); ?> บาท
                <div class="progress mt-2">
                    <?php if($request->total_with_interest > 0): ?>
                        <?php
                            $paymentProgress = ($request->total_paid / $request->total_with_interest) * 100;
                        ?>
                    <?php else: ?>
                        <?php
                            $paymentProgress = 0;
                        ?>
                    <?php endif; ?>

                    <div class="progress-bar bg-success" style="width: <?php echo e($paymentProgress); ?>%;">
                        <?php echo e(number_format($paymentProgress, 2)); ?>%
                    </div>
                </div>
            </div>

            
            <div class="mb-3">
                <strong>ระยะเวลาการผ่อน:</strong>
                <?php echo e($daysPassed); ?> / <?php echo e($request->installment_period ?? 'N/A'); ?> วัน
                <div class="progress mt-2">
                    <?php if(isset($request->installment_period) && $request->installment_period > 0): ?>
                        <?php
                            $timeProgress = min(100, ($daysPassed / $request->installment_period) * 100); 
                        ?>
                    <?php else: ?>
                        <?php
                            $timeProgress = 0; // ป้องกัน Division by zero ชัดเจนที่สุด
                        ?>
                    <?php endif; ?>
                    <div class="progress-bar bg-info" style="width: <?php echo e($timeProgress); ?>%;">
                        <?php echo e(number_format($timeProgress, 2)); ?>%
                    </div>
                </div>
            </div>

            

            <div class="mt-4">
                <button class="btn btn-info" data-bs-toggle="collapse" data-bs-target="#bankInfo<?php echo e($request->id); ?>">🏦 ข้อมูลธนาคาร</button>
                <button class="btn btn-warning" data-bs-toggle="collapse" data-bs-target="#uploadSlip<?php echo e($request->id); ?>">📤 อัพโหลดสลิป</button>
            </div>

            <div class="collapse mt-3" id="bankInfo<?php echo e($request->id); ?>">
                <div class="card card-body">
                    <?php $__empty_2 = true; $__currentLoopData = $bankAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                        <div class="d-flex align-items-center mb-2">
                            <img src="<?php echo e(asset('storage/'.$bank->logo)); ?>" width="50" class="me-3">
                            <div>
                                <strong><?php echo e($bank->bank_name); ?></strong><br>
                                <?php echo e($bank->account_name); ?><br><?php echo e($bank->account_number); ?>

                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                        <div class="alert alert-secondary">ไม่มีข้อมูลบัญชีธนาคาร</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="collapse mt-3" id="uploadSlip<?php echo e($request->id); ?>">
                <form action="<?php echo e(route('payments.upload-proof', $request->id)); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="number" name="amount_paid" class="form-control mb-2" required placeholder="จำนวนเงินที่โอน (บาท)">
                    <input type="file" name="payment_proof" class="form-control mb-2" required>
                    <button class="btn btn-primary">✅ ส่งหลักฐาน</button>
                </form>
            </div>
        </div>
    </div>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="alert alert-warning">⚠️ ไม่มีข้อมูลการผ่อนทองที่อนุมัติค่ะ</div>
    <?php endif; ?>

    
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-secondary text-white">
            📋 ประวัติการชำระเงินล่าสุด
        </div>
        <div class="card-body">
            <?php if($payments->count()): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>วัน/เวลา</th>
                        <th>จำนวนเงิน</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($payment->created_at->format('d/m/Y H:i')); ?></td>
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
<!-- Include Bottom Navigation -->
<?php echo $__env->make('partials.bottom-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\installment-new\resources\views/dashboard.blade.php ENDPATH**/ ?>