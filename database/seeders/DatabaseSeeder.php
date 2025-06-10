<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\InstallmentRequest;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // สร้าง User
        User::create([
            'name' => 'สุรเชษฐ์',
            'surname' => 'หงษ์ทอง',
            'phone' => '0615855949',
            'id_card_number' => '1229900809412',
            'password' => bcrypt('0870303002'),
            'is_admin' => 1,
            'address' => 'กรุงเทพมหานคร',
            'workplace' => 'บริษัทตัวอย่าง',
            'salary' => 50000,
            'identity_verification_status' => 'verified'
        ]);

        // สร้างตัวอย่างข้อมูลการผ่อนสินค้า
        InstallmentRequest::create([
            'user_id' => 1,
            'gold_type' => 'ทองรูปพรรณ',
            'gold_amount' => 10,
            'installment_period' => 10,
            'interest_rate' => 3.5,
            'penalty_rate' => 2,
            'status' => 'approved',
            'admin_notes' => 'อนุมัติเรียบร้อย',
            'due_date' => now()->addMonths(10),
        ]);
    }
}
