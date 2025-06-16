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
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'first_name');
            $table->renameColumn('surname', 'last_name');

            $table->string('email')->unique()->after('phone');
            $table->date('date_of_birth')->nullable()->after('address');
            $table->string('gender', 10)->nullable()->after('date_of_birth');

            $table->string('bank_name')->nullable()->after('salary');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account_number');

            $table->string('slip_salary_image')->nullable()->after('id_card_image');
            $table->json('additional_documents')->nullable()->after('slip_salary_image');

            $table->string('role')->default('customer')->after('is_admin');
            $table->string('status')->default('active')->after('role');

            $table->boolean('two_factor_enabled')->default(false)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_enabled');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('first_name', 'name');
            $table->renameColumn('last_name', 'surname');

            $table->dropColumn([
                'email',
                'date_of_birth',
                'gender',
                'bank_name',
                'bank_account_number',
                'bank_account_name',
                'slip_salary_image',
                'additional_documents',
                'role',
                'status',
                'two_factor_enabled',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }

};
