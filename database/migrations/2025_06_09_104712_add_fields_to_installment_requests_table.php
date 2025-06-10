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
            if (!Schema::hasColumn('installment_requests', 'penalty_rate')) {
                $table->float('penalty_rate')->default(2);
            }
        });
    }

    public function down(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            if (Schema::hasColumn('installment_requests', 'penalty_rate')) {
                $table->dropColumn('penalty_rate');
            }
        });
    }
};
