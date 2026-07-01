<?php

namespace App\Services;

use App\Models\Device;
use App\Models\FaultSetting;
use Illuminate\Support\Collection;

class ThresholdConfigService
{
    public function getVersion(): int
    {
        $latestUpdatedAt = FaultSetting::query()->max('updated_at');

        return $latestUpdatedAt ? strtotime((string) $latestUpdatedAt) : 1;
    }

    /**
     * @return array<string, float|int>
     */
    public function getThresholds(): array
    {
        $settings = FaultSetting::query()
            ->get()
            ->keyBy('parameter');

        return [
            'version' => $this->getVersion(),
            'max_current' => $this->maxValue($settings, 'current', 5.0),
            'min_voltage' => $this->minValue($settings, 'voltage', 185.0),
            'max_voltage' => $this->maxValue($settings, 'voltage', 258.0),
            'min_power_factor' => $this->minValue($settings, 'power_factor', 0.0),
            'max_real_power' => $this->maxValue($settings, 'real_power', 0.0),
            'max_apparent_power' => $this->maxValue($settings, 'apparent_power', 0.0),
        ];
    }

    public function getConfigFrame(Device $device): string
    {
        $thresholds = $this->getThresholds();

        $device->update([
            'threshold_config_version' => $thresholds['version'],
            'threshold_config_status' => $device->threshold_config_ack_version === $thresholds['version'] ? 'synced' : 'pending',
            'threshold_config_error' => null,
        ]);

        return sprintf(
            'CFG,%d,%.3f,%.1f,%.1f,%.2f,%.1f,%.1f',
            $thresholds['version'],
            $thresholds['max_current'],
            $thresholds['min_voltage'],
            $thresholds['max_voltage'],
            $thresholds['min_power_factor'],
            $thresholds['max_real_power'],
            $thresholds['max_apparent_power'],
        );
    }

    public function recordAck(Device $device, int $version, string $status, ?string $message = null): Device
    {
        $device->update([
            'threshold_config_ack_version' => $status === 'ACK' ? $version : $device->threshold_config_ack_version,
            'threshold_config_status' => $status === 'ACK' ? 'synced' : 'failed',
            'threshold_config_error' => $status === 'ACK' ? null : $message,
            'threshold_config_synced_at' => now(),
        ]);

        return $device->fresh();
    }

    /**
     * @param  Collection<string, FaultSetting>  $settings
     */
    private function minValue(Collection $settings, string $parameter, float $fallback): float
    {
        $setting = $settings->get($parameter);

        if (! $setting || ! $setting->enabled) {
            return $fallback;
        }

        return (float) $setting->min_value;
    }

    /**
     * @param  Collection<string, FaultSetting>  $settings
     */
    private function maxValue(Collection $settings, string $parameter, float $fallback): float
    {
        $setting = $settings->get($parameter);

        if (! $setting || ! $setting->enabled) {
            return $fallback;
        }

        return (float) $setting->max_value;
    }
}
