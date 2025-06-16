<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ถ้าเป็น Admin (แยก guard admin ชัดเจน)
        if (Auth::guard('admin')->check()) {
            if ($request->routeIs('admin.login') || $request->routeIs('admin.login.submit')) {
                return redirect()->route('admin.dashboard');
            }
            return $next($request);
        }

        // ถ้าไม่ใช่ Admin ต้องไปหน้า login admin
        return redirect()->route('admin.login');
    }
}
