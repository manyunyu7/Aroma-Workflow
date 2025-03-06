@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Selamat Datang
                {{ Auth::user()->name }}</h3>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item"><a href="index.html">Dashboard</a>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="col-5 align-self-center">
            <div class="customize-input float-right">

            </div>
        </div>
    </div>

@endsection

@section('page-wrapper')
    <!-- ============================================================== -->
    <!-- Bread crumb and right sidebar toggle -->
    <!-- ============================================================== -->


    <!-- ============================================================== -->
    <!-- End Bread crumb and right sidebar toggle -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- *************************************************************** -->
        <!-- Start First Cards -->
        <!-- *************************************************************** -->
        <div class="card-group">
            <div class="card border-right">
                <div class="card-body">
                    <div class="d-flex d-lg-flex d-md-block align-items-center">
                        <div>
                            <div class="d-inline-flex align-items-center">
                                <h2 class="text-dark mb-1 font-weight-medium">{{ $totalUser }}</h2>
                                {{-- <span class="badge bg-primary font-12 text-white font-weight-medium badge-pill ml-2 d-lg-block d-md-none">+18.33%</span> --}}
                            </div>
                            <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Jumlah Pengguna</h6>
                        </div>
                        <div class="ml-auto mt-md-3 mt-lg-0">
                            <span class="opacity-7 text-muted"><i data-feather="user-plus"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card border-right">
                <div class="card-body">
                    <div class="d-flex d-lg-flex d-md-block align-items-center">
                        <div>
                            <h2 class="text-dark mb-1 w-100 text-truncate font-weight-medium">{{ $totalUserUser }}</h2>
                            <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">User
                            </h6>
                        </div>
                        <div class="ml-auto mt-md-3 mt-lg-0">
                            <span class="opacity-7 text-muted"><i data-feather="user-plus"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card border-right">
                <div class="card-body">
                    <div class="d-flex d-lg-flex d-md-block align-items-center">
                        <div>
                            <div class="d-inline-flex align-items-center">
                                <h2 class="text-dark mb-1 font-weight-medium">{{ $totalUserOperator }}</h2>
                                {{-- <span class="badge bg-danger font-12 text-white font-weight-medium badge-pill ml-2 d-md-none d-lg-block">-18.33%</span> --}}
                            </div>
                            <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Operator</h6>
                        </div>
                        <div class="ml-auto mt-md-3 mt-lg-0">
                            <span class="opacity-7 text-muted"><i data-feather="user-plus"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex d-lg-flex d-md-block align-items-center">
                        <div>
                            <h2 class="text-dark mb-1 font-weight-medium">{{ $totalUserAdmin }}</h2>
                            <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Admin</h6>
                        </div>
                        <div class="ml-auto mt-md-3 mt-lg-0">
                            <span class="opacity-7 text-muted"><i data-feather="globe"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- *************************************************************** -->
        <!-- End First Cards -->
        <!-- *************************************************************** -->


        <div class="row">
            <!-- Column -->
            <div class="col-md-6 col-lg-4 col-xlg-3">
                <div class="card card-hover">
                    <div class="p-2 bg-primary text-center">
                        <h1 class="font-light text-white">{{ $totalTicket }}</h1>
                        <h6 class="text-white">Total Tickets</h6>
                    </div>
                </div>
            </div>
            <!-- Column -->
            <div class="col-md-6 col-lg-2 col-xlg-3">
                <div class="card card-hover">
                    <div class="p-2 bg-cyan text-center">
                        <h1 class="font-light text-white">{{ $totalTicketProgress }}</h1>
                        <h6 class="text-white">Diproses</h6>
                    </div>
                </div>
            </div>
            <!-- Column -->
            <div class="col-md-6 col-lg-2 col-xlg-3">
                <div class="card card-hover">
                    <div class="p-2 bg-success text-center">
                        <h1 class="font-light text-white">{{ $totalTicketSolved }}</h1>
                        <h6 class="text-white">Completed</h6>
                    </div>
                </div>
            </div>
            <!-- Column -->
            <div class="col-md-6 col-lg-2 col-xlg-3">
                <div class="card card-hover">
                    <div class="p-2 bg-warning text-center">
                        <h1 class="font-light text-white">{{ $totalTicketPending }}</h1>
                        <h6 class="text-white">Pending</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-2 col-xlg-3">
                <div class="card card-hover">
                    <div class="p-2 bg-danger text-center">
                        <h1 class="font-light text-white">{{ $totalTicketCanceled }}</h1>
                        <h6 class="text-white">Dibatalkan</h6>
                    </div>
                </div>
            </div>
            <!-- Column -->
        </div>

        <!-- *************************************************************** -->
        <!-- Start Sales Charts Section -->
        <!-- *************************************************************** -->
        <div class="row">

            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Jumlah Pengguna</h4>
                        <div id="user_graphic" class="mt-2" style="height:283px; width:100%;"></div>
                        <ul class="list-style-none mb-0">
                            <li>
                                <i class="fas fa-circle text-primary font-10 mr-2"></i>
                                <span class="text-muted">Admin</span>
                                <span class="text-dark float-right font-weight-medium">{{$totalUserAdmin}}</span>
                            </li>
                            <li class="mt-3">
                                <i class="fas fa-circle text-danger font-10 mr-2"></i>
                                <span class="text-muted">User</span>
                                <span class="text-dark float-right font-weight-medium">{{$totalUserUser}}</span>
                            </li>
                            <li class="mt-3">
                                <i class="fas fa-circle text-cyan font-10 mr-2"></i>
                                <span class="text-muted">Operator</span>
                                <span class="text-dark float-right font-weight-medium">{{$totalUserOperator}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Kategori Tiket</h4>
                        <div id="campaign-v2" class="mt-2" style="height:383px; width:100%;"></div>
                        <ul class="list-style-none mb-0">
                        </ul>
                    </div>
                </div>
            </div>


            <div class="col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Kategori Tiket</h4>
                        <div class="net-income mt-4 position-relative" style="height:294px;"></div>
                        <ul class="list-inline text-center mt-5 mb-2">
                            <li class="list-inline-item text-muted font-italic">Kategori Tiket</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">
            @include('main.components.message')
        </div>

        <div class="row col-12 d-none">
            <div class="card">
                <img class="card-img-top" src="holder.js/100x180/" alt="">
                <div class="card-body">
                    <h4 class="card-title">Semua Ticket</h4>
                    <div class="table-responsive">
                        <table id="table_data" class="table table-striped table-bordered no-wrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Judul Ticket</th>
                                    <th>Deskripsi Ticket</th>
                                    <th>Status</th>
                                    <th>Detail Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($tickets as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->ticket_title }}</td>
                                        <td>{{ $item->ticket_detail }}</td>
                                        <td>
                                            @if ($item->status == 3)
                                                <button type="button"
                                                    class="btn waves-effect waves-light btn-rounded btn-warning">Pending</button>
                                            @endif
                                            @if ($item->status == 1)
                                                <button type="button"
                                                    class="btn waves-effect waves-light btn-rounded btn-success">Completed</button>
                                            @endif
                                            @if ($item->status == 2)
                                                <button type="button"
                                                    class="btn waves-effect waves-light btn-rounded btn-primary">Progress</button>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <button id="{{ $item->id }}" type="button"
                                                    class="btn btn-danger btn-delete mr-2">Batalkan Ticket</button>
                                                <a href="{{ url('admin/ticket' . '/' . $item->id . '/edit') }}">
                                                    <button type="button" class="btn btn-primary">Edit</button>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                @empty

                                @endforelse

                            </tbody>
                            <tfoot>

                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>
        <!-- *************************************************************** -->
        <!-- End Sales Charts Section -->
        <!-- *************************************************************** -->
        <!-- *************************************************************** -->
        <!-- Start Location and Earnings Charts Section -->
        <!-- *************************************************************** -->

        <!-- *************************************************************** -->
        <!-- End Location and Earnings Charts Section -->
        <!-- *************************************************************** -->

    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->


    <!-- Destroy Modal -->
    <div class="modal fade" id="destroy-modal" tabindex="-1" role="dialog" aria-labelledby="destroy-modalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="destroy-modalLabel">Apakah Anda Yakin Ingin Membatalkan Ticket Ini ?
                    </h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <a class="btn-destroy" href="">
                        <button type="button" class="btn btn-danger">Hapus</button>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Destroy Modal -->

@endsection



@section('app-script')
<script type="text/javascript"
src="https://cdn.datatables.net/v/bs4-4.1.1/jszip-2.5.0/dt-1.10.23/b-1.6.5/b-colvis-1.6.5/b-flash-1.6.5/b-html5-1.6.5/b-print-1.6.5/cr-1.5.3/r-2.2.7/sb-1.0.1/sp-1.2.2/datatables.min.js">
</script>
<script type="text/javascript" charset="utf8"
src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js">
</script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js">
</script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js">
</script>


@include('home.script_admin')


<script type="text/javascript">
    $(function() {
        var table = $('#table_data').DataTable({
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
            buttons: [
                'copyHtml5',
                {
                    extend: 'excelHtml5',
                    title: 'Data Santri Export {{ \Carbon\Carbon::now()->year }}'
                },
                'csvHtml5',
            ],

        });

        $('body').on("click", ".btn-delete", function() {
            var id = $(this).attr("id")
            $(".btn-destroy").attr("href", window.location.origin + "/admin/ticket/" + id + "/delete")
            $("#destroy-modal").modal("show")
        });

        $('body').on("click", ".btn-add-new", function() {
            var id = $(this).attr("id")
            $(".btn-destroy").attr("id", id)
            $("#insert-modal").modal("show")
        });


    });
</script>
@endsection
