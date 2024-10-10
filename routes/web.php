<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransitController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home', [
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::get('/operators', [TransitController::class, 'getOperators'])->name('operators');
Route::get('/stops/{operator}', [TransitController::class, 'getStops'])->name('stops');
Route::get('/real-time-arrivals/{stopCode}/{agency}', [TransitController::class, 'getRealTimeArrivals'])->name('real-time-arrivals');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
