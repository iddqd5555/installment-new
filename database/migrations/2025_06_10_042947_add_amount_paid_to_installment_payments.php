<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('installment_payments', 'amount_paid')) {
                $table->decimal('amount_paid', 10, 2)->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            if (Schema::hasColumn('installment_payments', 'amount_paid')) {
                $table->dropColumn('amount_paid');
            }
        });
    }

};
