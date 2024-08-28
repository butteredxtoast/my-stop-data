<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/real-time-arrivals/{stopCode}/{agency}', function ($stopCode, $agency) {
    $apiKey = env('511_API_KEY');
    $baseUrl = env('511_API_BASE_URL');

    // Perform the API request with the stopCode and agency provided via the route
    $response = Http::get("{$baseUrl}/StopMonitoring", [
        'api_key' => $apiKey,
        'agency' => $agency, // SF
        'stopCode' => $stopCode, // 15567
    ]);

    // Check if the response is successful
    if ($response->successful()) {
        return response()->json($response->body()); // return JSON response
    } else {
        return response()->json(['error' => 'Unable to fetch data'], 500);
    }
});

Route::get('/stops/{operator}', function ($operator) {
    $apiKey = env('511_API_KEY');
    $baseUrl = env('511_API_BASE_URL');

    // Fetch the list of stops for the given operator
    $response = Http::get("{$baseUrl}/stops", [
        'api_key' => $apiKey,
        'operator_id' => $operator,
    ]);

    if ($response->successful()) {
        // Return the list of stops
        return response()->json($response->json());
    } else {
        return response()->json(['error' => 'Unable to fetch stop data'], 500);
    }
});

Route::get('/operators', function () {
    $apiKey = env('511_API_KEY');
    $baseUrl = env('511_API_BASE_URL');

    $response = Http::get("{$baseUrl}/operators", [
        'api_key' => $apiKey,
    ]);

    if ($response->successful()) {
        return response()->json($response->json());
    } else {
        return response()->json(['error' => 'Unable to fetch operator data'], 500);
    }
});

require __DIR__.'/auth.php';
