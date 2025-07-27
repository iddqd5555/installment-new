<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nickname')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('relationship_with_buyer')->nullable();
            $table->string('house_number')->nullable();
            $table->string('line_id')->nullable();
            $table->string('facebook')->nullable();
            $table->string('workplace_address')->nullable();
            $table->string('position')->nullable();
            $table->string('work_duration')->nullable();
            $table->string('spouse_name')->nullable();
            $table->string('spouse_phone')->nullable();
            $table->string('work_phone')->nullable();
            $table->decimal('daily_income', 10, 2)->nullable();
            $table->decimal('daily_balance', 10, 2)->nullable();
            $table->string('partner_name')->nullable();
            $table->string('partner_phone')->nullable();
            $table->string('partner_occupation')->nullable();
            $table->decimal('partner_salary', 10, 2)->nullable();
            $table->string('emergency_contact_name_1')->nullable();
            $table->string('emergency_contact_relation_1')->nullable();
            $table->string('emergency_contact_address_1')->nullable();
            $table->string('emergency_contact_phone_1')->nullable();
            $table->string('emergency_contact_name_2')->nullable();
            $table->string('emergency_contact_relation_2')->nullable();
            $table->string('emergency_contact_address_2')->nullable();
            $table->string('emergency_contact_phone_2')->nullable();
            $table->string('residence_status')->nullable();
            $table->string('occupation')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nickname', 'marital_status', 'relationship_with_buyer', 'house_number',
                'line_id', 'facebook', 'workplace_address', 'position', 'work_duration',
                'spouse_name', 'spouse_phone', 'work_phone', 'daily_income', 'daily_balance',
                'partner_name', 'partner_phone', 'partner_occupation', 'partner_salary',
                'emergency_contact_name_1', 'emergency_contact_relation_1', 'emergency_contact_address_1', 'emergency_contact_phone_1',
                'emergency_contact_name_2', 'emergency_contact_relation_2', 'emergency_contact_address_2', 'emergency_contact_phone_2',
                'residence_status', 'occupation'
            ]);
        });
    }
};
