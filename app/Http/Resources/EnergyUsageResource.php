<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnergyUsageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->date ?? null,
            'week' => $this->week ?? null,
            'month' => $this->month ?? null,
            'daily_kwh' => isset($this->daily_kwh) ? (float) $this->daily_kwh : null,
            'weekly_kwh' => isset($this->weekly_kwh) ? (float) $this->weekly_kwh : null,
            'monthly_kwh' => isset($this->monthly_kwh) ? (float) $this->monthly_kwh : null,
        ];
    }
}
