<?php
namespace App\Helpers;


use Illuminate\Support\Facades\Http;


class CatalystHelper
{
    public static function getCatalystAccessToken(string $clientId, string $clientSecret)
    {
        // Retrieve BASE_URL from the environment
        $BASE_URL = env('URL_CATALYST'); // Use getenv() for plain PHP

        // Ensure BASE_URL is not empty
        if (empty($BASE_URL)) {
            throw new \Exception("BASE_URL is not set in the environment.");
        }

        // Construct the full URL for the OAuth token endpoint
        $tokenUrl = ($BASE_URL) . 'oauth/token';

        // Make the POST request to get the access token
        $response = Http::asForm()->post($tokenUrl, [
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ]);

        // Parse the JSON response
        $data = $response->json();

        // Return the access token if it exists, otherwise return null
        return $data['access_token'] ?? null;
    }
}
