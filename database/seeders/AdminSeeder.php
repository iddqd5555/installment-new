<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'prefix' => 'WISDOMGOLD',
            'username' => 'admin',
            'password' => bcrypt('1234'),
            'role' => 'approver',
        ]);
    }
}
