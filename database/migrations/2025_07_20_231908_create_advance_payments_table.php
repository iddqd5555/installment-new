<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvancePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('advance_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('installment_request_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 10, 2);
            $table->string('slip_image')->nullable();
            $table->string('slip_hash')->nullable();
            $table->string('slip_reference')->nullable();
            $table->json('slip_ocr_json')->nullable();
            $table->timestamps();

            $table->foreign('installment_request_id')
                  ->references('id')->on('installment_requests')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('advance_payments');
    }
}
