<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;

class Login extends BaseLogin
{
    protected static string $view = 'admin.login';

    public $prefix = '';
    public $username = '';
    public $password = '';

    protected $rules = [
        'prefix' => 'required',
        'username' => 'required',
        'password' => 'required',
    ];

    public function authenticate(): ?LoginResponse
    {
        $credentials = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        $admin = Admin::where('prefix', $this->prefix)
            ->where('username', $this->username)
            ->first();

        if (!$admin || !Auth::guard('admin')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'username' => __('ข้อมูลเข้าสู่ระบบไม่ถูกต้อง'),
            ]);
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
