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
        Schema::create('fault_settings', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('parameter');
            $blueprint->string('fault_code');
            $blueprint->decimal('min_value', 15, 6);
            $blueprint->decimal('max_value', 15, 6);
            $blueprint->string('unit');
            $blueprint->boolean('enabled')->default(true);
            $blueprint->text('description')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fault_settings');
    }
};
