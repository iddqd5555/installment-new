@extends('layouts.app')

@section('content')
<div class="flex-grow-1 d-flex align-items-center justify-content-center py-3">
    <div class="card shadow-lg border-0 rounded-4 p-4" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-3">
            <h4 class="fw-bold text-success">เข้าสู่ระบบสมาชิก</h4>
            <p class="text-muted small">กรอกเบอร์โทรศัพท์เพื่อเข้าสู่ระบบ</p>
        </div>

        <form id="login-form" method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-floating mb-3">
                <input type="text" class="form-control rounded-3" id="phone" name="phone" placeholder="เบอร์โทรศัพท์" value="{{ old('phone') }}" required>
                <label for="phone">📞 เบอร์โทรศัพท์</label>
                @error('phone')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-floating mb-3 d-none" id="password-container">
                <input type="password" class="form-control rounded-3" id="password" name="password" placeholder="รหัสผ่าน" required>
                <label for="password">🔑 รหัสผ่าน</label>
                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <button type="submit" class="btn btn-success w-100 rounded-3 py-2">เข้าสู่ระบบ</button>
        </form>
    </div>
</div>

<script>
document.getElementById('login-form').addEventListener('submit', function(e) {
    let phoneInput = document.getElementById('phone');
    let passwordContainer = document.getElementById('password-container');
    let passwordInput = document.getElementById('password');

    if(passwordContainer.classList.contains('d-none')) {
        e.preventDefault();
        if(phoneInput.value.trim().length >= 10) {
            passwordContainer.classList.remove('d-none');
            passwordInput.removeAttribute('disabled'); // เปิดใช้งานช่อง password
            passwordInput.focus();
        } else {
            alert('กรุณากรอกเบอร์โทรศัพท์ให้ครบถ้วน');
        }
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const passwordContainer = document.getElementById('password-container');
    const passwordInput = document.getElementById('password');
    passwordInput.setAttribute('disabled', 'disabled'); // เริ่มต้นให้ช่อง password ถูก disable ไว้ก่อน

    @if(session('show_password') || $errors->has('password'))
        passwordContainer.classList.remove('d-none');
        passwordInput.removeAttribute('disabled');
    @endif
});
</script>
@endsection
