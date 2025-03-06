@extends('main.app')

@section('page-breadcrumb')
<h4 class="page-title text-dark font-weight-medium mb-1">Tambah Jenis Anggaran</h4>
@endsection

@section('page-wrapper')
@include('main.components.message')

<div class="card border-success">
    <div class="card-header bg-success text-white">
        <h4 class="mb-0">Tambah Jenis Anggaran</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.jenis-anggaran.store') }}" method="post">
            @csrf
            <div class="form-group">
                <label for="">Nama Jenis Anggaran</label>
                <input type="text" class="form-control" required name="nama" placeholder="Masukkan nama">
            </div>

            <div class="form-group">
                <label for="">Tampilkan?</label>
                <select class="form-control" name="is_show">
                    <option value="1">Ya</option>
                    <option value="0">Tidak</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Tambahkan</button>
        </form>
    </div>
</div>
@endsection
