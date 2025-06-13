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
        Schema::table('installment_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('installment_payments', 'payment_proof')) {
                $table->string('payment_proof')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            if (Schema::hasColumn('installment_payments', 'payment_proof')) {
                $table->dropColumn('payment_proof');
            }
        });
    }
};
