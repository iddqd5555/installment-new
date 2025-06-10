<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'id_card_number')) {
                $table->string('id_card_number')->nullable();
            }

            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable();
            }

            if (!Schema::hasColumn('users', 'workplace')) {
                $table->string('workplace')->nullable();
            }

            if (!Schema::hasColumn('users', 'salary')) {
                $table->decimal('salary', 10, 2)->nullable();
            }

            if (!Schema::hasColumn('users', 'identity_verification_status')) {
                $table->string('identity_verification_status')->default('pending');
            }

            if (!Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'id_card_number', 
                'address', 
                'workplace', 
                'salary', 
                'identity_verification_status', 
                'phone_verified_at'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

