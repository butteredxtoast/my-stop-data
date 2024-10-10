<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TransitController extends Controller
{
    /**
     * Get a list of all operators.
     */
    public function getOperators()
    {
        return Cache::remember('operators', now()->addDay(), function () {
            $apiKey = env('511_API_KEY');
            $baseUrl = env('511_API_BASE_URL');

            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json', // Specify JSON response format
                ])->get("{$baseUrl}/operators", [
                    'api_key' => $apiKey,
                ]);

                // Remove any BOM from the response body
                $cleanedResponse = preg_replace('/\x{FEFF}/u', '', $response->body());

                // Check if the response is successful
                if ($response->successful()) {
                    return json_decode($cleanedResponse, true); // Return valid JSON response
                } else {
                    \Log::error('Failed to fetch operator data', ['response' => $response->body()]);
                    return ['error' => 'Unable to fetch operator data'];
                }
            } catch (\Exception $e) {
                \Log::error('Exception occurred while fetching operator data', ['exception' => $e->getMessage()]);
                return ['error' => 'Unable to fetch operator data'];
            }
        });
    }

    /**
     * Get a list of stops for a given operator.
     */
    public function getStops($operator)
    {
        return Cache::remember("stops_{$operator}", now()->addDay(), function () use ($operator) {
            $apiKey = env('511_API_KEY');
            $baseUrl = env('511_API_BASE_URL');

            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                ])->get("{$baseUrl}/stops", [
                    'api_key' => $apiKey,
                    'operator_id' => $operator,
                ]);

                // Remove any BOM from the response body
                $cleanedResponse = preg_replace('/\x{FEFF}/u', '', $response->body());

                // Check if the response is successful
                if ($response->successful()) {
                    return json_decode($cleanedResponse, true); // Return valid JSON response
                } else {
                    \Log::error('Failed to fetch stop data', ['response' => $response->body()]);
                    return ['error' => 'Unable to fetch stop data'];
                }
            } catch (\Exception $e) {
                \Log::error('Exception occurred while fetching stop data', ['exception' => $e->getMessage()]);
                return ['error' => 'Unable to fetch stop data'];
            }
        });
    }

    /**
     * Get real-time arrivals for a given stop and agency.
     */
    public function getRealTimeArrivals($stopCode, $agency)
    {
        return Cache::remember("real_time_arrivals_{$stopCode}_{$agency}", now()->addMinutes(5), function () use ($stopCode, $agency) {
            $apiKey = env('511_API_KEY');
            $baseUrl = env('511_API_BASE_URL');

            // Perform the API request
            $response = Http::get("{$baseUrl}/StopMonitoring", [
                'api_key' => $apiKey,
                'agency' => $agency,
                'stopCode' => $stopCode,
            ]);

            // Remove any BOM from the response body
            $cleanedResponse = preg_replace('/\x{FEFF}/u', '', $response->body());

            // Check if the response is successful
            if ($response->successful()) {
                return json_decode($cleanedResponse, true); // return valid JSON
            } else {
                return ['error' => 'Unable to fetch real-time arrival data'];
            }
        });
    }
}
