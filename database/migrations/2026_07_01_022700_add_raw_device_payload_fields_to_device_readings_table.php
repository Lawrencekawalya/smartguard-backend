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
        Schema::table('device_readings', function (Blueprint $table) {
            $table->string('device_status')->nullable()->after('relay_status');
            $table->string('fault_reason')->nullable()->after('device_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_readings', function (Blueprint $table) {
            $table->dropColumn(['device_status', 'fault_reason']);
        });
    }
};
