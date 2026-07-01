<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaultSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parameter' => $this->parameter,
            'fault_code' => $this->fault_code,
            'min_value' => (float) $this->min_value,
            'max_value' => (float) $this->max_value,
            'unit' => $this->unit,
            'enabled' => (bool) $this->enabled,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
