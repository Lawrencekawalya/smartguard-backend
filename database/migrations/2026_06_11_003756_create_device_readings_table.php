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
        Schema::create('device_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->decimal('voltage', 8, 2);
            $table->decimal('current', 8, 3);
            $table->decimal('real_power', 10, 2);
            $table->decimal('apparent_power', 10, 2);
            $table->decimal('power_factor', 4, 2);
            $table->decimal('energy_kwh', 15, 6);
            $table->boolean('relay_status');
            $table->string('fault_status');
            $table->timestamp('created_at')->nullable();

            $table->index('device_id');
            $table->index('created_at');
            $table->index(['device_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_readings');
    }
};
