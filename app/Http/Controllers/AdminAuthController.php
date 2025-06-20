<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    // แสดงฟอร์ม login
    public function showLoginForm()
    {
        return view('admin.login');
    }

    // จัดการล็อคอิน
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'prefix'   => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // ตรวจสอบ credentials ของ admin
        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->route('custom.admin.dashboard');
        }

        // ถ้า login ไม่สำเร็จ กลับไปหน้า login พร้อม error
        return back()->withErrors(['login' => 'ข้อมูลเข้าสู่ระบบไม่ถูกต้อง']);
    }

    // ออกจากระบบ
    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('filament.admin.auth.login');
    }
}
