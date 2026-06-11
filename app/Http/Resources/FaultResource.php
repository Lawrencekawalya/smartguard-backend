<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaultResource extends JsonResource
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
            'device_code' => $this->device->device_code,
            'fault_type' => $this->fault_type,
            'description' => $this->description,
            'occurred_at' => $this->occurred_at,
            'resolved_at' => $this->resolved_at,
        ];
    }
}
