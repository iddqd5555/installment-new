<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_accounts', 'is_active')) {
                $table->boolean('is_active')->default(1)->after('logo');
            }
            if (!Schema::hasColumn('bank_accounts', 'is_default')) {
                $table->boolean('is_default')->default(0)->after('is_active');
            }
        });
    }

    public function down()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('bank_accounts', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('bank_accounts', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });
    }
};
