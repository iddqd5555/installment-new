<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
            Schema::create('installment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('fullname')->nullable();
            $table->string('phone')->nullable();
            $table->string('gold_type')->nullable();
            $table->integer('gold_amount')->nullable();
            $table->integer('installment_period')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_requests');
    }
};
