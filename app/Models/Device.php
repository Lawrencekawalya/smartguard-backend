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
        'threshold_config_version',
        'threshold_config_ack_version',
        'threshold_config_ack_payload',
        'threshold_config_status',
        'threshold_config_error',
        'threshold_config_synced_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'threshold_config_ack_payload' => 'array',
        'threshold_config_synced_at' => 'datetime',
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
