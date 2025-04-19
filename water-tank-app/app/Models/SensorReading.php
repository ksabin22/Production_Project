<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = [
        'distance_cm',
        'tds_ppm',
        'temp_c',
        'water_level',
        'reading_at',
        'ip',
        'last_online',
        'last_sync',
        'mac',
        'mode',
        'oled',
        'pump',
        'tds',
        'temp',
        'ultrasonic',
        'wifi_ssid',
    ];

    protected $casts = [
        'distance_cm' => 'double',
        'tds_ppm'     => 'double',
        'temp_c'      => 'double',
        'water_level' => 'integer',
        'reading_at'  =>  'datetime:Y-m-d H:i:s',
        'last_online' => 'datetime:H:i:s',
        'last_sync'   => 'datetime:H:i:s',
        'oled'        => 'boolean',
        'pump'        => 'boolean',
        'tds'         => 'boolean',
        'temp'        => 'boolean',
        'ultrasonic'  => 'boolean',
    ];
}
