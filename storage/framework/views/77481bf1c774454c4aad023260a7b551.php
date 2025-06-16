

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <h3>แก้ไขข้อมูลส่วนตัว</h3>

    <?php if(session('status')): ?>
        <div class="alert alert-success"><?php echo e(session('status')); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('profile.update')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <div class="mb-3">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" value="<?php echo e(old('email', $user->email)); ?>">
        </div>

        <div class="mb-3">
            <label>ที่อยู่:</label>
            <input type="text" name="address" class="form-control" value="<?php echo e(old('address', $user->address)); ?>">
        </div>

        <div class="mb-3">
            <label>วันเดือนปีเกิด:</label>
            <input type="date" name="date_of_birth" class="form-control" value="<?php echo e(old('date_of_birth', $user->date_of_birth)); ?>">
        </div>

        <div class="mb-3">
            <label>เพศ:</label>
            <select name="gender" class="form-select">
                <option value="">เลือกเพศ</option>
                <option value="male" <?php echo e(old('gender', $user->gender) == 'male' ? 'selected' : ''); ?>>ชาย</option>
                <option value="female" <?php echo e(old('gender', $user->gender) == 'female' ? 'selected' : ''); ?>>หญิง</option>
            </select>
        </div>

        <div class="mb-3">
            <label>รายได้:</label>
            <input type="number" name="salary" class="form-control" value="<?php echo e(old('salary', $user->salary)); ?>">
        </div>

        <div class="mb-3">
            <label>สถานที่ทำงาน:</label>
            <input type="text" name="workplace" class="form-control" value="<?php echo e(old('workplace', $user->workplace)); ?>">
        </div>

        <div class="mb-3">
            <label>ธนาคาร:</label>
            <input type="text" name="bank_name" class="form-control" value="<?php echo e(old('bank_name', $user->bank_name)); ?>">
        </div>

        <div class="mb-3">
            <label>เลขบัญชีธนาคาร:</label>
            <input type="text" name="bank_account_number" class="form-control" value="<?php echo e(old('bank_account_number', $user->bank_account_number)); ?>">
        </div>

        <div class="mb-3">
            <label>ชื่อบัญชีธนาคาร:</label>
            <input type="text" name="bank_account_name" class="form-control" value="<?php echo e(old('bank_account_name', $user->bank_account_name)); ?>">
        </div>

        <div class="mb-3">
            <label>ภาพบัตรประชาชน:</label>
            <input type="file" name="id_card_image" class="form-control">
        </div>

        <div class="mb-3">
            <label>ภาพสลิปเงินเดือน:</label>
            <input type="file" name="slip_salary_image" class="form-control">
        </div>

        <div class="mb-3">
            <label>เอกสารเพิ่มเติม:</label>
            <input type="file" name="additional_documents" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\installment-new\resources\views/profile.blade.php ENDPATH**/ ?>