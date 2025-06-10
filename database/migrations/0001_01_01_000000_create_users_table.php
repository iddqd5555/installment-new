<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('id_card_number')->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->rememberToken();

            // เพิ่มใหม่ชัดเจนตามความต้องการของระบบ
            $table->string('id_card_image')->nullable();
            $table->string('house_registration_image')->nullable();
            $table->string('business_registration_image')->nullable();
            $table->string('bank_statement_image')->nullable();
            $table->string('bank_account_image')->nullable();
            $table->string('staff_reference')->nullable();
            $table->string('identity_verification_status')->default('pending');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};

