<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaultSetting extends Model
{
    protected $fillable = [
        'parameter',
        'fault_code',
        'min_value',
        'max_value',
        'unit',
        'enabled',
        'description',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'min_value' => 'float',
        'max_value' => 'float',
    ];
}
