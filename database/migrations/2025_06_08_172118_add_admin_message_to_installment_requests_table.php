<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->text('admin_message')->nullable()->after('status'); 
            // ข้อความจากแอดมิน (อนุญาตให้เป็น null)
        });
    }

    public function down(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->dropColumn('admin_message');
        });
    }
};

