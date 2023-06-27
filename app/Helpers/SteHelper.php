<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class SteHelper
{
    // const COMPANY = "Voldo";
    
    public static function getApiKey()
    {
        return env('API_STE');
    }
    
    public static function apiSte($data, $endpoint)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . self::getApiKey(),
        ])->post("https://systemtunes.com/apivoldo/".$endpoint, $data);
    
        $httpCode = $response->status();
        
        if ($httpCode === 200) {
            $responseData = $response->json();
            // Process the response data
            print_r($responseData);
        } elseif ($httpCode === 401) {
            $errorData = $response->json();
            print_r($errorData);
        }
    }
}
