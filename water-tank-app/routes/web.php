<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\SensorReadingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\WaterHistoryController;
use App\Http\Controllers\TdsHistoryController;
use App\Http\Controllers\SensorHistoryController;
use App\Http\Controllers\DeviceHistoryController;


// Login
Route::get('/', fn() => view('auth.login'));

// Profile (auth + verified)
Route::middleware(['auth', 'verified'])->group(function () {
  Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Dashboard & controls (auth + verified)
Route::middleware(['auth', 'verified'])->group(function () {
  Route::get('/dashboard',        [FirebaseController::class, 'dashboard'])->name('dashboard');
  Route::post('/toggle',          [FirebaseController::class, 'toggle'])->name('firebase.toggle');
  Route::post('/switch-mode',     [FirebaseController::class, 'switchMode'])->name('firebase.switchMode');
  Route::post('/update-wifi',     [FirebaseController::class, 'updateWifi'])->name('firebase.updateWifi');
  Route::post('/sensor-readings', [SensorReadingController::class, 'store'])->name('sensor.readings');

  Route::middleware(['auth', 'verified'])
    ->get('/temperature-history', [HistoryController::class, 'show'])
    ->name('temperature.history');
  Route::get('/temperature-history/data', [HistoryController::class, 'index'])
    ->name('temperature.history.data');
  Route::middleware(['auth', 'verified'])
    ->get('/water-level-history', [WaterHistoryController::class, 'show'])
    ->name('water.history');
  Route::get('/water-history/data', [WaterHistoryController::class, 'index'])
    ->name('water.history.data');
  Route::middleware(['auth', 'verified'])
    ->get('/tds-history', [TdsHistoryController::class, 'show'])
    ->name('tds.history');
  Route::get('/tds-history/data', [TdsHistoryController::class, 'index'])
    ->name('tds.history.data');
  Route::middleware(['auth', 'verified'])
    ->get('/sensor-history', [SensorHistoryController::class, 'show'])
    ->name('sensor.history');
  Route::get('/sensor-history/data', [SensorHistoryController::class, 'index'])
    ->name('sensor.history.data');
  Route::middleware(['auth', 'verified'])
    ->get('/device-history', [DeviceHistoryController::class, 'show'])
    ->name('device.history');
  Route::get('/device-history/data', [DeviceHistoryController::class, 'index'])
    ->name('device.history.data');
});

// Auth scaffolding
require __DIR__ . '/auth.php';
