<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlipReferenceAndSlipOcrJsonToInstallmentPayments extends Migration
{
    public function up()
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->string('slip_reference')->nullable()->unique()->after('slip_hash');
            $table->json('slip_ocr_json')->nullable()->after('slip_reference');
        });
    }

    public function down()
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->dropUnique(['slip_reference']);
            $table->dropColumn(['slip_reference', 'slip_ocr_json']);
        });
    }
}
