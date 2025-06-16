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
        'role', // ✅ เพิ่มคอลัมน์นี้เพื่อแยกประเภท Admin
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // ✅ เพิ่ม Role แบบ constant
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_APPROVER = 'approver';

    // ตรวจสอบบทบาท
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isApprover(): bool
    {
        return $this->role === self::ROLE_APPROVER;
    }

    public function getNameAttribute(): string
    {
        return $this->username; // ใช้ username เป็น name ไปเลย
    }
}
