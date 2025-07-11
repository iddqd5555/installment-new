<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class Admin extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $fillable = ['prefix', 'username', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    const ROLE_STAFF = 'staff';
    const ROLE_ADMIN = 'admin';
    const ROLE_OAA = 'OAA';

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isOAA(): bool
    {
        return $this->role === self::ROLE_OAA;
    }

    public function hasRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function getNameAttribute(): string
    {
        return $this->username;
    }

    // ✅ ปรับใหม่ชัดเจนที่สุด ให้ Filament ตรวจสอบ null ให้ถูกต้อง
    public function canAccessPanel(Panel $panel): bool
    {
        return auth('admin')->check();
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

}
