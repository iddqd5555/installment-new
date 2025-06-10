<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            if (Schema::hasColumn('installment_requests', 'product_price')) {
                $table->decimal('product_price', 10, 2)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            if (Schema::hasColumn('installment_requests', 'id_card_image')) {
                $table->string('id_card_image')->nullable(false)->change();
            }
            if (Schema::hasColumn('installment_requests', 'product_price')) {
                $table->decimal('product_price', 10, 2)->nullable(false)->change();
            }
        });
    }
};
