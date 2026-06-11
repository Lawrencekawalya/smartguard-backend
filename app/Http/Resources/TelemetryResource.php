<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TelemetryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'device_id' => $this->device_id,
            'device_code' => $this->device->device_code,
            'voltage' => (float) $this->voltage,
            'current' => (float) $this->current,
            'real_power' => (float) $this->real_power,
            'apparent_power' => (float) $this->apparent_power,
            'power_factor' => (float) $this->power_factor,
            'energy_kwh' => (float) $this->energy_kwh,
            'relay_status' => (bool) $this->relay_status,
            'fault_status' => $this->fault_status,
            'created_at' => $this->created_at,
        ];
    }
}
