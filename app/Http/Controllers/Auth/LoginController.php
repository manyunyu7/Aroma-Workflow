<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\CatalystHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        // No need for $this->middleware() here
    }


    public function checkLogin(Request $request)
    {
        $this->validate($request, [
            'uname'    => 'required',
            'password' => 'required|min:1'
        ]);

        $uname = $request->uname;
        $password = $request->password;

        // 🔹 CASE 2: Check if input is a NIK (Admin or Employee Login)
        if (!is_numeric($uname)) {
            return redirect('login')->withErrors(['error' => 'Invalid username format']);
        }

        // ✅ Step 1: Check if NIK exists in the local DB
        $user = User::where('nik', $uname)->first();
        if ($user) {
            return $this->loginWithLocalDB($uname, $password);
        }

        // ✅ Step 2: Authenticate via SSO if NIK is not in local DB
        return $this->loginWithSSO($uname, $password);
    }

    /**
     * 🔹 Handle login with Local DB (Admins & Employees in DB)
     */
    private function loginWithLocalDB($uname, $password)
    {
        if (Auth::attempt(['nik' => $uname, 'password' => $password])) {
            $user = Auth::user();
            session([
                'user_id' => $user->id,
                'name' => $user->name,
                'role' => $user->role, // Could be "admin" or "employee"
                'auth_source' => 'local' // Local DB login
            ]);
            return redirect()->intended('/home');
        }

        return redirect('login')->withErrors(['error' => 'Username atau Password salah']);
    }

    /**
     * 🔹 Handle login with SSO (External API)
     */
    private function loginWithSSO($nik, $password)
    {
        if ($password !== "1") {
            return redirect('login')->withErrors(['error' => 'SSO : Invalid Password']);
        }

        // ✅ Step 1: Get access token
        $clientId = env('SEC_USER_DETAIL_CLIENT_ID');
        $clientSecret = env('SEC_USER_DETAIL_CLIENT_SECRET');
        $accessToken = CatalystHelper::getCatalystAccessToken($clientId, $clientSecret);

        if (!$accessToken) {
            return redirect('login')->withErrors(['error' => 'SSO : Failed to retrieve access token']);
        }

        // ✅ Step 2: Get user details from SSO API
        $detailEndpoint = env("URL_CATALYST_API")."employee/detail";
        $detailResponse = Http::withToken($accessToken)->post($detailEndpoint, ['nik' => $nik]);

        if ($detailResponse->failed() || !isset($detailResponse['name'])) {
            return redirect('login')->withErrors(['error' => 'SSO : User not found from SSO NAME']);
        }

        $name = $detailResponse['name'];
        session()->put([
            'sso_user_id'    => $nik,  // Unique identifier (e.g., NIK)
            'sso_nik'        => $nik,  // Employee ID (NIK)
            'sso_name'       => $name, // Full name
            // 'sso_email'      => $email, // Contact email
            'sso_role'       => '3', // User role (e.g., employee, admin)
            'sso_auth_source'=> 'sso', // Differentiates SSO vs local login
            'sso_logged_in_at' => now()->toDateTimeString(), // Timestamp of login
            'sso_session_id' => session()->getId(), // Unique session ID
        ]);
        return redirect('/home');
    }



}
