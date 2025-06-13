<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_gold_prices', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique(); // วันที่ (ไม่ซ้ำกัน)
            $table->decimal('buy', 10, 2);   // ราคาทองรูปพรรณ รับซื้อ
            $table->decimal('sell', 10, 2);  // ราคาทองรูปพรรณ ขายออก
            $table->timestamps();            // เวลาสร้างและอัพเดตข้อมูล
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_gold_prices');
    }
};
