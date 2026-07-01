<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceReading;
use App\Models\Fault;
use App\Models\FaultSetting;
use Illuminate\Support\Facades\DB;

class TelemetryService
{
    public function __construct(
        private readonly EnergySummaryService $energySummaryService
    ) {}

    /**
     * Store telemetry data and handle side effects.
     */
    public function storeTelemetry(array $data): DeviceReading
    {
        return DB::transaction(function () use ($data) {
            // 1. Device Auto Registration
            $device = Device::firstOrCreate(
                ['device_code' => $data['device_code']],
                ['device_name' => 'SmartGuard Unit '.(Device::count() + 1)]
            );
            $device = Device::query()
                ->whereKey($device->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Get previous reading for transition detection
            $previousReading = $device->readings()->latest('id')->first();

            // 3. Relay Transition Detection
            if ($previousReading && (int) $previousReading->relay_status !== (int) $data['relay_status']) {
                $action = (int) $data['relay_status'] === 1 ? 'ON' : 'OFF';

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

            // 4. Backend Threshold Validation & Fault Lifecycle Management
            $detectedFaults = [];
            $isResetRecoveryPacket = $data['status'] === 'RUN' && $data['fault_reason'] === 'RESET_SUCCESS';

            if ($data['status'] === 'TRIP') {
                $detectedFaults[] = $data['fault_reason'];
            }

            // Reset recovery packets are control/status acknowledgements, not electrical samples.
            if (! $isResetRecoveryPacket) {
                // Check database-driven thresholds
                $settings = FaultSetting::where('enabled', true)->get();

                foreach ($settings as $setting) {
                    switch ($setting->parameter) {
                        case 'voltage':
                            if ($data['voltage'] > $setting->max_value) {
                                $detectedFaults[] = 'OVERVOLTAGE SURGE';
                            } elseif ($data['voltage'] < $setting->min_value) {
                                $detectedFaults[] = 'UNDERVOLTAGE BROWNOUT';
                            }
                            break;
                        case 'current':
                            if ($data['current'] > $setting->max_value) {
                                $detectedFaults[] = 'OVERCURRENT DETECTED';
                            }
                            break;
                        case 'power_factor':
                            if ($setting->min_value > 0 && $data['power_factor'] < $setting->min_value) {
                                $detectedFaults[] = 'LOW_POWER_FACTOR';
                            }
                            break;
                        case 'real_power':
                            if ($setting->max_value > 0 && $data['real_power'] > $setting->max_value) {
                                $detectedFaults[] = 'OVERLOAD';
                            }
                            break;
                        case 'apparent_power':
                            if ($setting->max_value > 0 && $data['apparent_power'] > $setting->max_value) {
                                $detectedFaults[] = 'OVERAPPARENT';
                            }
                            break;
                    }
                }
            }

            $detectedFaults = array_unique($detectedFaults);

            if (! empty($detectedFaults)) {
                foreach ($detectedFaults as $faultType) {
                    if ($faultType === 'NONE') {
                        continue;
                    }

                    $existingFault = $device->faults()
                        ->where('fault_type', $faultType)
                        ->whereNull('resolved_at')
                        ->first();

                    if (! $existingFault) {
                        $device->faults()->create([
                            'fault_type' => $faultType,
                            'occurred_at' => now(),
                        ]);
                    }
                }
            } elseif ($data['status'] === 'RUN') {
                // If status is RUN and no backend faults detected, resolve all open incidents
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
                'relay_status' => (bool) $data['relay_status'],
                'device_status' => $data['status'],
                'fault_reason' => $data['fault_reason'],
                'fault_status' => empty($detectedFaults) ? 'RUN' : ($data['status'] === 'TRIP' ? 'TRIP' : 'FAULT_DETECTED'),
                'created_at' => now(),
            ]);

            // 6. Keep analytics summaries synchronized with cumulative meter readings.
            $this->energySummaryService->recordReading($device, $reading, $previousReading);

            // 7. Update Device last_seen_at
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

        if (! $device) {
            return null;
        }

        return $device->readings()->latest('id')->first();
    }
}
