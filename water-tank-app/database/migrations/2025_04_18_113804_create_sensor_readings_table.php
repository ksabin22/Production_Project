<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->bigIncrements('id');

            // raw readings
            $table->double('distance_cm', 8, 4);
            $table->double('tds_ppm',      10, 4);
            $table->double('temp_c',       6,  2);
            $table->integer('water_level');

            // device‑sent timestamp
            $table->timestamp('reading_at')
                  ->useCurrent()       // default to NOW()
                  ->unique();          // enforce unique timestamps

            // metadata
            $table->string('ip',        45)->nullable();
            $table->time('last_online')->nullable();
            $table->time('last_sync')->nullable();
            $table->string('mac',       17)->nullable();

            // control/status flags
            $table->string('mode',       10)->nullable();
            $table->boolean('oled')->default(false);
            $table->boolean('pump')->default(false);
            $table->boolean('tds')->default(false);
            $table->boolean('temp')->default(false);
            $table->boolean('ultrasonic')->default(false);

            // extra
            $table->string('wifi_ssid', 64)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
