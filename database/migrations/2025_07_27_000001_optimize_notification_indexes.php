<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OptimizeNotificationIndexes extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['role', 'user_id'], 'role_user_id_idx');
            $table->index('type');
            $table->index('is_read');
        });
    }
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('role_user_id_idx');
            $table->dropIndex(['type']);
            $table->dropIndex(['is_read']);
        });
    }
}
