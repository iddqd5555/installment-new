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
            if (!Schema::hasColumn('installment_requests', 'fine_rate')) {
                $table->float('fine_rate')->default(1.5);
            }
            if (!Schema::hasColumn('installment_requests', 'next_payment_date')) {
                $table->date('next_payment_date')->nullable();
            }
            if (!Schema::hasColumn('installment_requests', 'total_with_interest')) {
                $table->float('total_with_interest')->default(0);
            }
            if (!Schema::hasColumn('installment_requests', 'admin_notes')) {
                $table->string('admin_notes')->nullable();
            }
            if (!Schema::hasColumn('installment_requests', 'due_date')) {
                $table->date('due_date')->nullable();
            }
            if (!Schema::hasColumn('installment_requests', 'status')) {
                $table->string('status')->default('pending');
            }
        });
    }

    public function down(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $columns = ['fine_rate', 'next_payment_date', 'total_with_interest', 'admin_notes', 'due_date', 'status'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('installment_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
