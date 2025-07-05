<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'prefix' => ['required'],
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials, true)) {
            $request->session()->regenerate();
            return redirect()->intended(route('custom.admin.dashboard'));
        }

        throw ValidationException::withMessages([
            'username' => ['ข้อมูลล็อกอินไม่ถูกต้อง'],
        ]);
    }
}
