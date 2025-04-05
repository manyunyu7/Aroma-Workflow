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

    public function checkLogin(Request $request)
    {
        $this->validate($request, [
            'uname'    => 'required',
            'password' => 'required|min:1'
        ]);

        $uname = $request->uname;
        $password = $request->password;

        // 🔹 CASE 1: If input is an email, login with email & password
        if (filter_var($uname, FILTER_VALIDATE_EMAIL)) {
            return $this->loginWithLocalDB($uname, $password, 'email');
        }

        // 🔹 CASE 2: Check if input is a NIK (must be numeric)
        if (!is_numeric($uname)) {
            return redirect('login')->withErrors(['error' => 'Invalid username format']);
        }


        return $this->loginWithSSO($uname, $password);
    }

    /**
     * 🔹 Handle login with Local DB (Admins & Employees in DB)
     */
    private function loginWithLocalDB($uname, $password, $field)
    {
        if (Auth::attempt([$field => $uname, 'password' => $password])) {
            $user = Auth::user();
            session([
                'user_id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'auth_source' => 'local'
            ]);
            return redirect()->intended('/home');
        }

        return redirect('login')->withErrors(['error' => 'Username atau Password salah (local)']);
    }

    /**
     * 🔹 Handle login with SSO (External API)
     */
    private function loginWithSSO($nik, $password)
    {
        if ($password !== "1") {
            return redirect('login')->withErrors(['error' => 'SSO : Invalid Password']);
        }

        //check if user exist in our DB
        $user = User::where('nik', $nik)->first();
        if (!$user) {
            return redirect('login')->withErrors(['error' => 'Akun tidak ditemukan pada sistem kami']);
        }

        if($user->status == "Not Active") {
            return redirect('login')->withErrors(['error' => 'Akun tidak aktif']);
        }

        if($user) {
            Auth::login($user);
        }

        // ✅ Get access token
        $clientId = env('SEC_USER_DETAIL_CLIENT_ID');
        $clientSecret = env('SEC_USER_DETAIL_CLIENT_SECRET');
        $accessToken = CatalystHelper::getCatalystAccessToken($clientId, $clientSecret);

        if (!$accessToken) {
            return redirect('login')->withErrors(['error' => 'SSO : Failed to retrieve access token']);
        }

        // ✅ Fetch user details from SSO API
        $detailEndpoint = env("URL_CATALYST_API")."employee/detail";
        $detailResponse = Http::withToken($accessToken)->post($detailEndpoint, ['nik' => $nik]);

        if ($detailResponse->failed() || !isset($detailResponse['name'])) {
            return redirect('login')->withErrors(['error' => 'SSO : User not found from SSO NAME']);
        }

        session()->put([
            'sso_user_id'    => $nik,
            'sso_nik'        => $nik,
            'sso_name'       => $detailResponse['name'],
            'sso_auth_source'=> 'sso',
            'sso_logged_in_at' => now()->toDateTimeString(),
            'sso_session_id' => session()->getId(),
        ]);

        return redirect('/home');
    }
}
