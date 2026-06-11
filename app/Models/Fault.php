<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fault extends Model
{
    protected $fillable = [
        'device_id',
        'fault_type',
        'description',
        'occurred_at',
        'resolved_at',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
