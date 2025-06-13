<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(config('app.name', 'Wisdom Gold วิสด้อม โกลด์')); ?></title>
    <meta name="description" content="ผ่อนทอง ผ่อนมือถือ ง่ายๆ ไม่ต้องใช้บัตรเครดิต กับ Wisdom Gold วิสด้อม โกลด์">
    <meta name="keywords" content="ผ่อนทอง, ผ่อนมือถือ, สินเชื่อ, วิสด้อมโกลด์, Wisdom Gold, ผ่อนไอโฟน, KPLUS">
    <meta name="author" content="Wisdom Gold วิสด้อม โกลด์">

    <!-- Open Graph SEO (สำหรับ Facebook และ Social media) -->
    <meta property="og:title" content="<?php echo e(config('app.name', 'Wisdom Gold วิสด้อม โกลด์')); ?>">
    <meta property="og:description" content="ผ่อนทอง ผ่อนมือถือ ง่ายๆ ไม่ต้องใช้บัตรเครดิต กับ Wisdom Gold วิสด้อม โกลด์">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e(url('/')); ?>">
    <meta property="og:image" content="<?php echo e(asset('images/seo_image.png')); ?>">
    
    <!-- Fonts & Bootstrap -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- ไอคอนเว็บไซต์ -->
    <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>">
    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

</head>
<body class="bg-gray-50">

<div class="spinner-wrapper d-none" id="spinner">
    <div class="spinner-border text-success" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
<nav class="bg-kplus-green py-3 nav-shadow position-relative">
    <div class="container d-flex flex-wrap align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center gap-2">
            <button id="mobile-menu-btn" class="d-md-none">☰</button>
            <a href="/" class="text-lg font-bold text-white">
                <span class="d-block">WISDOM GOLD</span>
                <span class="d-block fs-6">วิสด้อม โกลด์</span>
            </a>
        </div>

        <div id="desktop-menu" class="d-none d-md-flex gap-3 align-items-center">
            <a href="/" class="menu-link">หน้าแรก</a>
            <a href="/gold" class="menu-link">ผ่อนทอง</a>
            <a href="/phone" class="menu-link">ผ่อนมือถือ</a>
            <a href="/contact" class="menu-link">ติดต่อเรา</a>
            <?php if(auth()->guard()->check()): ?>
                <form action="<?php echo e(route('logout')); ?>" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <button class="btn-rounded">ออกจากระบบ</button>
                </form>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="btn-rounded">เข้าสู่ระบบ</a>
            <?php endif; ?>
        </div>

        <?php if(auth()->guard()->check()): ?>
        <div class="credit-status position-static position-md-absolute">
            <?php
                $creditStatus = auth()->user()->credit_status ?? 'เครดิตดีมาก';
                $statusColors = [
                    'เครดิตดีมาก' => 'linear-gradient(to right, #8e2de2, #4a00e0)',
                    'เครดิตดี' => 'linear-gradient(to right, #11998e, #38ef7d)',
                    'เครดิตปานกลาง' => 'linear-gradient(to right, #f7971e, #ffd200)',
                    'เครดิตแย่' => 'linear-gradient(to right, #e65c00, #F9D423)',
                    'เครดิตแย่มาก' => 'linear-gradient(to right, #93291E, #ED213A)',
                ];
                $currentColor = $statusColors[$creditStatus] ?? $statusColors['เครดิตปานกลาง'];
            ?>

            <span class="badge px-2 py-1" style="background: <?php echo e($currentColor); ?>;">
                เครดิต: <?php echo e($creditStatus); ?>

            </span>
        </div>
        <?php endif; ?>
    </div>

    <div id="mobile-menu" class="d-md-none px-4">
        <a href="/" class="menu-link">หน้าแรก</a>
        <a href="/gold" class="menu-link">ผ่อนทอง</a>
        <a href="/phone" class="menu-link">ผ่อนมือถือ</a>
        <a href="/contact" class="menu-link">ติดต่อเรา</a>
        <?php if(auth()->guard()->check()): ?>
            <span class="menu-link">เครดิต: <?php echo e($creditStatus); ?></span>
            <form action="<?php echo e(route('logout')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button class="btn-rounded w-100">ออกจากระบบ</button>
            </form>
        <?php else: ?>
            <a href="<?php echo e(route('login')); ?>" class="btn-rounded w-100">เข้าสู่ระบบ</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container py-4 <?php echo e(request()->is('login') ? 'd-flex align-items-center justify-content-center flex-grow-1' : ''); ?>" 
     id="content-wrapper"
     style="<?php echo e(request()->is('login') ? 'background: linear-gradient(to bottom, #f3fcf3, #e1f7e1); min-height: calc(100vh - 120px);' : ''); ?>">
    <?php echo $__env->yieldContent('content'); ?>
</div>

<!-- JavaScript -->
<script>
const btn = document.getElementById('mobile-menu-btn');
const menu = document.getElementById('mobile-menu');

btn.addEventListener('click', () => {
    if (menu.classList.contains('active')) {
        menu.style.height = `${menu.scrollHeight}px`;
        requestAnimationFrame(() => {
            menu.style.height = '0px';
        });
        menu.classList.remove('active');
    } else {
        menu.style.height = '0px';
        menu.classList.add('active');
        requestAnimationFrame(() => {
            menu.style.height = `${menu.scrollHeight}px`;
        });
    }
});

menu.addEventListener('transitionend', () => {
    if (menu.classList.contains('active')) {
        menu.style.height = 'auto';
    } else {
        menu.style.height = '0px';
    }
});

window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) {
        menu.classList.remove('active');
        menu.style.height = null;
    }
});

</script>

<footer class="text-center py-3 bg-light footer-text">
    <small>© WISDOM GOLD GROUP CO., LTD. บริษัท วิสดอม โกลด์ กรุ้ป จำกัด</small>
</footer>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\installment-new\resources\views/layouts/app.blade.php ENDPATH**/ ?>