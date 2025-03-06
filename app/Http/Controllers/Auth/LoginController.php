<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

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
            'uname'   => 'required',
            'password' => 'required|min:2'
        ]);

        if (Auth::attempt(
            [
                'email' => $request->uname,
                'password' => $request->password
            ],
            $request->get('remember')
        )) {
            return redirect()->intended('/home');
        } else {
            return redirect('login')->withErrors([
                'error' => 'Username Atau Password Salah'
            ]);
        }

        return back()->withInput($request->only('uname', 'remember'));
    }
}
