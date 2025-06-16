<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @livewireStyles
    @filamentStyles
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Wisdom Gold วิสด้อม โกลด์') }}</title>
    <meta name="description" content="ผ่อนทอง ผ่อนมือถือ ง่ายๆ ไม่ต้องใช้บัตรเครดิต กับ Wisdom Gold วิสด้อม โกลด์">
    <meta name="keywords" content="ผ่อนทอง, ผ่อนมือถือ, สินเชื่อ, วิสด้อมโกลด์, Wisdom Gold, ผ่อนไอโฟน, KPLUS">
    <meta name="author" content="Wisdom Gold วิสด้อม โกลด์">

    <!-- Open Graph SEO (สำหรับ Facebook และ Social media) -->
    <meta property="og:title" content="{{ config('app.name', 'Wisdom Gold วิสด้อม โกลด์') }}">
    <meta property="og:description" content="ผ่อนทอง ผ่อนมือถือ ง่ายๆ ไม่ต้องใช้บัตรเครดิต กับ Wisdom Gold วิสด้อม โกลด์">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('images/seo_image.png') }}">
    
    <!-- Fonts & Bootstrap -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- ไอคอนเว็บไซต์ -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-gray-50">

<div class="spinner-wrapper d-none" id="spinner">
    <div class="spinner-border text-success" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
<nav class="bg-kplus-green py-3 nav-shadow position-relative">
    <div class="container d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center gap-2">
            <button id="mobile-menu-btn" class="d-md-none">☰</button>
            <a href="/" class="text-lg font-bold text-white">
                <span class="d-block">WISDOM GOLD</span>
                <span class="d-block fs-6">วิสด้อม โกลด์</span>
            </a>
        </div>

        <div id="desktop-menu" class="d-none d-md-flex gap-3 align-items-center">
            <a href="/" class="menu-link">หน้าแรก</a>
            <a href="{{ auth()->check() ? route('gold.member') : route('gold.index') }}" class="menu-link">ผ่อนทอง</a>
            <a href="/phone" class="menu-link">ผ่อนมือถือ</a>
            <a href="/contact" class="menu-link">ติดต่อเรา</a>

            @auth
                <a href="{{ route('profile.edit') }}" class="menu-link btn-rounded bg-white text-dark shadow-sm">
                    แก้ไขข้อมูลส่วนตัว
                </a>

                <form action="{{ route('logout') }}" method="POST" class="ms-2">
                    @csrf
                    <button class="btn-rounded btn-logout">ออกจากระบบ</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-rounded">เข้าสู่ระบบ</a>
            @endauth
        </div>

        @auth
        <div class="position-absolute top-0 end-0 p-2 credit-status-desktop">
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

                $identityStatus = auth()->user()->identity_verification_status;
                $identityBadge = $identityStatus === 'verified'
                    ? ['color' => '#28aaff', 'text' => 'ยืนยันตัวตนแล้ว']
                    : ['color' => '#ff3c41', 'text' => 'ยังไม่ยืนยันตัวตน'];
            @endphp

            <div class="d-flex gap-2 flex-column align-items-end">
                <span class="badge px-2 py-1" style="background: {{ $currentColor }};">
                    เครดิต: {{ $creditStatus }}
                </span>
                <span class="badge px-2 py-1" style="background-color: {{ $identityBadge['color'] }};">
                    {{ $identityBadge['text'] }}
                </span>
            </div>
        </div>
        @endauth
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="d-md-none px-4">
        <a href="/" class="menu-link">หน้าแรก</a>
        <a href="{{ route('profile.edit') }}" class="menu-link">แก้ไขข้อมูลส่วนตัว</a>
        <a href="{{ auth()->check() ? route('gold.member') : route('gold.index') }}" class="menu-link">ผ่อนทอง</a>
        <a href="/phone" class="menu-link">ผ่อนมือถือ</a>
        <a href="/contact" class="menu-link">ติดต่อเรา</a>

        @auth
            <div class="d-flex gap-2 flex-column align-items-start my-2">
                <span class="badge px-2 py-1" style="background: {{ $currentColor }};">
                    เครดิต: {{ $creditStatus }}
                </span>
                <span class="badge px-2 py-1" style="background-color: {{ $identityBadge['color'] }};">
                    {{ $identityBadge['text'] }}
                </span>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn-rounded w-100 btn-logout">ออกจากระบบ</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn-rounded w-100">เข้าสู่ระบบ</a>
        @endauth
    </div>
</nav>


<div class="container py-4 {{ request()->is('login') ? 'd-flex align-items-center justify-content-center flex-grow-1' : '' }}" 
     id="content-wrapper"
     style="{{ request()->is('login') ? 'background: linear-gradient(to bottom, #f3fcf3, #e1f7e1); min-height: calc(100vh - 120px);' : '' }}">
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

<footer class="text-center py-3 bg-light footer-text">
    <small>© WISDOM GOLD GROUP CO., LTD. บริษัท วิสดอม โกลด์ กรุ้ป จำกัด</small>
</footer>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')

@livewireScripts
@filamentScripts
</body>
</html>
