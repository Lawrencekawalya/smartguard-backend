<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\DeviceReading;
use App\Models\EnergySummary;
use App\Models\Fault;
use App\Models\RelayLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure a login user exists
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Create a main device
        $device = Device::updateOrCreate(
            ['device_code' => 'SmartGuard-MTR-001'],
            [
                'device_name' => 'Main SmartGuard Unit',
                'location' => 'Main Distribution Board',
                'status' => 'active',
                'firmware_version' => '1.0.4',
                'ip_address' => '192.168.1.50',
                'last_seen_at' => now(),
            ],
        );

        // 3. Generate data for the last 30 days
        $start = Carbon::now()->subDays(30);
        $totalKwh = 1500.0;

        for ($i = 0; $i <= 30; $i++) {
            $date = $start->copy()->addDays($i);
            $dailyKwh = rand(10, 25) + (rand(0, 99) / 100);
            $totalKwh += $dailyKwh;

            // Daily Energy Summary
            EnergySummary::create([
                'device_id' => $device->id,
                'summary_date' => $date->toDateString(),
                'daily_kwh' => $dailyKwh,
                'monthly_kwh' => $totalKwh,
            ]);

            // Sample Readings for the day (one every few hours for "Peak" detection)
            for ($h = 0; $h < 24; $h += 4) {
                DeviceReading::create([
                    'device_id' => $device->id,
                    'voltage' => rand(220, 245),
                    'current' => rand(2, 12) + (rand(0, 9) / 10),
                    'real_power' => rand(500, 2500),
                    'apparent_power' => rand(550, 2600),
                    'power_factor' => 0.9 + (rand(0, 9) / 100),
                    'energy_kwh' => $totalKwh,
                    'relay_status' => true,
                    'fault_status' => 'RUN',
                    'created_at' => $date->copy()->setHour($h),
                ]);
            }
        }

        // 4. Add some faults
        Fault::create([
            'device_id' => $device->id,
            'fault_type' => 'OVERVOLTAGE SURGE',
            'description' => 'Grid voltage spiked to 258V',
            'occurred_at' => now()->subDays(5)->setHour(14),
            'resolved_at' => now()->subDays(5)->setHour(14)->addMinutes(15),
        ]);

        Fault::create([
            'device_id' => $device->id,
            'fault_type' => 'OVERCURRENT DETECTED',
            'description' => 'Load exceeded 15A threshold',
            'occurred_at' => now()->subDays(2)->setHour(18),
            'resolved_at' => now()->subDays(2)->setHour(18)->addMinutes(5),
        ]);

        // 5. Add relay logs
        RelayLog::create([
            'device_id' => $device->id,
            'action' => 'AUTO_TRIP',
            'triggered_by' => 'HARDWARE_ENGINE',
            'created_at' => now()->subDays(2)->setHour(18),
        ]);

        RelayLog::create([
            'device_id' => $device->id,
            'action' => 'MANUAL_RESET',
            'triggered_by' => 'USER_DASHBOARD',
            'created_at' => now()->subDays(2)->setHour(18)->addMinutes(5),
        ]);
    }
}
