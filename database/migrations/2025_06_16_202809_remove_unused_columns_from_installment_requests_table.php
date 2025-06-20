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
            if (Schema::hasColumn('installment_requests', 'remaining_months')) {
                $table->dropColumn('remaining_months');
            }
            if (Schema::hasColumn('installment_requests', 'last_month_reduced')) {
                $table->dropColumn('last_month_reduced');
            }
        });
    }

    public function down(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->integer('remaining_months')->default(0);
            $table->timestamp('last_month_reduced')->nullable();
        });
    }

};
