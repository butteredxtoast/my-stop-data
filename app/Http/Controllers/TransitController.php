<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TransitController extends Controller
{
    /**
     * Fetch the real-time arrivals for a given stop code and agency.
     */
    public function getRealTimeArrivals($stopCode, $agency)
    {
        $apiKey = env('511_API_KEY');
        $baseUrl = env('511_API_BASE_URL');

        // Perform the API request with the stopCode and agency provided via the route
        $response = Http::get("{$baseUrl}/StopMonitoring", [
            'api_key' => $apiKey,
            'agency' => $agency, // SF
            'stopCode' => $stopCode, // 15567
        ]);

        // Strip any BOM from the response body
        $cleanedResponse = preg_replace('/\x{FEFF}/u', '', $response->body());

        // Check if the response is successful
        if ($response->successful()) {
            return response()->json(json_decode($cleanedResponse, true)); // return valid JSON
        } else {
            return response()->json(['error' => 'Unable to fetch data'], 500);
        }
    }

    /**
     * Fetch the list of stops for a given operator.
     */
    public function getStops($operator)
    {
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
    }

    /**
     * Fetch the list of operators.
     */
    public function getOperators()
    {
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
    }
}
