<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;

class Login extends BaseLogin
{
    protected static string $view = 'filament-panels::pages.auth.login';

    public $prefix = '';
    public $username = '';
    public $password = '';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\TextInput::make('prefix')
                    ->label('Prefix')
                    ->required()
                    ->autofocus(),

                \Filament\Forms\Components\TextInput::make('username')
                    ->label('Username')
                    ->required(),

                \Filament\Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(),
            ]);
    }

    protected function getCredentials(): array
    {
        return [
            'prefix' => $this->form->getState()['prefix'],
            'username' => $this->form->getState()['username'],
            'password' => $this->form->getState()['password'],
        ];
    }

    protected function getLoginUsername(): string
    {
        return 'username';
    }

    public function authenticate(): ?LoginResponse
    {
        $credentials = $this->getCredentials();

        if (!Auth::guard('admin')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'username' => __('ข้อมูลเข้าสู่ระบบไม่ถูกต้อง'),
            ]);
        }

        // ระบุ guard admin ชัดเจนที่สุดตอน regenerate session
        request()->session()->regenerate();

        // ตั้งค่า guard เริ่มต้นเป็น admin (สำคัญมาก)
        Auth::shouldUse('admin');

        return redirect()->intended(route('custom.admin.dashboard'));
    }

    // ✅ เพิ่ม method นี้ชัดเจนที่สุด
    protected function getRedirectUrl(): string
    {
        return route('filament.admin.pages.dashboard');
    }
}
