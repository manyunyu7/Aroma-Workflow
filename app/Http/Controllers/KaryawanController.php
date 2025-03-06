<?php

namespace App\Http\Controllers;

use App\Models\User;
use Facade\FlareClient\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class KaryawanController extends Controller
{
    public function viewAddKaryawan()
    {
        return view('karyawan.add');
    }

    public function viewEdit($id)
    {
        $karyawan = User::findOrFail($id);
        return view('karyawan.edit')->with(compact('karyawan'));
    }

    public function viewManage()
    {
        $user = User::all();
        return view('karyawan.manage')->with(compact('user'));
    }

    public function destroy($id)
    {
        if ($id == Auth::user()->id) {
            return back()->with(["error" => "Anda Tidak dapat menghapus akun milik sendiri"]);
        }
        $karyawan = User::findOrFail($id);
        if ($karyawan->delete()) {
            return back()->with(["success" => "Berhasil Menghapus Data Karyawan"]);
        } else {
            return back()->with(["error" => "Gagal Menghapus Data Karyawan"]);
        }
    }


    public function edit(Request $request, $id)
    {
        $rules = [
            'nama' => 'required',
            'nip' => ['required', Rule::unique('users', 'nip')->ignore($id)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
        ];

        $customMessages = [
            'required' => 'Mohon Isi Kolom :attribute terlebih dahulu',
            'unique' => ':attribute sudah digunakan, silakan gunakan yang lain.'
        ];

        $this->validate($request, $rules, $customMessages);

        $object = User::findOrFail($id);
        $object->name = $request->nama;
        $object->nip = $request->nip;
        $object->email = $request->email;
        $object->role = $request->role;

        $object->save();

        return back()->with(["success" => "Berhasil Menyimpan Perubahan"]);
    }



    public function store(Request $request)
    {

        $rules = [
            'nama' => 'required',
            'nip' => 'required|unique:users,nip',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:5',
        ];


        $customMessages = [
            'required' => 'Mohon Isi Kolom :attribute terlebih dahulu'
        ];

        $this->validate($request, $rules, $customMessages);


        $object = new User();
        $object->name = $request->nama;
        $object->email = $request->email;
        $object->nip = $request->nip;
        $object->password = $request->password;
        $object->role = $request->role;
        $object->save();

        if ($object) {
            return back()->with(["success" => "Berhasil Menambah User Baru"]);
        } else {
            return back()->with(["error" => "Gagal Menambah User Baru"]);
        }
    }
}
