<style>
.bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 65px;
    background-color: #ffffff;
    box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-around;
    align-items: center;
    border-top: 1px solid #ddd;
    z-index: 9999;
}
.bottom-nav .nav-item {
    flex-grow: 1;
    text-align: center;
    font-size: 12px;
    color: #888;
    text-decoration: none;
    padding-top: 5px;
}
.bottom-nav .nav-item.active {
    color: #198754; /* Bootstrap green */
}
.bottom-nav .nav-item .icon {
    font-size: 20px;
    margin-bottom: 2px;
}
body {
    padding-bottom: 70px; /* Space for bottom navigation */
}
</style>

<div class="bottom-nav">
    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <div class="icon">🏠</div>
        หน้าแรก
    </a>

    <a href="{{ route('orders.history') }}" class="nav-item {{ request()->routeIs('orders.history') ? 'active' : '' }}">
        <div class="icon">📋</div>
        ประวัติ
    </a>

    <a href="{{ route('notifications') }}" class="nav-item {{ request()->routeIs('notifications') ? 'active' : '' }}">
        <div class="icon">🔔</div>
        แจ้งเตือน
    </a>

    <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
        <div class="icon">👤</div>
        โปรไฟล์
    </a>
</div>
