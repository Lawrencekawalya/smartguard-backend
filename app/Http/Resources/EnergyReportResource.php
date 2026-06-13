<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnergyReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this['date'],
            'energy_used' => (float) $this['energy_used'],
            'estimated_cost' => (float) $this['estimated_cost'],
            'peak_power' => (float) $this['peak_power'],
            'fault_count' => (int) $this['fault_count'],
        ];
    }
}
