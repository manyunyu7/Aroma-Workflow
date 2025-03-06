@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Ticket</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Ticket</li>
                        <li class="breadcrumb-item text-muted" aria-current="page">{{ $ticket_status }} Ticket</li>
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


    @include('main.components.message')


    <div class="card border-primary">
        <div class="card-header bg-primary">
            <h4 class="mb-0 text-white">{{ $ticket_status }} Ticket</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table_data" class="table table-hover table-bordered display no-wrap" style="width:100%">
                    <thead class="">
                    <tr>
                        <th>No</th>
                        <th>No Ticket</th>
                        <th>Prioritas</th>
                        <th>Judul Ticket</th>
                        <th>Durasi Hingga Selesai</th>
                        <th>Deskripsi Ticket</th>
                        <th>Status</th>
                        <th>Operator Ticket</th>
                        <th>Detail Data</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($tickets as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->nomor_ticket }}</td>
                            <td>{{ $item->priority }}</td>
                            <td>{{ $item->ticket_title }}</td>
                            <td>{{ $item->duration_det }}</td>
                            <td>{{ $item->ticket_detail }}</td>
                            <td>
                                @if ($item->status == 3)
                                    <button type="button" id="{{ $item->id }}"
                                            class="btn waves-effect btn-delegate waves-light btn-rounded btn-warning">
                                        Pending
                                    </button>
                                @endif
                                @if ($item->status == 1)
                                    <button type="button" id="{{ $item->id }}"
                                            class="btn waves-effect btn-delegate waves-light btn-rounded btn-success">
                                        Completed
                                    </button>
                                @endif
                                @if ($item->status == 2)
                                    <button type="button" id="{{ $item->id }}"
                                            class="btn waves-effect btn-delegate waves-light btn-rounded btn-primary">
                                        Progress
                                    </button>
                                @endif
                            </td>

                            @if ($item->operator != null)
                                <td>{{ $item->operator->name }} <br>
                                    <span id="{{ $item->id }}" class="text-primary btn-delegate"
                                          style="cursor:pointer">(Ganti)</span>
                                </td>


                            @endif

                            @if ($item->operator == null)
                                @if (Auth::user()->role == 2)
                                    <td>
                                        {{$item->operator}}
                                    </td>
                                @else
                                    <td>
                                        <button type="button" id="{{ $item->id }}"
                                                class="btn btn-delegate btn-block waves-light btn-rounded btn-light txt-dark">
                                            Proses
                                        </button>
                                    </td>
                                @endif

                            @endif
                            <td>
                                <div class="d-flex">
                                    <button id="{{ $item->id }}" type="button"
                                            class="btn btn-danger btn-delete mr-2">Batalkan Ticket
                                    </button>
                                    <a href="{{ url('admin/ticket' . '/' . $item->id . '/edit') }}">
                                        <button type="button" class="btn btn-primary">Beri Respon</button>
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


    <!-- Destroy Modal -->
    <div class="modal fade" id="destroy-modal" tabindex="-1" role="dialog" aria-labelledby="destroy-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="destroy-modalLabel">Apakah Anda Yakin Ingin Membatalkan Ticket Ini ,
                        <br>
                        (Ticket tidak dapat dibuka dapat dibaca kembali)?</h5>

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


    <!-- Modal -->
    <div class="modal fade" id="modal-delegate" tabindex="-1" role="dialog" aria-labelledby="modelTitleId"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delegasi / Handover Ticket</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('/ticket/delegate') }}" method="post">
                        @csrf
                        <input type="hidden" id="id_ticket_delegate" name="id">
                        Delegasikan Ticket Kepada :
                        <div class="form-group">
                            <label for="">Operator Ticket</label>
                            <select required class="form-control" name="delegate_id" id="">
                                @forelse ($operators as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @empty

                                @endforelse
                            </select>

                            <button type="submit" id="" class="btn mt-4 btn-primary btn-block">Handover Ticket</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>



@endsection


@section('app-script')
    <script type="text/javascript"
            src="https://cdn.datatables.net/v/bs4-4.1.1/jszip-2.5.0/dt-1.10.23/b-1.6.5/b-colvis-1.6.5/b-flash-1.6.5/b-html5-1.6.5/b-print-1.6.5/cr-1.5.3/r-2.2.7/sb-1.0.1/sp-1.2.2/datatables.min.js">
    </script>
    <script type="text/javascript" charset="utf8"
            src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js">
    </script>
    <script type="text/javascript" charset="utf8"
            src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js">
    </script>
    <script type="text/javascript" charset="utf8"
            src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js">
    </script>




    <script type="text/javascript">
        $(function () {
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
                        title: 'Data Ticket Export {{ \Carbon\Carbon::now()->year }}'
                    },
                    'csvHtml5',
                ],

            });

            $('body').on("click", ".btn-delete", function () {
                var id = $(this).attr("id")
                $(".btn-destroy").attr("href", window.location.origin + "/admin/ticket/" + id + "/delete")
                $("#destroy-modal").modal("show")
            });

            $('body').on("click", ".btn-delegate", function () {
                var id = $(this).attr("id")
                document.getElementById("id_ticket_delegate").value = id;
                $("#modal-delegate").modal("show")
            });

            $('body').on("click", ".btn-add-new", function () {
                var id = $(this).attr("id")
                $(".btn-destroy").attr("id", id)
                $("#insert-modal").modal("show")
            });


        });
    </script>




@endsection
