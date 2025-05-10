<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorReading;

class SensorReadingController extends Controller
{
    /**
     * Handle an incoming sensor‑reading POST.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // raw readings
            'distance_cm'        => 'required|numeric',
            'tds_ppm'            => 'required|numeric',
            'temp_c'             => 'required|numeric',
            'water_level'        => 'required|integer',

            // device‑sent timestamp (unique natural key)
            'reading_at'         => 'required|date_format:Y-m-d H:i:s',

            // metadata (nullable in case some bits are missing)
            'ip'                 => 'nullable|string|max:45',
            'last_online'        => 'nullable|date_format:H:i:s',
            'last_sync'          => 'nullable|date_format:H:i:s',
            'mac'                => 'nullable|string|max:17',

            // control flags
            'mode'       => 'nullable|string|max:20',
            'oled_control'       => 'required|boolean',
            'pump_control'       => 'required|boolean',
            'tds_control'        => 'required|boolean',
            'temp_control'       => 'required|boolean',
            'ultrasonic_control' => 'required|boolean',

            // status metadata
            'status_buzzer'      => 'nullable|string|max:10',
            'status_oled'        => 'nullable|string|max:10',
            'status_pump'        => 'nullable|string|max:10',
            'status_tds'         => 'nullable|string|max:10',
            'status_temp'        => 'nullable|string|max:10',
            'status_ultrasonic'  => 'nullable|string|max:10',
            'wifi_ssid'          => 'nullable|string|max:50',
        ]);

        // firstOrCreate prevents duplicate reading_at
        $reading = SensorReading::firstOrCreate(
            ['reading_at' => $data['reading_at']],
            $data
        );

        return response()->json([
            'success'          => true,
            'wasRecentlyCreated' => $reading->wasRecentlyCreated,
            'reading'          => $reading,
        ], $reading->wasRecentlyCreated ? 201 : 200);
    }
}
