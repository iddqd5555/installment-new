<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',      // ✅ เพิ่มบรรทัดนี้
        'account_number', // ✅ เพิ่มบรรทัดนี้
        'account_name',   // ✅ เพิ่มบรรทัดนี้
        'logo',           // ✅ เพิ่มบรรทัดนี้
    ];
}
