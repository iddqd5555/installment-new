<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth('admin')->check() || !auth('admin')->user()->hasRole($roles)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
