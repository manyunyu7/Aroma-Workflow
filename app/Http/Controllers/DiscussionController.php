<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Models\Discussion;
use App\Models\TicketModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class DiscussionController extends Controller
{
    public function store(Request $request)
    {
        $sendedMessage = "";
        if ($request->message == "" || $request->message == null) {
            // DO NOTHING
        } else {
            $sendedMessage = $request->message;
        }

        $object = new Discussion();

        $object->id_sender = Auth::user()->id;
        $object->message = $sendedMessage;
        $object->topic = $request->topic;

        $ditanggapiOleh = "Pengguna";

        if (Auth::user()->role == 2 || Auth::user()->role == 1) {
            $ditanggapiOleh = "Operator";
        }


        if ($object->save()) {

            $topic = TicketModel::find($request->topic);
            $sender = User::find($topic->sender_id);
            $senderName = $sender->name;

            $emailFor = $sender->email;

            if (Auth::user()->role==2 || Auth::user()->role==1){
                $emailFor= Auth::user()->email;
            }

            Mail::to($emailFor)->send(new SendMail(
                "#$topic->nomor_ticket : Balasan Baru ",
                "Status Ticket anda dengan nomor ticket "
                . " "
                . "<strong>" . $topic->nomor_ticket . "</strong>"
                . " Telah Ditanggapi oleh $ditanggapiOleh "
                . $object->user_detail->name . " dengan pesan sebagai berikut : <br> <br>
            <h4> $object->message </h4>",
                $senderName,
            ));

            return back()->with(["success" => "Berhasil Menambahkan Diskusi"]);
        } else {
            return back()->with(["error" => "Gagal Menambahkan Diskusi"]);
        }
    }
}
