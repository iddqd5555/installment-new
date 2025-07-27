<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\InstallmentRequest;

class InstallmentRequestPolicy
{
    // staff เห็นแค่ของตัวเอง, admin/OAA เห็นหมด
    public function view(Admin $user, InstallmentRequest $request)
    {
        if ($user->role === 'admin' || $user->role === 'OAA') {
            return true;
        }
        return $request->responsible_staff === $user->username;
    }

    // staff อนุมัติแค่ของตัวเองที่ pending, admin/OAA ได้หมด
    public function approve(Admin $user, InstallmentRequest $request)
    {
        if ($user->role === 'staff') {
            return $request->responsible_staff === $user->username && $request->status === 'pending';
        }
        return $user->role === 'admin' || $user->role === 'OAA';
    }

    // เพิ่มเติม: ป้องกันลบ/แก้ไขเฉพาะสิทธิ์ที่อนุญาต (optional)
    public function delete(Admin $user, InstallmentRequest $request)
    {
        return $user->role === 'admin' || $user->role === 'OAA';
    }

    public function update(Admin $user, InstallmentRequest $request)
    {
        if ($user->role === 'staff') {
            return $request->responsible_staff === $user->username;
        }
        return $user->role === 'admin' || $user->role === 'OAA';
    }
}
