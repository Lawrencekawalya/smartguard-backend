<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergySummary extends Model
{
    protected $fillable = [
        'device_id',
        'summary_date',
        'daily_kwh',
        'monthly_kwh',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
