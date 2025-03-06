<?php

namespace App\Http\Controllers;

use App\Models\TicketCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function viewCreate()
    {
        return view('category.add');
    }

    public function viewEdit($id)
    {
        $datas = TicketCategory::findOrFail($id);
        return view('category.edit')->with(compact('datas'));
    }

    public function viewManage()
    {
        $datas = TicketCategory::whereNull('is_deleted')->get();
        return view('category.manage')->with(compact('datas'));
    }

    public function store(Request $request)
    {
        $object = new TicketCategory();
        $object->name = $request->nama;

        if ($object->save()) {
            return back()->with(["success" => "Berhasil Menambahkan Data"]);
        } else {
            return back()->with(["error" => "Gagal Menambahkan Data"]);
        }
    }

    public function destroy($id)
    {
        $object = TicketCategory::find($id);
        $object->is_deleted = 1;

        if ($object->save()) {
            return back()->with(["success" => "Berhasil Mengupdate Data"]);
        } else {
            return back()->with(["error" => "Gagal Mengupdate Data"]);
        }
    }

    public function update($id,Request $request)
    {
        $object = TicketCategory::find($id);
        $object->name = $request->nama;

        if ($object->save()) {
            return back()->with(["success" => "Berhasil Mengupdate Data"]);
        } else {
            return back()->with(["error" => "Gagal Mengupdate Data"]);
        }
    }

}
