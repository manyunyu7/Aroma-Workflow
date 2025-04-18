<?php

use App\Helpers\CatalystHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;


/**
 * Check if the authenticated user has a specific role
 *
 * @param string $roleName
 * @return bool
 */
function hasRole($roleName)
{
    $user = Auth::user();
    if (!$user) {
        return false;
    }


    return $user->roles()->where('role', $roleName)->exists();
}


if (!function_exists('format_currency')) {
    function format_currency($amount, $currency = 'USD')
    {
        return "$currency " . number_format($amount, 2);
    }
}

if (!function_exists('slugify')) {
    function slugify($string)
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    }
}

if (!function_exists('getAuthRole')) {
    function getAuthRole()
    {
        $user = Auth::user();
        if (!$user) return [];

        $roles = $user->roles;

        // Map roles to an array of role names
        return $roles->pluck('role')->toArray();
    }
}

if (!function_exists('getAuthId')) {
    function getAuthId()
    {
        if (Auth::check()) {
            return Auth::user()->id; // Local DB user
        }
        return 'Guest';
    }
}

if (!function_exists('getAuthNik')) {
    function getAuthNik()
    {
        if (Auth::check()) {
            return Auth::user()->nik; // Local DB user
        }
        return 'Guest';
    }
}



if (!function_exists('getAuthName')) {
    function getAuthName()
    {
        if (Auth::check()) {
            return Auth::user()->name; // Local DB user
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
        $detailEndpoint = env("URL_CATALYST_API") . "employee/detail";

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



if (!function_exists('getAllCostCenters')) {
    /**
     * Get all cost centers from API
     *
     * @return array
     */
    function getAllCostCenters()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::get('http://10.204.222.12/backend-fista/costcenter/getlist');

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === true) {
                    return $data['data'];
                }
            }

            \Illuminate\Support\Facades\Log::error('Failed to fetch cost centers', ['response' => $response->json()]);
            return [];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching cost centers: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('getCostCenterAccounts')) {
    /**
     * Get account list for a specific cost center ID
     *
     * @param int $unitCcId
     * @return array
     */
    function getCostCenterAccounts($unitCcId)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::get("http://10.204.222.12/backend-fista/costcenter/getaccountlist/{$unitCcId}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === true) {
                    return $data['data'];
                }
            }

            \Illuminate\Support\Facades\Log::error('Failed to fetch account list', ['unit_cc_id' => $unitCcId, 'response' => $response->json()]);
            return [];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching account list: ' . $e->getMessage(), ['unit_cc_id' => $unitCcId]);
            return [];
        }
    }
}

if (!function_exists('getCostCenterNameByAccountId')) {
    /**
     * Get cost center name by account ID
     *
     * @param string $accountId
     * @return string|null
     */
    function getCostCenterNameByAccountId($accountId)
    {
        // For now, returning null as it's mentioned "we might need it later"
        return null;
    }
}
