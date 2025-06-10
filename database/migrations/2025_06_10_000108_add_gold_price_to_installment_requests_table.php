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
            $table->decimal('approved_gold_price', 10, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->dropColumn('approved_gold_price');
        });
    }
};
