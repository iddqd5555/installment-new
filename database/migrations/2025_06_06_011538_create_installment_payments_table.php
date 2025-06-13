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
        Schema::create('installment_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_request_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('status')->default('pending');
            $table->string('admin_notes')->nullable();
            $table->string('payment_proof')->nullable();
            $table->date('payment_due_date')->nullable(); // ✅ ตรงนี้ต้องมี
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('installment_payments');
    }

};
