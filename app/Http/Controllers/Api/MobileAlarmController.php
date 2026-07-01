<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Fault;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MobileAlarmController extends Controller
{
    public function state(Request $request, string $deviceCode)
    {
        $device = Device::where('device_code', $deviceCode)->first();

        if (! $device) {
            return response()->json(['message' => 'Device not found'], Response::HTTP_NOT_FOUND);
        }

        $latestReading = $device->readings()->latest('id')->first();
        $activeFault = $device->faults()
            ->whereNull('resolved_at')
            ->latest('id')
            ->first();
        $recentFaults = $device->faults()
            ->latest('id')
            ->limit(6)
            ->get();

        $offlineAfterSeconds = (int) config('services.smartguard.offline_after_seconds', 10);
        $deviceOnline = $device->last_seen_at !== null
            && $device->last_seen_at->greaterThanOrEqualTo(now()->subSeconds($offlineAfterSeconds));
        $latestReadingHasFault = $latestReading?->fault_status
            && $latestReading->fault_status !== 'RUN';

        $alarmState = 'NORMAL';

        if ($activeFault) {
            $alarmState = $activeFault->acknowledged_at ? 'FAULT_ACTIVE_ACKED' : 'FAULT_ACTIVE_UNACKED';
        } elseif ($latestReadingHasFault) {
            $alarmState = 'FAULT_ACTIVE_UNACKED';
        } elseif (! $deviceOnline) {
            $alarmState = 'OFFLINE';
        }

        return response()->json([
            'device_code' => $device->device_code,
            'device_online' => $deviceOnline,
            'alarm_state' => $alarmState,
            'fault_id' => $activeFault?->id,
            'fault_type' => $activeFault?->fault_type,
            'occurred_at' => $activeFault?->occurred_at?->toIso8601String(),
            'resolved_at' => $activeFault?->resolved_at?->toIso8601String(),
            'acknowledged_at' => $activeFault?->acknowledged_at?->toIso8601String(),
            'relay_status' => (bool) ($latestReading?->relay_status ?? false),
            'last_seen_at' => $device->last_seen_at?->toIso8601String(),
            'recent_faults' => $recentFaults->map(fn (Fault $fault): array => [
                'id' => $fault->id,
                'fault_type' => $fault->fault_type,
                'occurred_at' => $fault->occurred_at?->toIso8601String(),
                'resolved_at' => $fault->resolved_at?->toIso8601String(),
                'acknowledged_at' => $fault->acknowledged_at?->toIso8601String(),
                'is_active' => $fault->resolved_at === null,
            ])->values(),
            'reading' => $latestReading ? [
                'voltage' => (float) $latestReading->voltage,
                'current' => (float) $latestReading->current,
                'real_power' => (float) $latestReading->real_power,
                'power_factor' => (float) $latestReading->power_factor,
                'energy_kwh' => (float) $latestReading->energy_kwh,
                'fault_status' => $latestReading->fault_status,
            ] : null,
        ]);
    }

    public function acknowledge(Request $request, Fault $fault)
    {
        if (! $fault->acknowledged_at) {
            $fault->update(['acknowledged_at' => now()]);
        }

        return response()->json([
            'fault_id' => $fault->id,
            'acknowledged_at' => $fault->fresh()->acknowledged_at?->toIso8601String(),
            'resolved_at' => $fault->fresh()->resolved_at?->toIso8601String(),
        ]);
    }
}
