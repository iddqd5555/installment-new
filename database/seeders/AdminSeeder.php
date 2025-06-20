<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admins')->insert([
            [
                'prefix' => 'WISDOMGOLD',
                'username' => 'oaa', // ผู้บริหารหรือผู้ดูแลระบบสูงสุด
                'password' => Hash::make('0870303002'),
                'role' => 'OAA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prefix' => 'WISDOMGOLD',
                'username' => 'admin',
                'password' => Hash::make('0870303002'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'prefix' => 'WISDOMGOLD',
                'username' => 'staff',
                'password' => Hash::make('0870303002'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
