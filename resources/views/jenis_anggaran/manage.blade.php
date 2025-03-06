@extends('main.app')

@section('page-breadcrumb')
<h4 class="page-title text-dark font-weight-medium mb-1">Manage Jenis Anggaran</h4>
@endsection

@section('page-wrapper')
@include('main.components.message')

<div class="card border-primary">
    <div class="card-body">
        <h4 class="card-title">Jenis Anggaran</h4>
        <table class="table table-bordered table-responsive-lg">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Ditampilkan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->is_show ? 'Ya' : 'Tidak' }}</td>
                    <td>
                        <a href="{{ route('admin.jenis-anggaran.edit', $item->id) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('admin.jenis-anggaran.destroy', $item->id) }}" method="post" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
