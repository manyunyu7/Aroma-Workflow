<?php

use App\Helpers\CatalystHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

if (!function_exists('format_currency')) {
    function format_currency($amount, $currency = 'USD') {
        return "$currency " . number_format($amount, 2);
    }
}

if (!function_exists('slugify')) {
    function slugify($string) {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    }
}

if (!function_exists('getAuthRole')) {
    function getAuthRole()
    {
        if (Auth::check()) {
            return Auth::user()->role; // Local DB user
        } elseif (session()->has('sso_role')) {
            return session('sso_role'); // SSO user
        }
        return 'Guest';
    }
}

if (!function_exists('getAuthId')) {
    function getAuthId()
    {
        if (Auth::check()) {
            return Auth::user()->id; // Local DB user
        } elseif (session()->has('sso_role')) {
            return session('sso_user_id'); // SSO user
        }
        return 'Guest';
    }
}

if (!function_exists('getAuthNik')) {
    function getAuthNik()
    {
        if (Auth::check()) {
            return Auth::user()->id; // Local DB user
        } elseif (session()->has('sso_role')) {
            return session('sso_nik'); // SSO user
        }
        return 'Guest';
    }
}



if (!function_exists('getAuthName')) {
    function getAuthName()
    {
        if (Auth::check()) {
            return Auth::user()->name; // Local DB user
        } elseif (session()->has('sso_name')) {
            return session('sso_name'); // SSO user
        }
        return 'Guest';
    }
}

if (!function_exists('getDetailNaker')) {
    function getDetailNaker($nik)
    {
        if (!$nik) {
            return null; // No authenticated user
        }

        // Use the stored client credentials for authentication
        $clientId = env('SEC_USER_DETAIL_CLIENT_ID');
        $clientSecret = env('SEC_USER_DETAIL_CLIENT_SECRET');
        $detailEndpoint = env("URL_CATALYST_API")."employee/detail";

        // Fetch access token
        $accessToken = CatalystHelper::getCatalystAccessToken($clientId, $clientSecret);

        if (!$accessToken) {
            return null;
        }

        // Fetch employee details
        $response = Http::withToken($accessToken)->post($detailEndpoint, [
            'nik' => $nik
        ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json() ?? null;
    }
}
