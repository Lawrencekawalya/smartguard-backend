<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceReading;
use App\Models\Fault;
use App\Models\RelayLog;
use Illuminate\Support\Facades\DB;

class TelemetryService
{
    /**
     * Store telemetry data and handle side effects.
     */
    public function storeTelemetry(array $data): DeviceReading
    {
        return DB::transaction(function () use ($data) {
            // 1. Device Auto Registration
            $device = Device::firstOrCreate(
                ['device_code' => $data['device_code']],
                ['device_name' => 'SmartGuard Unit ' . (Device::count() + 1)]
            );

            // 2. Get previous reading for transition detection
            $previousReading = $device->readings()->latest('id')->first();

            // 3. Relay Transition Detection
            if ($previousReading && (int)$previousReading->relay_status !== (int)$data['relay_status']) {
                $action = (int)$data['relay_status'] === 1 ? 'ON' : 'OFF';
                
                // Check if it was an AUTO_TRIP
                if ($action === 'OFF' && $data['status'] === 'TRIP') {
                    $action = 'AUTO_TRIP';
                }

                $device->relayLogs()->create([
                    'action' => $action,
                    'triggered_by' => $data['status'] === 'TRIP' ? 'HARDWARE_ENGINE' : 'REMOTE_COMMAND',
                    'created_at' => now(),
                ]);
            }

            // 4. Fault Lifecycle Management
            if ($data['status'] === 'TRIP') {
                $existingFault = $device->faults()
                    ->where('fault_type', $data['fault_reason'])
                    ->whereNull('resolved_at')
                    ->first();

                if (!$existingFault) {
                    $device->faults()->create([
                        'fault_type' => $data['fault_reason'],
                        'occurred_at' => now(),
                    ]);
                }
            } elseif ($data['status'] === 'RUN') {
                $device->faults()
                    ->whereNull('resolved_at')
                    ->update(['resolved_at' => now()]);
            }

            // 5. Telemetry Persistence
            $reading = $device->readings()->create([
                'voltage' => $data['voltage'],
                'current' => $data['current'],
                'real_power' => $data['real_power'],
                'apparent_power' => $data['apparent_power'],
                'power_factor' => $data['power_factor'],
                'energy_kwh' => $data['energy_kwh'],
                'relay_status' => (bool)$data['relay_status'],
                'fault_status' => $data['status'],
                'created_at' => now(),
            ]);

            // 6. Update Device last_seen_at
            $device->update(['last_seen_at' => now()]);

            return $reading;
        });
    }

    /**
     * Get the latest telemetry for a device code.
     */
    public function getLatestTelemetry(string $deviceCode): ?DeviceReading
    {
        $device = Device::where('device_code', $deviceCode)->first();

        if (!$device) {
            return null;
        }

        return $device->readings()->latest('id')->first();
    }
}
