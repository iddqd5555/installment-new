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
            $table->string('payment_status')->default('pending')->after('fine');
        });
    }

    public function down()
    {
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }

};
