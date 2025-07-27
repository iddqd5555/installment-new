<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'role', 'type', 'title', 'message', 'data', 'is_read', 'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    // ประเภทแจ้งเตือนหลัก (enum)
    const TYPE_SLIP = 'slip';
    const TYPE_PAYMENT = 'payment';
    const TYPE_ANNOUNCE = 'announce';
    const TYPE_OVERDUE = 'overdue';
    const TYPE_PAYMENT_OVERDUE_ADMIN = 'payment_overdue_admin';
    const TYPE_PAYMENT_OVERDUE_ADMIN_HIGHLIGHT = 'payment_overdue_admin_highlight';
    const TYPE_ADVANCE_DEDUCTED = 'advance_deducted';
    const TYPE_SYSTEM = 'system';
    const TYPE_OTHER = 'other';

    // กลุ่มผู้รับ (enum)
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';

    // Scope: สำหรับ query แจ้งเตือนลูกค้า+broadcast
    public function scopeForUser(Builder $query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere(function($q2){
                  $q2->whereNull('user_id')->where('role', self::ROLE_USER);
              });
        });
    }

    // Scope: สำหรับ query แจ้งเตือนแอดมิน+broadcast
    public function scopeForAdmin(Builder $query, $adminId = null)
    {
        if ($adminId) {
            return $query->where(function($q) use ($adminId) {
                $q->where('user_id', $adminId)
                  ->orWhere(function($q2){
                      $q2->whereNull('user_id')->where('role', self::ROLE_ADMIN);
                  });
            });
        }
        return $query->where('role', self::ROLE_ADMIN);
    }

    // ความสัมพันธ์: ผู้ใช้
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mark as read แบบปลอดภัย
    public function markAsRead()
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }
}
