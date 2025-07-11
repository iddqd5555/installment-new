<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete(); //อ้างถึงพนักงานที่รับผิดชอบ
            $table->decimal('total_collected', 12, 2); // ยอดเก็บเงินทั้งหมด
            $table->decimal('commission_rate', 5, 2)->default(3.00); // อัตราคอมมิชชัน (%)
            $table->decimal('commission_amount', 12, 2); // จำนวนเงินคอมมิชชัน
            $table->date('calculation_date'); // วันที่คำนวณ
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
