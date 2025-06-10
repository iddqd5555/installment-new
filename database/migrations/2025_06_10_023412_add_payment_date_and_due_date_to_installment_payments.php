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
        Schema::table('installment_payments', function (Blueprint $table) {
            // due_date มีอยู่แล้ว ไม่ต้องเพิ่มอีก
            $table->date('payment_date')->nullable()->after('due_date'); 
        });
    }

    public function down()
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->dropColumn('payment_date');
        });
    }
};
