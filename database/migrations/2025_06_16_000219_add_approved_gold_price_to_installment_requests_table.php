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
            if (!Schema::hasColumn('installment_requests', 'approved_gold_price')) {
                $table->decimal('approved_gold_price', 12, 2)->default(0)->after('gold_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            if (Schema::hasColumn('installment_requests', 'approved_gold_price')) {
                $table->dropColumn('approved_gold_price');
            }
        });
    }
};
