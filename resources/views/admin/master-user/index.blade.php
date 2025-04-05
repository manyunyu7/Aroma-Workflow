@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Master User</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Master User</li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Manage</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="col-5 align-self-center">
            <div class="customize-input float-right">
                <a href="{{ route('admin.master-user.create') }}" class="btn btn-primary">Tambah Master User</a>
            </div>
        </div>
    </div>
@endsection

@section('page-wrapper')

    @include('main.components.message')

    <div class="card border-primary">
        <div class="card-body">
            <h4 class="card-title">Master Users</h4>
            <div class="table-responsive">
                <table id="table_data" class="table table-hover table-bordered display no-wrap" style="width:100%">
                    <thead class="">
                        <tr>
                            <th>No</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Unit Kerja</th>
                            <th>Jabatan</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Created By</th>
                            <th>Edited Date</th>
                            <th>Edited By</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($masterUsers as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $user->nik }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->unit_kerja }}</td>
                                <td>{{ $user->jabatan }}</td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        <span class="badge badge-info">{{ $role->role }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @if ($user->status == 'Active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Not Active</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d-M-Y') }}</td>
                                <td>{{ $user->created_by }}</td>
                                <td>{{ $user->updated_at != $user->created_at ? $user->updated_at->format('d-M-Y') : '-' }}</td>
                                <td>{{ $user->edited_by ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.master-user.edit', $user->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('admin.master-user.destroy', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">No master users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('app-script')
    <script type="text/javascript"
        src="https://cdn.datatables.net/v/bs4-4.1.1/jszip-2.5.0/dt-1.10.23/b-1.6.5/b-colvis-1.6.5/b-flash-1.6.5/b-html5-1.6.5/b-print-1.6.5/cr-1.5.3/r-2.2.7/sb-1.0.1/sp-1.2.2/datatables.min.js">
    </script>

    <script type="text/javascript">
        $(function() {
            $('#table_data').DataTable({
                processing: true,
                serverSide: false,
                columnDefs: [{
                    orderable: true,
                    targets: 0
                }],
                dom: 'T<"clear">lfrtip<"bottom"B>',
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5'],
            });
        });
    </script>
@endsection
