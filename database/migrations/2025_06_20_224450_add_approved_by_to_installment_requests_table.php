<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->constrained('admins')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            $table->dropColumn('approved_by');
        });
    }

};
