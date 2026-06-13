<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnergySettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tariff_rate' => (float) $this->tariff_rate,
            'currency' => $this->currency,
            'description' => $this->description,
        ];
    }
}
