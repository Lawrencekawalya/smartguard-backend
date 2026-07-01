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
        $offlineAfterSeconds = (int) config('services.smartguard.offline_after_seconds', 10);
        $isOnline = $this->last_seen_at !== null
            && $this->last_seen_at->greaterThanOrEqualTo(now()->subSeconds($offlineAfterSeconds));
        $faultStatus = $latestReading?->fault_status ?? 'UNKNOWN';
        $latestReadingHasFault = $latestReading?->fault_status
            && $latestReading->fault_status !== 'RUN';

        return [
            'device_code' => $this->device_code,
            'status' => $latestReadingHasFault ? $faultStatus : ($isOnline ? $faultStatus : 'OFFLINE'),
            'is_online' => $isOnline,
            'relay_status' => $isOnline && (bool) ($latestReading?->relay_status ?? false),
            'fault_status' => $faultStatus,
            'last_seen_at' => $this->last_seen_at?->toIso8601String(),
            'offline_after_seconds' => $offlineAfterSeconds,
        ];
    }
}
