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

    protected $casts = [
        'daily_kwh' => 'decimal:6',
        'monthly_kwh' => 'decimal:6',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
