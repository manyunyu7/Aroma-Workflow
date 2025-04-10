<?php

namespace App\Http\Controllers;

use App\Helpers\CatalystHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class MasterKaryawanController extends Controller
{

    public function getAllUsers(Request $request){
        // return all users and their roles
        $users = \App\Models\User::with('roles')->get();

        if($request->grouped_by_role == true){
            $groupedUsers = [];
            foreach($users as $user){
                foreach($user->roles as $role){
                    $groupedUsers[$role->name][] = $user;
                }
            }
            return response()->json([
                'state' => 'success',
                'code' => 200,
                'data' => $groupedUsers,
            ]);
        }
        // return all users without grouping
        return response()->json([
            'state' => 'success',
            'code' => 200,
            'data' => $users,
        ]);
    }

    public function detailKaryawan(Request $request)
    {
        $nik = $request->input('nik');

        // ✅ Step 1: Get access token
        $clientId = env('SEC_USER_DETAIL_CLIENT_ID');
        $clientSecret = env('SEC_USER_DETAIL_CLIENT_SECRET');
        $accessToken = CatalystHelper::getCatalystAccessToken($clientId, $clientSecret);

        if (!$accessToken) {
            return redirect('login')->withErrors(['error' => 'Failed to retrieve access token']);
        }

        // ✅ Step 2: Get user details from SSO API
        $detailEndpoint = env("URL_CATALYST_API") . "employee/detail";
        $detailResponse = Http::withToken($accessToken)->post($detailEndpoint, ['nik' => $nik]);

        return $detailResponse->json();
    }

    public function getAllKaryawan(Request $request)
    {
        $clientIdPersonal = "9964612a-f658-4d31-8f28-f146dd8c8eb3";
        $clientSecretPersonal = "LZFCNQ0QrMcvMxUjJqkolRkPaySQh0hJVwTRGusb";
        $endpointPersonal = "https://catalyst.telkomakses.co.id:2101/api/v1/employee/personal";

        $clientIdDetail = "9ae6194e-ab57-4936-ab34-08877c9b2382";
        $clientSecretDetail = "BvmTwAFR8Ku112gPvuULOoIZHQr1VWZrjtuKGc99";
        $endpointDetail = "https://catalyst.telkomakses.co.id:2101/api/v1/employee/detail";

        // Get parameter from request
        $param = $request->input('param');

        // Get access token for personal data
        $accessTokenPersonal = CatalystHelper::getCatalystAccessToken($clientIdPersonal, $clientSecretPersonal);
        if (!$accessTokenPersonal) {
            return response()->json(['error' => 'Failed to retrieve access token for personal data'], 500);
        }

        // Fetch list of employees
        $personalResponse = Http::withToken($accessTokenPersonal)->post($endpointPersonal, [
            'param' => $param,
        ]);

        if ($personalResponse->failed()) {
            return response()->json(['error' => 'Failed to retrieve employee personal data'], $personalResponse->status());
        }

        $employees = $personalResponse->json();
        if (!isset($employees['payload']) || empty($employees['payload'])) {
            return response()->json(['error' => 'No employees found'], 404);
        }

        // Get access token for detail data
        $accessTokenDetail = CatalystHelper::getCatalystAccessToken($clientIdDetail, $clientSecretDetail);
        if (!$accessTokenDetail) {
            return response()->json(['error' => 'Failed to retrieve access token for employee details'], 500);
        }

        // Fetch details for each employee using NIK
        $detailedEmployees = [];
        foreach ($employees['payload'] as $employee) {
            $nik = $employee['nik'] ?? null;
            if (!$nik) continue;

            $detailResponse = Http::withToken($accessTokenDetail)->post($endpointDetail, [
                'nik' => $nik,
            ]);

            $detailData = $detailResponse->successful() ? $detailResponse->json() : ['error' => 'Failed to fetch details'];

            $detailedEmployees[] = [
                'personal' => $employee,
                'detail' => $detailData,
            ];
        }

        return response()->json([
            'state' => 'success',
            'code' => 200,
            'data' => $detailedEmployees,
        ]);
    }
}
