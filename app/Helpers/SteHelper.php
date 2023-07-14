<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class SteHelper
{

    public static function apiSte($data, $endpoint)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('API_STE', ''),
        ])->post("https://systemtunes.com/apivoldo/".$endpoint, $data);

        $httpCode = $response->status();

        return $response->json();

        if ($httpCode === 200) {
            $responseData = $response->json();
            // Process the response data
            // print_r($responseData);
            return $responseData;
        } elseif ($httpCode === 401) {
            $errorData = $response->json();
            // print_r($errorData);
            return $errorData;
        }
    }
}
