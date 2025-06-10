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
            $table->float('interest_rate')->default(3.5);
        });
    }

    public function down()
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->dropColumn('interest_rate');
        });
    }
};
