<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $admin = Auth::guard('admin')->user();
        
        if ($admin && in_array($admin->role, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized.');
    }
}
