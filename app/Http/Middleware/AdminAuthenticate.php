<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {

            // สำคัญมาก ตรวจสอบ guard ให้ดี!
            if ($request->is('admin') || $request->is('admin/*')) {
                return '/admin'; // ใช้ของ Filament
            }

            return route('login'); // ใช้ของ user ปกติ
        }
        return null;
    }
}
