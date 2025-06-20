<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('installment_requests', 'status')) {
                $table->string('status')->default('pending');
            }

            if (!Schema::hasColumn('installment_requests', 'admin_message')) {
                $table->text('admin_message')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('installment_requests', function (Blueprint $table) {
            if (Schema::hasColumn('installment_requests', 'admin_message')) {
                $table->dropColumn('admin_message');
            }
            if (Schema::hasColumn('installment_requests', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

