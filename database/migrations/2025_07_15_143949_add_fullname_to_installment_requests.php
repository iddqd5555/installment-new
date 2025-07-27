<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            // เพิ่มฟิลด์ fullname (nullable, สำหรับ guest)
            if (!Schema::hasColumn('installment_requests', 'fullname')) {
                $table->string('fullname')->nullable()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            if (Schema::hasColumn('installment_requests', 'fullname')) {
                $table->dropColumn('fullname');
            }
        });
    }
};
