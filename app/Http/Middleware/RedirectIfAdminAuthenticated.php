<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // ถ้า guard เป็น admin ให้ไปที่ dashboard
                if ($guard === 'admin') {
                    return redirect('/admin/dashboard');
                }
                // guard ปกติ (user)
                return redirect('/dashboard');
            }
        }

        return $next($request);
    }
}
