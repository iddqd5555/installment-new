<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {

            if (!Schema::hasColumn('installment_requests', 'total_paid')) {
                $table->decimal('total_paid', 12, 2)->default(0)->after('total_with_interest');
            }

            if (!Schema::hasColumn('installment_requests', 'remaining_amount')) {
                $table->decimal('remaining_amount', 12, 2)->default(0)->after('total_paid');
            }

            if (!Schema::hasColumn('installment_requests', 'remaining_months')) {
                $table->integer('remaining_months')->default(0)->after('installment_period');
            }

            if (!Schema::hasColumn('installment_requests', 'due_date')) {
                $table->date('due_date')->nullable()->after('next_payment_date');
            }

            if (!Schema::hasColumn('installment_requests', 'last_month_reduced')) {
                $table->timestamp('last_month_reduced')->nullable()->after('due_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->dropColumn([
                'total_paid',
                'remaining_amount',
                'remaining_months',
                'due_date',
                'last_month_reduced'
            ]);
        });
    }
};