<?php

namespace App\Services;

use App\Models\Device;
use App\Models\FaultSetting;
use Illuminate\Database\Eloquent\Collection;

class FaultSettingService
{
    /**
     * Get all fault settings.
     */
    public function getAllSettings(): Collection
    {
        return FaultSetting::all();
    }

    /**
     * Get a specific fault setting by ID.
     */
    public function getSettingById(int $id): ?FaultSetting
    {
        return FaultSetting::find($id);
    }

    /**
     * Update a fault setting.
     */
    public function updateSetting(FaultSetting $setting, array $data): FaultSetting
    {
        $setting->update($data);
        Device::query()->update([
            'threshold_config_status' => 'pending',
            'threshold_config_error' => null,
        ]);

        return $setting;
    }
}
