<?php

namespace Database\Seeders;

use App\Models\FaultSetting;
use Illuminate\Database\Seeder;

class FaultSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'parameter' => 'voltage',
                'fault_code' => 'OVERVOLTAGE',
                'min_value' => 185,
                'max_value' => 258,
                'unit' => 'V',
                'enabled' => true,
                'description' => 'Grid voltage operating range',
            ],
            [
                'parameter' => 'current',
                'fault_code' => 'OVERCURRENT',
                'min_value' => 0,
                'max_value' => 5.0,
                'unit' => 'A',
                'enabled' => true,
                'description' => 'Maximum allowable load current',
            ],
            [
                'parameter' => 'power_factor',
                'fault_code' => 'LOW_POWER_FACTOR',
                'min_value' => 0,
                'max_value' => 0,
                'unit' => 'PF',
                'enabled' => false,
                'description' => 'Reserved for future power factor protection',
            ],
            [
                'parameter' => 'real_power',
                'fault_code' => 'OVERLOAD',
                'min_value' => 0,
                'max_value' => 0,
                'unit' => 'W',
                'enabled' => false,
                'description' => 'Reserved for future real power protection',
            ],
            [
                'parameter' => 'apparent_power',
                'fault_code' => 'OVERAPPARENT',
                'min_value' => 0,
                'max_value' => 0,
                'unit' => 'VA',
                'enabled' => false,
                'description' => 'Reserved for future apparent power protection',
            ],
        ];

        foreach ($settings as $setting) {
            FaultSetting::updateOrCreate(
                ['fault_code' => $setting['fault_code']],
                $setting
            );
        }
    }
}
