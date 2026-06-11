<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'device_name',
        'device_code',
        'location',
        'status',
        'firmware_version',
        'ip_address',
        'last_seen_at',
    ];

    public function readings()
    {
        return $this->hasMany(DeviceReading::class);
    }

    public function faults()
    {
        return $this->hasMany(Fault::class);
    }

    public function relayLogs()
    {
        return $this->hasMany(RelayLog::class);
    }

    public function energySummaries()
    {
        return $this->hasMany(EnergySummary::class);
    }
}
