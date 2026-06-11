<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelayLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'device_id',
        'action',
        'triggered_by',
        'created_at',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
