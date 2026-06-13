<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergySetting extends Model
{
    protected $fillable = [
        'tariff_rate',
        'currency',
        'description',
    ];

    protected $casts = [
        'tariff_rate' => 'decimal:2',
    ];
}
