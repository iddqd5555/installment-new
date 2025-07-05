<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->string('contract_number')->nullable();
            $table->string('payment_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->dropColumn(['contract_number', 'payment_number']);
        });
    }
};
