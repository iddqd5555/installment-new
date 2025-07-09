<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentQrLogsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_qr_logs', function (Blueprint $table) {
            $table->id();
            $table->string('qr_ref')->unique();
            $table->string('amount');
            $table->string('currency')->default('THB');
            $table->string('status')->default('pending');
            $table->string('qr_image')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('customer_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_qr_logs');
    }
}
