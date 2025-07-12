<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->string('slip_reference')->nullable()->after('slip_hash')->unique();
            $table->json('slip_ocr_json')->nullable()->after('slip_reference');
        });
    }
    public function down()
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->dropColumn(['slip_reference', 'slip_ocr_json']);
        });
    }

};
