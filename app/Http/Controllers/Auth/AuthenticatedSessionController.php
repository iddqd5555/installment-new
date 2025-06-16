<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Providers\RouteServiceProvider; // 👈 เพิ่มตรงนี้
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate(['phone' => ['required', 'string']]);

        // เช็คว่าเบอร์โทรนี้มีอยู่ในระบบหรือไม่
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'ไม่พบเบอร์โทรนี้ในระบบ'])->onlyInput('phone');
        }

        // ถ้ามีเบอร์โทรแล้ว แต่ยังไม่ได้กรอกรหัสผ่าน
        if (!$request->filled('password')) {
            return back()->withInput(['phone' => $request->phone])->with('show_password', true);
        }

        // ถ้ามีการกรอกรหัสผ่านแล้ว
        if (Auth::attempt(['phone' => $request->phone, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['password' => 'รหัสผ่านไม่ถูกต้อง'])->withInput(['phone' => $request->phone])->with('show_password', true);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function destroyAdmin(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    // หน้า login Admin
    public function createAdmin()
    {
        return view('admin.auth.login');
    }

    // ตรวจสอบ Admin
    public function storeAdmin(Request $request)
    {
        $credentials = $request->validate([
            'prefix' => ['required'],
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'username' => 'Invalid credentials.',
        ]);
    }
}
