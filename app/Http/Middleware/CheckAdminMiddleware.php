<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // ตรวจสอบการล็อคอินก่อน
        if (!auth()->check()) {
            return redirect('/login');
        }

        // ตรวจสอบสถานะแอดมินชัดเจน
        if (auth()->user()->is_admin != 1) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        // ให้ดำเนินการต่อถ้าเป็นแอดมิน
        return $next($request);
    }
}
