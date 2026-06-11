<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceReading;
use App\Models\Fault;
use App\Models\RelayLog;
use App\Models\EnergySummary;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Get the dashboard status for a device.
     */
    public function getStatus(string $deviceCode): ?Device
    {
        return Device::where('device_code', $deviceCode)->first();
    }

    /**
     * Get the latest reading for a device.
     */
    public function getLatestReading(string $deviceCode): ?DeviceReading
    {
        $device = Device::where('device_code', $deviceCode)->first();
        return $device ? $device->readings()->latest('id')->first() : null;
    }

    /**
     * Get the latest fault for a device.
     */
    public function getLatestFault(string $deviceCode): ?Fault
    {
        $device = Device::where('device_code', $deviceCode)->first();
        return $device ? $device->faults()->latest('id')->first() : null;
    }

    /**
     * Get fault history for a device.
     */
    public function getFaultHistory(string $deviceCode, int $limit = 20): Collection
    {
        $device = Device::where('device_code', $deviceCode)->first();
        return $device ? $device->faults()->latest('id')->limit($limit)->get() : collect();
    }

    /**
     * Get relay history for a device.
     */
    public function getRelayHistory(string $deviceCode, int $limit = 20): Collection
    {
        $device = Device::where('device_code', $deviceCode)->first();
        return $device ? $device->relayLogs()->latest('id')->limit($limit)->get() : collect();
    }

    /**
     * Get daily energy usage for a device.
     */
    public function getDailyUsage(string $deviceCode): Collection
    {
        $device = Device::where('device_code', $deviceCode)->first();
        return $device ? $device->energySummaries()
            ->select('summary_date as date', 'daily_kwh')
            ->orderBy('summary_date', 'desc')
            ->limit(30)
            ->get() : collect();
    }

    /**
     * Get monthly energy usage for a device.
     */
    public function getMonthlyUsage(string $deviceCode): Collection
    {
        $device = Device::where('device_code', $deviceCode)->first();
        if (!$device) return collect();

        $format = config('database.default') === 'sqlite' 
            ? "strftime('%Y-%m', summary_date)" 
            : "DATE_FORMAT(summary_date, '%Y-%m')";

        return $device->energySummaries()
            ->selectRaw("$format as month, MAX(monthly_kwh) as monthly_kwh")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();
    }
}
