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
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // If User is Karyawan
        if (Auth::user()->role == "2") {
            return redirect("/operator/home");
        }
        // If User is User Biasa / Pengirim Ticket
        if (Auth::user()->role == "3") {
            return redirect("/user/home");
        }
        // If User is User Admin
        if (Auth::user()->role == "1") {
            return redirect("/admin/home");
        }


        return view('home.index');
    }

    public function homeUser()
    {
        $auth = Auth::user()->id;
        $totalTicket = TicketModel::all()->where('sender_id', '=', $auth)->count();
        $totalTicketPending = TicketModel::where('sender_id', '=', $auth)->where('status', '=', '3')->count();
        $totalTicketSolved = TicketModel::where('sender_id', '=', $auth)->where('status', '=', '1')->count();
        $totalTicketProgress = TicketModel::where('sender_id', '=', $auth)->where('status', '=', '2')->count();
        $totalTicketCanceled = TicketModel::where('sender_id', '=', $auth)->where('status', '=', 99)->count();

        $tickets =  TicketModel::where('sender_id', '=', $auth)->where('status','<>',99)->get();
        return view('home.user')
            ->with(compact('totalTicket','totalTicketCanceled', 'totalTicketPending', 'totalTicketSolved', 'totalTicketProgress', 'tickets'));
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
        $totalUserOperator = User::all()->where('role','=','2')->count();
        $totalUserAdmin = User::all()->where('role','=','1')->count();
        $totalUserUser = User::all()->where('role','=','3')->count();

        $discuss = Discussion::where('type','=',null)->limit(3)->get();

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
