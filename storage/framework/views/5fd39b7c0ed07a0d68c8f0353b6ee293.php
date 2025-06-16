<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <?php if(session('error')): ?>
        <div class="alert alert-danger">
            <strong>เกิดข้อผิดพลาด!</strong><br>
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <strong>ทำรายการเรียบร้อยแล้ว!</strong><br>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    <h2 class="text-success">Dashboard การผ่อนของคุณ</h2>

    <?php if(auth()->user()->unreadNotifications->count()): ?>
        <div class="alert alert-info">
            <ul>
                <?php $__currentLoopData = auth()->user()->unreadNotifications->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <?php echo e(\Illuminate\Support\Arr::get($notification->data, 'message', 'ไม่มีข้อความ')); ?>

                        <?php if(\Illuminate\Support\Arr::get($notification->data, 'due_date')): ?>
                            (กำหนดชำระ: <?php echo e(\Carbon\Carbon::parse(\Illuminate\Support\Arr::get($notification->data, 'due_date'))->format('d/m/Y')); ?>)
                        <?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php $__empty_1 = true; $__currentLoopData = $installmentRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">📌 ผ่อนทอง (<?php echo e(number_format($request->gold_amount, 2)); ?> บาท)</h5>

                
                <div class="card bg-success text-white mb-3">
                    <div class="card-body">
                        <p><strong>💵 ยอดที่ต้องชำระเดือนนี้:</strong> 
                        <?php
                            // ประกาศตัวแปร monthlyPayment ก่อนใช้งาน
                            $monthlyPayment = $request->total_with_interest / $request->installment_period;

                            $paidThisMonth = $request->installmentPayments
                                ->where('status', 'approved')
                                ->filter(function($payment) {
                                    return \Carbon\Carbon::parse($payment->created_at)->isCurrentMonth();
                                })
                                ->sum('amount_paid');

                            $dueThisMonth = $monthlyPayment - $paidThisMonth;
                        ?>

                        <?php echo e(number_format(max($dueThisMonth, 0), 2)); ?> บาท
                        </p>
                        <strong>ชำระแล้ว:</strong> <?php echo e(number_format($request->total_paid, 2)); ?> บาท<br>
                        <strong>คงเหลือ:</strong> <?php echo e(number_format($request->remaining_amount, 2)); ?> บาท
                        <div class="progress mt-2">
                            <?php
                                $paymentProgress = $request->total_with_interest > 0
                                    ? ($request->total_paid / $request->total_with_interest) * 100
                                    : 0;
                            ?>
                            <div class="progress-bar bg-light" style="width: <?php echo e($paymentProgress); ?>%;">
                                <?php echo e(number_format($paymentProgress, 2)); ?>%
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card bg-info text-white mb-3">
                    <div class="card-body">
                        <strong>ระยะเวลาการผ่อน:</strong> <?php echo e($request->installment_period); ?> เดือน (เหลือ <?php echo e($request->remaining_months); ?> เดือน)
                        <div class="progress mt-2">
                            <?php
                                $timeProgress = (($request->installment_period - $request->remaining_months) / $request->installment_period) * 100;
                            ?>
                            <div class="progress-bar bg-light" style="width: <?php echo e($timeProgress); ?>%;">
                                <?php echo e(number_format($timeProgress, 2)); ?>%
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                    $monthlyPayment = $request->total_with_interest / $request->installment_period;
                ?>

                <p><strong>📅 เดือนที่เหลือ:</strong> <?php echo e($request->remaining_months); ?> เดือน</p>
                <p><strong>💵 ยอดที่ต้องชำระครั้งถัดไป:</strong> <?php echo e(number_format($monthlyPayment, 2)); ?> บาท</p>
                <p><strong>📆 วันชำระครั้งถัดไป:</strong> 
                    <?php if($request->next_payment_date): ?>
                        <?php echo e(\Carbon\Carbon::parse($request->next_payment_date)->format('d/m/Y')); ?>

                    <?php else: ?>
                        ยังไม่กำหนด
                    <?php endif; ?>
                </p>

                
                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <h5>📌 ช่องทางการชำระเงิน</h5>
                        <?php $__empty_2 = true; $__currentLoopData = $bankAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                            <div class="bank-info my-2 d-flex align-items-center">
                                <img src="<?php echo e(asset('storage/'.$bank->logo)); ?>" width="60" alt="<?php echo e($bank->bank_name); ?>" class="me-3">
                                <div>
                                    <strong><?php echo e($bank->bank_name); ?></strong><br>
                                    ชื่อบัญชี: <?php echo e($bank->account_name); ?><br>
                                    เลขที่บัญชี: <?php echo e($bank->account_number); ?>

                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                            <div class="alert alert-warning">⚠️ ไม่มีข้อมูลบัญชีธนาคาร</div>
                        <?php endif; ?>
                    </div>
                </div>

                <button class="btn btn-warning" type="button" data-bs-toggle="collapse"
                    data-bs-target="#uploadSlip<?php echo e($request->id); ?>" aria-expanded="false">
                    อัพโหลดสลิป
                </button>

                <div class="collapse mt-3" id="uploadSlip<?php echo e($request->id); ?>">
                    <div class="card card-body">
                        <form id="payment-form-<?php echo e($request->id); ?>" action="<?php echo e(route('payments.upload-proof', $request->id)); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" id="remaining_amount_<?php echo e($request->id); ?>" value="<?php echo e($request->remaining_amount); ?>">

                            <div class="mb-3">
                                <label class="form-label">จำนวนเงินที่โอน (บาท)</label>
                                <input type="number" class="form-control" id="amount_paid_<?php echo e($request->id); ?>" name="amount_paid" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">อัปโหลดสลิปธนาคาร</label>
                                <input type="file" class="form-control" name="payment_proof" required>
                            </div>
                            <button class="btn btn-primary" type="submit">ส่งหลักฐานการชำระเงิน</button>
                        </form>
                    </div>
                </div>
                <hr>

                🌟 ราคาทองที่อนุมัติ: <strong><?php echo e(number_format($request->approved_gold_price, 2)); ?> บาท</strong><br>
                💳 ราคารวมทองคำ: <strong><?php echo e(number_format($request->total_gold_price, 2)); ?> บาท</strong><br>
                📌 ดอกเบี้ย (<?php echo e($request->interest_rate); ?>%): <strong><?php echo e(number_format($request->interest_amount, 2)); ?> บาท</strong><br>
                💰 เงินรวมดอกเบี้ย: <strong><?php echo e(number_format($request->total_with_interest, 2)); ?> บาท</strong><br>
            </div>
        </div>
     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="alert alert-warning">⚠️ ไม่มีข้อมูลการผ่อนที่อนุมัติค่ะ</div>
    <?php endif; ?>

    
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h5>📌 ประวัติการชำระเงิน</h5>
            <?php if($payments->count() > 0): ?>
                <div class="payment-history mt-3">
                    <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="payment-item d-flex align-items-center justify-content-between shadow-sm p-3 rounded mb-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-cash-stack text-success me-3" style="font-size: 2rem;"></i>
                            <div>
                                <strong>โอนเงิน</strong><br>
                                <small class="text-muted"><?php echo e($payment->created_at->format('d/m/Y H:i')); ?></small>
                            </div>
                        </div>
                        <div class="text-end">
                            <strong><?php echo e(number_format($payment->amount_paid, 2)); ?> บาท</strong><br>
                            <?php if($payment->status == 'approved'): ?>
                                <span class="badge bg-success">เงินเข้า</span>
                            <?php elseif($payment->status == 'pending'): ?>
                                <span class="badge bg-warning text-dark">อยู่ระหว่างการตรวจสอบ</span>
                            <?php else: ?>
                                <span class="badge bg-danger">ผิดพลาด</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="text-muted mt-3">ยังไม่มีประวัติการชำระเงินค่ะ</p>
            <?php endif; ?>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php $__currentLoopData = $installmentRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    document.getElementById('payment-form-<?php echo e($request->id); ?>').addEventListener('submit', function(e) {
        const amountPaid = parseFloat(document.getElementById('amount_paid_<?php echo e($request->id); ?>').value);
        const remainingAmount = parseFloat(document.getElementById('remaining_amount_<?php echo e($request->id); ?>').value);

        if (amountPaid > remainingAmount) {
            e.preventDefault();
            alert('⚠️ จำนวนเงินที่ชำระเกินยอดคงเหลือที่ต้องชำระค่ะ!');
        }
    });
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\installment-new\resources\views/dashboard.blade.php ENDPATH**/ ?>