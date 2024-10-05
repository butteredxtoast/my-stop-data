<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TransitService
{
    public function getRealTimeArrivals($stopCode, $agency)
    {
        // All API interactions happen here
        $apiKey = env('511_API_KEY');
        $baseUrl = env('511_API_BASE_URL');

        $response = Http::get("{$baseUrl}/StopMonitoring", [
            'api_key' => $apiKey,
            'agency' => $agency,
            'stopCode' => $stopCode,
        ]);

        return $response->successful() ? json_decode($response->body(), true) :
            ['error' => 'Unable to fetch data'];
    }

}
