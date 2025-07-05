<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->decimal('daily_penalty', 10, 2)->default(100); // ค่าปรับรายวัน
            $table->decimal('total_penalty', 12, 2)->default(0);  // ค่าปรับสะสมทั้งหมด
            $table->date('first_approved_date')->nullable();      // วันที่อนุมัติครั้งแรก
        });
    }

    public function down()
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->dropColumn(['daily_penalty', 'total_penalty', 'first_approved_date']);
        });
    }

};
