<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseController;

Route::get('/', function () {
    return view('auth.login');
});

// ✅ Profile routes – Require authenticated and verified users
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ✅ Dashboard and Firebase routes – Require login and email verification
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [FirebaseController::class, 'dashboard'])->name('dashboard');
    Route::post('/toggle', [FirebaseController::class, 'toggle'])->name('firebase.toggle');
    Route::post('/switch-mode', [FirebaseController::class, 'switchMode'])->name('firebase.switchMode');
    Route::post('/update-wifi', [FirebaseController::class, 'updateWifi'])->name('firebase.updateWifi'); // ✅ Add this
});

// ✅ Auth routes
require __DIR__.'/auth.php';
