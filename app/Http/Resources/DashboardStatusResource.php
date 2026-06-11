<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $latestReading = $this->readings()->latest('id')->first();

        return [
            'device_code' => $this->device_code,
            'status' => $latestReading?->fault_status ?? 'UNKNOWN',
            'relay_status' => (bool) ($latestReading?->relay_status ?? false),
            'fault_status' => $latestReading?->fault_status ?? 'UNKNOWN',
            'last_seen_at' => $this->last_seen_at?->toIso8601String(),
        ];
    }
}
