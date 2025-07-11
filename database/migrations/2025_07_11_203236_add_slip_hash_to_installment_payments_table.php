<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlipHashToInstallmentPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->string('slip_path')->nullable()->after('id');
            $table->text('slip_qr_text')->nullable()->after('slip_path');
            $table->string('slip_hash', 64)->nullable()->after('slip_qr_text');
        });
    }

    public function down()
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->dropColumn('slip_path');
            $table->dropColumn('slip_qr_text');
            $table->dropColumn('slip_hash');
        });
    }
}
