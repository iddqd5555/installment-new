<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->string('product_name')->nullable();
            $table->decimal('product_price', 12, 2)->default(0);
            $table->integer('installment_months')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'product_price', 'installment_months']);
        });
    }

};
