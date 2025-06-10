<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts & Bootstrap -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
            @auth
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn-rounded">ออกจากระบบ</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-rounded">เข้าสู่ระบบ</a>
            @endauth
        </div>

        @auth
        <div class="credit-status position-static position-md-absolute">
            @php
                $creditStatus = auth()->user()->credit_status ?? 'เครดิตดีมาก';
                $statusColors = [
                    'เครดิตดีมาก' => 'linear-gradient(to right, #8e2de2, #4a00e0)',
                    'เครดิตดี' => 'linear-gradient(to right, #11998e, #38ef7d)',
                    'เครดิตปานกลาง' => 'linear-gradient(to right, #f7971e, #ffd200)',
                    'เครดิตแย่' => 'linear-gradient(to right, #e65c00, #F9D423)',
                    'เครดิตแย่มาก' => 'linear-gradient(to right, #93291E, #ED213A)',
                ];
                $currentColor = $statusColors[$creditStatus] ?? $statusColors['เครดิตปานกลาง'];
            @endphp

            <span class="badge px-2 py-1" style="background: {{ $currentColor }};">
                เครดิต: {{ $creditStatus }}
            </span>
        </div>
        @endauth
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="d-md-none px-4">
        <a href="/" class="menu-link">หน้าแรก</a>
        <a href="/gold" class="menu-link">ผ่อนทอง</a>
        <a href="/phone" class="menu-link">ผ่อนมือถือ</a>
        <a href="/contact" class="menu-link">ติดต่อเรา</a>
        @auth
            <span class="menu-link">เครดิต: {{ $creditStatus }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn-rounded w-100">ออกจากระบบ</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn-rounded w-100">เข้าสู่ระบบ</a>
        @endauth
    </div>
</nav>

<div class="container py-4" id="content-wrapper">
    @yield('content')
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

<footer class="text-center py-3 bg-light mt-5">
    <div class="container">
        <a href="{{ route('contact') }}" class="text-decoration-none">ติดต่อเรา</a>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
