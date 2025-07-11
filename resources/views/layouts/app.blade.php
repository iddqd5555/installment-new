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
    <!-- Open Graph SEO -->
    <meta property="og:title" content="{{ config('app.name', 'Wisdom Gold วิสด้อม โกลด์') }}">
    <meta property="og:description" content="ผ่อนทอง ผ่อนมือถือ ง่ายๆ ไม่ต้องใช้บัตรเครดิต กับ Wisdom Gold วิสด้อม โกลด์">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('images/seo_image.png') }}">
    <!-- Google Fonts + Bootstrap + Bootstrap Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <!-- Custom Theme CSS -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Sarabun', sans-serif; background: #f7f7f7; }
        .theme-bg { background: #730A22 !important; }
        .theme-color { color: #730A22 !important; }
        .btn-theme { background: #730A22; color: #fff; border-radius: 8px; padding: 8px 24px; font-weight: bold; }
        .navbar .btn-theme, .btn-theme:active, .btn-theme:focus, .btn-theme:hover { color: #fff; }
        .menu-link { color: #fff; font-weight: bold; padding: 8px 16px; border-radius: 6px; transition: background .15s; }
        .menu-link:hover, .menu-link.active { background: #a42638; color: #fff; }
        .btn-rounded { border-radius: 8px !important; font-weight: bold; }
        .btn-logout { background: #fff !important; color: #730A22 !important; border-radius: 8px; border: 1px solid #730A22; }
        .btn-logout:hover { background: #730A22 !important; color: #fff !important; }
        #mobile-menu { background: #730A22; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<div class="spinner-wrapper d-none" id="spinner">
    <div class="spinner-border theme-color" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<nav class="theme-bg py-3 nav-shadow position-relative">
    <div class="container d-flex align-items-center justify-content-between position-relative">
        <div class="d-flex align-items-center gap-2">
            <button id="mobile-menu-btn" class="d-md-none" style="background:none;border:none;font-size:1.6em;color:#fff;">☰</button>
            <a href="/" class="text-lg font-bold text-white" style="text-decoration:none;">
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
                <a href="{{ route('profile.edit') }}" class="menu-link btn-rounded bg-white text-dark shadow-sm">แก้ไขข้อมูลส่วนตัว</a>
                <form action="{{ route('logout') }}" method="POST" class="ms-2 d-inline">
                    @csrf
                    <button class="btn-rounded btn-logout">ออกจากระบบ</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-rounded btn-theme ms-2">เข้าสู่ระบบ</a>
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
    <div id="mobile-menu" class="d-md-none px-4" style="overflow:hidden;height:0;transition:height 0.3s;">
        <a href="/" class="menu-link w-100 d-block mb-1">หน้าแรก</a>
        <a href="{{ route('profile.edit') }}" class="menu-link w-100 d-block mb-1">แก้ไขข้อมูลส่วนตัว</a>
        <a href="{{ auth()->check() ? route('gold.member') : route('gold.index') }}" class="menu-link w-100 d-block mb-1">ผ่อนทอง</a>
        <a href="/phone" class="menu-link w-100 d-block mb-1">ผ่อนมือถือ</a>
        <a href="/contact" class="menu-link w-100 d-block mb-2">ติดต่อเรา</a>
        @auth
            <div class="d-flex gap-2 flex-column align-items-start my-2">
                <span class="badge px-2 py-1" style="background: {{ $currentColor }};">
                    เครดิต: {{ $creditStatus }}
                </span>
                <span class="badge px-2 py-1" style="background-color: {{ $identityBadge['color'] }};">
                    {{ $identityBadge['text'] }}
                </span>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="mb-2">
                @csrf
                <button class="btn-rounded btn-logout w-100">ออกจากระบบ</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="btn-rounded btn-theme w-100 mb-2">เข้าสู่ระบบ</a>
        @endauth
    </div>
</nav>

<main class="flex-grow-1">
    <div class="container py-4 {{ request()->is('login') ? 'd-flex align-items-center justify-content-center flex-grow-1' : '' }}" id="content-wrapper"
        style="{{ request()->is('login') ? 'background: linear-gradient(to bottom, #f3fcf3, #e1f7e1); min-height: calc(100vh - 120px);' : '' }}">
        @yield('content')
    </div>
</main>
@include('layouts.footer')

<!-- JavaScript for mobile menu -->
<script>
const btn = document.getElementById('mobile-menu-btn');
const menu = document.getElementById('mobile-menu');
if(btn && menu) {
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
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
@livewireScripts
@filamentScripts
@stack('scripts')
</body>
</html>
