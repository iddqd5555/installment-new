<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('installment_payments', 'slip_reference')) {
                $table->string('slip_reference')->nullable()->after('slip_hash');
            }
            if (!Schema::hasColumn('installment_payments', 'slip_ocr_json')) {
                $table->text('slip_ocr_json')->nullable()->after('slip_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            if (Schema::hasColumn('installment_payments', 'slip_reference')) {
                $table->dropColumn('slip_reference');
            }
            if (Schema::hasColumn('installment_payments', 'slip_ocr_json')) {
                $table->dropColumn('slip_ocr_json');
            }
        });
    }
};
