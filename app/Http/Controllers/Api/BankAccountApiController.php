<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;

class BankAccountApiController extends Controller
{
    // ดึงบัญชีที่เปิดใช้งานทั้งหมด (หรือแค่บัญชีหลัก)
    public function active()
    {
        $banks = BankAccount::where('is_active', 1)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get([
                'id', 'bank_name', 'account_name', 'account_number', 'logo', 'is_default'
            ]);
        return response()->json(['banks' => $banks]);
    }
}
