<?php

namespace Database\Seeders;

use App\Models\EnergySetting;
use Illuminate\Database\Seeder;

class EnergySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EnergySetting::firstOrCreate([], [
            'tariff_rate' => 805,
            'currency' => 'UGX',
            'description' => 'UMEME Residential Tariff',
        ]);
    }
}
