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
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->decimal('total_installment_amount', 12, 2)->default(0)->after('approved_gold_price');
            $table->decimal('daily_payment_amount', 12, 2)->default(0)->after('total_installment_amount');
            $table->decimal('first_payment_amount', 12, 2)->default(0)->after('daily_payment_amount');
            $table->integer('payment_interval_days')->default(1)->after('start_date'); // แก้ให้ after('start_date') ที่มีอยู่แล้ว
        });
    }

    public function down(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->dropColumn([
                'total_installment_amount',
                'daily_payment_amount',
                'first_payment_amount',
                'payment_interval_days',
            ]);
        });
    }
};
