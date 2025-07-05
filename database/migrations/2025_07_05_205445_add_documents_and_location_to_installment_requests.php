<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->string('document_image')->nullable();
            $table->double('latitude', 10, 7)->nullable();
            $table->double('longitude', 10, 7)->nullable();
        });
    }

    public function down()
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->dropColumn(['document_image', 'latitude', 'longitude']);
        });
    }

};
