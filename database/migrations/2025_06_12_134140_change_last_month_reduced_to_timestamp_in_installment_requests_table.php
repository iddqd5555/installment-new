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
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->timestamp('last_month_reduced')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->string('last_month_reduced')->nullable()->change();
        });
    }

};
