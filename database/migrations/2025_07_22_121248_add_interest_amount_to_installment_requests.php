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
            $table->decimal('interest_amount', 12, 2)->default(0)->after('total_with_interest');
        });
    }
    public function down()
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->dropColumn('interest_amount');
        });
    }
};
