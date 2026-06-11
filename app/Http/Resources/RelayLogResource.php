<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RelayLogResource extends JsonResource
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
            'action' => $this->action,
            'triggered_by' => $this->triggered_by,
            'created_at' => $this->created_at,
        ];
    }
}
