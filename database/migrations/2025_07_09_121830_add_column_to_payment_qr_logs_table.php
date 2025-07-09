<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_qr_logs', function (Blueprint $table) {
            // เพิ่มคอลัมน์ใหม่ (ถ้ายังไม่มี)
            if (!Schema::hasColumn('payment_qr_logs', 'installment_payment_id')) {
                $table->unsignedBigInteger('installment_payment_id')->nullable()->after('transaction_id');
                $table->foreign('installment_payment_id')
                      ->references('id')->on('installment_payments')
                      ->onDelete('set null');
            }
            if (!Schema::hasColumn('payment_qr_logs', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('installment_payment_id');
            }
        });
    }

    public function down()
    {
        Schema::table('payment_qr_logs', function (Blueprint $table) {
            if (Schema::hasColumn('payment_qr_logs', 'installment_payment_id')) {
                $table->dropForeign(['installment_payment_id']);
                $table->dropColumn('installment_payment_id');
            }
            if (Schema::hasColumn('payment_qr_logs', 'customer_id')) {
                $table->dropColumn('customer_id');
            }
        });
    }
};
