<?php

namespace App\Http\Controllers;


use App\Models\JenisAnggaran;
use Illuminate\Http\Request;

class JenisAnggaranController extends Controller
{
    public function index()
    {
        $datas = JenisAnggaran::latest()->get();
        return view('jenis_anggaran.manage', compact('datas'));
    }

    public function create()
    {
        return view('jenis_anggaran.create');
    }

    public function store(Request $request)
    {
        $request->validate(['nama' => 'required|string|max:255']);
        JenisAnggaran::create($request->all());

        return redirect()->route('admin.jenis-anggaran.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit(JenisAnggaran $jenisAnggaran)
    {
        return view('jenis_anggaran.edit', compact('jenisAnggaran'));
    }

    public function update(Request $request, JenisAnggaran $jenisAnggaran)
    {
        $request->validate(['nama' => 'required|string|max:255']);
        $jenisAnggaran->update($request->all());

        return redirect()->route('admin.jenis-anggaran.index')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy(JenisAnggaran $jenisAnggaran)
    {
        $jenisAnggaran->delete();
        return redirect()->route('admin.jenis-anggaran.index')->with('success', 'Data berhasil dihapus');
    }
}
