<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RegistrasiController extends Controller
{
    
    public function store(Request $request){


        $object = new User();
        $object->name = $request->nama;
        $object->email = $request->email;
        $object->password = bcrypt($request->password);
        $object->role = 3;
        
        if ($object->save()) {
            return back()->with(["success" => "Registrasi Berhasil. Silakan Login Untuk Melanjutkan"]);
        } else {
            return back()->with(["error" => "Registrasi Gagal."]);
        }
        

    }

}
