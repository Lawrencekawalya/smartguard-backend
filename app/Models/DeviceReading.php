<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceReading extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'voltage',
        'current',
        'real_power',
        'apparent_power',
        'power_factor',
        'energy_kwh',
        'relay_status',
        'fault_status',
        'created_at',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
