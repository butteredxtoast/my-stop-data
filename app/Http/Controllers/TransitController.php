<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TransitController extends Controller
{
    /**
     * Get real-time arrivals for a given stop and agency.
     */
    public function getRealTimeArrivals($stopCode, $agency)
    {
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
            return response()->json(json_decode($cleanedResponse, true)); // return valid JSON
        } else {
            return response()->json(['error' => 'Unable to fetch data'], 500);
        }
    }

    /**
     * Get a list of stops for a given operator.
     */
    public function getStops($operator)
    {
        $apiKey = env('511_API_KEY');
        $baseUrl = env('511_API_BASE_URL');

        // Fetch the list of stops
        $response = Http::get("{$baseUrl}/stops", [
            'api_key' => $apiKey,
            'operator_id' => $operator,
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        } else {
            return response()->json(['error' => 'Unable to fetch stop data'], 500);
        }
    }

    /**
     * Get a list of all operators.
     */
    public function getOperators()
    {
        return Cache::remember('operators', now()->addDay(), function () {
            $apiKey = env('511_API_KEY');
            $baseUrl = env('511_API_BASE_URL');

            try {
                $response = Http::get("{$baseUrl}/operators", [
                    'api_key' => $apiKey,
                ]);

                if ($response->successful()) {
                    return $response->json();
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
}
