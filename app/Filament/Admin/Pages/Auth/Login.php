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
        $admin = Admin::where('prefix', $this->prefix)
        ->where('username', $this->username)
        ->first();

    if (!$admin || !\Hash::check($this->password, $admin->password)) {
        throw ValidationException::withMessages([
            'username' => __('ข้อมูลเข้าสู่ระบบไม่ถูกต้อง'),
        ]);
    }

    // login ให้สำเร็จ
    Auth::guard('admin')->login($admin);
    session()->regenerate();

    return app(LoginResponse::class);
    }
}
