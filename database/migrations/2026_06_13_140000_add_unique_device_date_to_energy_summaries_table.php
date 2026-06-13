<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('energy_summaries')
            ->select('device_id', 'summary_date')
            ->groupBy('device_id', 'summary_date')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('device_id')
            ->orderBy('summary_date')
            ->get()
            ->each(function ($duplicate): void {
                $rows = DB::table('energy_summaries')
                    ->where('device_id', $duplicate->device_id)
                    ->where('summary_date', $duplicate->summary_date)
                    ->orderBy('id')
                    ->get();
                $retainedRow = $rows->first();

                DB::table('energy_summaries')
                    ->where('id', $retainedRow->id)
                    ->update([
                        'daily_kwh' => $rows->sum('daily_kwh'),
                        'monthly_kwh' => $rows->max('monthly_kwh'),
                        'updated_at' => now(),
                    ]);

                DB::table('energy_summaries')
                    ->whereIn('id', $rows->skip(1)->pluck('id'))
                    ->delete();
            });

        Schema::table('energy_summaries', function (Blueprint $table) {
            $table->unique(
                ['device_id', 'summary_date'],
                'energy_summaries_device_date_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('energy_summaries', function (Blueprint $table) {
            $table->dropUnique('energy_summaries_device_date_unique');
        });
    }
};
