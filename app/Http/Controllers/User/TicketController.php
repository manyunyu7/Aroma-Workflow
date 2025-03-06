<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
use App\Models\TicketCategory;
use App\Models\TicketModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{

    public function viewCreate()
    {
        $categories = TicketCategory::whereNull('is_deleted')->get();
        return view('ticket.add')->with(compact('categories'));
    }

    public function viewDetail($id){
        $data = TicketModel::find($id);
        $user = User::find($data->sender_id);
        $discussions= Discussion::where('topic','=',$id)->get();

        return view('ticket.edit')->with(compact('data','user','discussions'));
    }


    public function viewUserPending()
    {
        $tickets = TicketModel::where('sender_id', '=', Auth::user()->id)
            ->where('status', '=', 3)->get();
        $ticket_status = "Pending";
        return view('ticket.manage')->with(compact('ticket_status', 'tickets'));
    }

    public function viewUserProgress()
    {
        $tickets = TicketModel::where('sender_id', '=', Auth::user()->id)
            ->where('status', '=', 2)->get();
        $ticket_status = "Progress";
        return view('ticket.manage')->with(compact('ticket_status', 'tickets'));
    }
    public function viewUserComplete()
    {
        $tickets = TicketModel::where('sender_id', '=', Auth::user()->id)
            ->where('status', '=', 1)->get();
        $ticket_status = "Complete";
        return view('ticket.manage')->with(compact('ticket_status', 'tickets'));
    }

    public function destroy(Request $request){
        $object = TicketModel::find($request->id);
        $object->status="99";

        if ($object->save()) {
            return redirect('/user/ticket/pending')->with(["success" => "Berhasil Menghapus Ticket"]);
        } else {
            return redirect('/user/ticket/pending')->with(["error" => "Gagal Menghapus Ticket"]);
        }

    }

    public function store(Request $request)
    {
        $rules = [
            "title_ticket" => "required",
            "message" => "required",
        ];

        $customMessages = [
            'required' => 'Mohon Isi Kolom :attribute terlebih dahulu'
        ];

        $this->validate($request, $rules, $customMessages);

        $object = new TicketModel();

        if ($request->hasFile('image')) {
            $file = $request->file('icon');
            $extension = $file->getClientOriginalExtension(); // you can also use file name
            $fileName = time() . '.' . $extension;

            $savePath = "/web_files/category/";
            $savePathDB = "$savePath.$fileName";
            $path = public_path() . "$savePath";
            $upload = $file->move($path, $fileName);

            $object->category_name = $request->title;
            $object->photo_path = $savePathDB;
            $object->save();
        }

        $object->sender_id = Auth::user()->id;
        $object->ticket_title = $request->title_ticket;
        $object->category= $request->category;
        $object->priority= $request->prioritas;
        $object->ticket_detail = $request->message;
        $object->status = 3;

        if ($object->save()) {

            $objectdis = new Discussion();
            $objectdis->id_sender = Auth::user()->id;
            $objectdis->message = $request->message;
            $objectdis->topic = $object->id;
            $objectdis->save();


            return back()->with(["success" => "Berhasil Mengirim Ticket, Silakan Lihat Status pada Menu Tracking"]);
        } else {
            return back()->with(["error" => "Gagal Mengirim Ticket"]);
        }
    }
}
