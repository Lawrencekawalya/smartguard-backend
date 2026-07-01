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
        Schema::table('devices', function (Blueprint $table) {
            $table->unsignedBigInteger('threshold_config_version')->nullable()->after('last_seen_at');
            $table->unsignedBigInteger('threshold_config_ack_version')->nullable()->after('threshold_config_version');
            $table->string('threshold_config_status')->default('pending')->after('threshold_config_ack_version');
            $table->text('threshold_config_error')->nullable()->after('threshold_config_status');
            $table->timestamp('threshold_config_synced_at')->nullable()->after('threshold_config_error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'threshold_config_version',
                'threshold_config_ack_version',
                'threshold_config_status',
                'threshold_config_error',
                'threshold_config_synced_at',
            ]);
        });
    }
};
