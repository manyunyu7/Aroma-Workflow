<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use App\Models\Discussion;
use App\Models\TicketCategory;
use App\Models\TicketModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    public function viewManage($status)
    {
        $status_code = 0;
        switch ($status) {
            case 'pending':
                $status_code = 3;
                break;

            case 'progress':
                $status_code = 2;
                break;

            case 'complete':
                $status_code = 1;
                break;

            case 'undelegated':
                $status_code = 123;

                break;

            case 'mywork':
                $tickets = TicketModel::where('delegate_id', '=', Auth::user()->id)
                    ->where('status', '!=', 99)->get();
                $ticket_status = "";
                $operators = User::where([
                    ['role', '=', '2'],
                ])->get();

                return view('ticket.admin.manage')->with(compact('ticket_status', 'tickets', 'operators'));
                break;

            default:
                # code...
                break;
        }
        $tickets = TicketModel::where('status', '=', $status_code)->get();
        $ticket_status = "";
        $operators = User::where([
            ['role', '=', '2'],
        ])->get();

        if (Auth::user()->role == 2) {
            $tickets = TicketModel::where('delegate_id', '=', Auth::user()->id)->where('status', '=', $status_code)->get();
        }


        if ($status_code == 123) {
            $tickets = TicketModel::where('delegate_id', '=', null)->where('status', '!=', 99)->get();
        }


        return view('ticket.admin.manage')->with(compact('ticket_status', 'tickets', 'operators'));
    }


    function delegate(Request $request)
    {
        $object = TicketModel::find($request->id);
        $object->delegate_id = $request->delegate_id;
        $user = User::find($request->delegate_id);
        $sender = User::find($object->sender_id);

        $discussions = new Discussion();
        $discussions->message = "Ticket Ini Telah Dihandover kepada operator $user->name";
        $discussions->type = 3;
        $discussions->topic = $object->id;
        $discussions->save();

        $emailFor = $sender->email;
        $idTicket = $object->id;
        $emailMessage = "Ticket $idTicket Anda Telah Dialihkan kepada operator lain";
//        $kirim = Mail::to($email)->send(new SendMail($emailMessage,$emailFor));
//        $kirim = Mail::to($email)->send(new SendMail("Nama","Jenderal"));


        if ($object->save()) {
            //Kirim Email ke User
            Mail::to($emailFor)->send(new SendMail(
                "Pembaruan Ticket",
                "Status Ticket anda dengan nomor ticket "
                . " "
                . $object->nomor_ticket
                . " Telah Dialihkan ke operator lain : "
                . $object->operator->name
                . " dengan pesan tambahan sebagai berikut <br> <br>
            $discussions->message",
                $user->name
            ));

            //Kirim Email ke Staff Delegasi
            Mail::to($object->operator->email)->send(new SendMail(
                "Pembaruan Ticket",
                "Status Ticket dengan nomor ticket " . " "
                . $object->nomor_ticket
                . " Telah Didelegasikan kepada anda "
                . $object->operator->name
                . " dengan pesan tambahan sebagai berikut <br> <br>
            $discussions->message",
                $user->name
            ));

            return back()->with(["success" => "Berhasil Handover Ticket Kepada $user->name"]);
        } else {
            return back()->with(["error" => "Gagal Handover Ticket Kepada $user->name"]);
        }
    }

    public function destroy(Request $request)
    {
        $object = TicketModel::find($request->id);
        if ($object->delete()) {
            return redirect('/admin/ticket/pending')->with(["success" => "Berhasil Menghapus Ticket"]);
        } else {
            return redirect('/admin/ticket/pending')->with(["error" => "Gagal Menghapus Ticket"]);
        }
    }

    public function update_status(Request $request)
    {

        $object = TicketModel::find($request->id);
        $object->status = $request->status;
        $object->priority = $request->priority;

        if ($request->status == 1) {
            $object->durasi = date('Y-m-d H:i:s');
        }

        if ($object->save()) {


            $user = User::find($object->sender_id);
            $kirim = Mail::to($user->email)->send(new SendMail(
                "#$object->nomor_ticket Pembaruan Status Ticket",
                "Status Ticket anda dengan nomor ticket "
                . " "
                . $object->nomor_ticket
                . " Telah Diupdate oleh menjadi <strong>$object->status_desc </strong> <br>"
                . "Oleh Operator : <br>"
                . $object->operator->name,
                $user->name
            ));


            return back()->with(["success" => "Berhasil Mengubah Status Ticket Tickets"]);
        } else {
            return back()->with(["error" => "Gagal Mengubah Status Ticket"]);
        }
    }


    public function viewCreate()
{
    $categories = TicketCategory::all();
    return view('ticket.add')->with(compact('categories'));
}

    public function viewDetail($id)
    {
        $data = TicketModel::find($id);
        $user = User::find($data->sender_id);
        $discussions = Discussion::where('topic', '=', $id)->get();
        $operators = User::where('role', '=', '2')->get();

        return view('ticket.edit')->with(compact('data', 'user', 'discussions', 'operators'));
    }
}
