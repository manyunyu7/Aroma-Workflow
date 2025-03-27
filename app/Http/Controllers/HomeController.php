<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\TicketModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index()
    {
        // Check if user is logged in via SSO session
        if (session()->has('sso_user_id')) {
            $role = session('sso_role');

            // Avoid infinite redirects
            if (request()->is('admin/home') && $role == "1") {
                return $this->homeAdmin();
            }
            if (request()->is('operator/home') && $role == "2") {
                return $this->homeOperator();
            }
            if (request()->is('user/home') && $role == "3") {
                return $this->homeUser();
            }

            // Redirect if not already in the correct route
            if ($role == "1") return redirect("/admin/home");
            if ($role == "2") return redirect("/operator/home");
            if ($role == "3") return redirect("/user/home");

            return view('home.index');
        }

        // Check if user is logged in via Laravel Auth (Local Admin)
        if (Auth::check()) {
            $role = Auth::user()->role;

            // Avoid infinite redirects
            if (request()->is('admin/home') && $role == "1") {
                return $this->homeAdmin();
            }
            if (request()->is('operator/home') && $role == "2") {
                return $this->homeOperator();
            }
            if (request()->is('user/home') && $role == "3") {
                return $this->homeUser();
            }

            // Redirect if not already in the correct route
            if ($role == "1") return redirect("/admin/home");
            if ($role == "2") return redirect("/operator/home");
            if ($role == "3") return redirect("/user/home");

            return view('home.index');
        }

        // If no session or authentication, redirect to login
        return redirect('/login')->withErrors(['error' => 'Please log in first']);
    }

    public function homeUser()
    {
        // Determine the authenticated user ID
        if (session()->has('sso_user_id')) {
            $authId = session('sso_user_id'); // Get user ID from SSO session
        } elseif (Auth::check()) {
            $authId = Auth::user()->id; // Get user ID from local database
        } else {
            return redirect('/login')->withErrors(['error' => 'Please log in first']);
        }

        // Fetch ticket data based on the authenticated user
        $totalTicket = TicketModel::where('sender_id', '=', $authId)->count();
        $totalTicketPending = TicketModel::where('sender_id', '=', $authId)->where('status', '=', '3')->count();
        $totalTicketSolved = TicketModel::where('sender_id', '=', $authId)->where('status', '=', '1')->count();
        $totalTicketProgress = TicketModel::where('sender_id', '=', $authId)->where('status', '=', '2')->count();
        $totalTicketCanceled = TicketModel::where('sender_id', '=', $authId)->where('status', 99)->count();

        $tickets = TicketModel::where('sender_id', '=', $authId)->where('status', '<>', 99)->get();

        return view('home.user')->with(compact(
            'totalTicket',
            'totalTicketCanceled',
            'totalTicketPending',
            'totalTicketSolved',
            'totalTicketProgress',
            'tickets'
        ));
    }


    public function homeAdmin()
    {
        $totalTicket = TicketModel::all()->count();
        $totalTicketPending = TicketModel::where('status', '=', '3')->count();
        $totalTicketSolved = TicketModel::where('status', '=', '1')->count();
        $totalTicketProgress = TicketModel::where('status', '=', '2')->count();
        $totalTicketCanceled = TicketModel::where('status', '=', 99)->count();


        $totalTicket1 = TicketModel::where('category', '=', '1')->count();
        $totalTicket2 = TicketModel::where('category', '=', '2')->count();
        $totalTicket3 = TicketModel::where('category', '=', '3')->count();
        $totalTicket4 = TicketModel::where('category', '=', '4')->count();
        $totalTicket5 = TicketModel::where('category', '=', '5')->count();
        $totalTicket6 = TicketModel::where('category', '=', '6')->count();

        $totalUser = User::all()->count();
        $totalUserOperator = User::all()->where('role', '=', '2')->count();
        $totalUserAdmin = User::all()->where('role', '=', '1')->count();
        $totalUserUser = User::all()->where('role', '=', '3')->count();

        $discuss = Discussion::where('type', '=', null)->limit(3)->get();

        $tickets = TicketModel::all();

        $cp = compact(
            'discuss',
            'totalTicket1',
            'totalTicket2',
            'totalTicket3',
            'totalTicket4',
            'totalTicket5',
            'totalTicket6',
            'totalUser',
            'totalUserAdmin',
            'totalUserOperator',
            'totalTicketCanceled',
            'totalUserUser',
            'totalTicket',
            'totalTicketPending',
            'totalTicketSolved',
            'totalTicketProgress',
            'tickets'
        );

        return view('home.admin')
            ->with($cp);
    }
}
