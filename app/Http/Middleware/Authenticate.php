<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            if (in_array('admin', $this->guards())) {
                return route('filament.admin.auth.login'); // ชัดเจนและถูกต้อง
            }

            return route('login');
        }

        return null;
    }
}
