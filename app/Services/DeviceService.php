<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;

class DeviceService
{
    /**
     * Get all devices.
     */
    public function getAllDevices(): Collection
    {
        return Device::all();
    }

    /**
     * Get a device by ID.
     */
    public function getDeviceById(int $id): ?Device
    {
        return Device::find($id);
    }

    /**
     * Create a new device.
     */
    public function createDevice(array $data): Device
    {
        return Device::create($data);
    }

    /**
     * Update an existing device.
     */
    public function updateDevice(Device $device, array $data): Device
    {
        $device->update($data);
        return $device;
    }

    /**
     * Delete a device.
     */
    public function deleteDevice(Device $device): bool
    {
        return $device->delete();
    }
}
