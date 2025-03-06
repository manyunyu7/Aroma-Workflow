@extends('main.app')

@section('page-breadcrumb')
<h4 class="page-title text-dark font-weight-medium mb-1">Edit Jenis Anggaran</h4>
@endsection

@section('page-wrapper')
@include('main.components.message')

<div class="card border-warning">
    <div class="card-header bg-warning text-white">
        <h4 class="mb-0">Edit Jenis Anggaran</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.jenis-anggaran.update', $jenisAnggaran->id) }}" method="post">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="">Nama Jenis Anggaran</label>
                <input type="text" class="form-control" required name="nama" value="{{ $jenisAnggaran->nama }}">
            </div>

            <div class="form-group">
                <label for="">Tampilkan?</label>
                <select class="form-control" name="is_show">
                    <option value="1" {{ $jenisAnggaran->is_show ? 'selected' : '' }}>Ya</option>
                    <option value="0" {{ !$jenisAnggaran->is_show ? 'selected' : '' }}>Tidak</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection
