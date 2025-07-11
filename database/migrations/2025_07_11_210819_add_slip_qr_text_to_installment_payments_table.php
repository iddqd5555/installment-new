<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlipQrTextToInstallmentPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->text('slip_qr_text')->nullable()->after('slip_hash');
        });
    }

    public function down()
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->dropColumn('slip_qr_text');
        });
    }
}
