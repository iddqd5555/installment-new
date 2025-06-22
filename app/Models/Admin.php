<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'prefix',
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Roles ชัดเจน 3 ระดับ
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

    // ชื่อที่แสดงคือ username
    public function getNameAttribute(): string
    {
        return $this->username;
    }
}
