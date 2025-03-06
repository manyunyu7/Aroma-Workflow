@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Ticket</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Edit Data Ticket</li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Edit Ticket</li>
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


    <div class="card border-success">
        <div class="card-header bg-primary">
            <h4 class="mb-0 text-white">Edit Data Ticket {{ $data->nomor_ticket }}</h4>
        </div>
        <div class="card-body">

            @csrf

            <h1>Nomor Ticket {{$data->nomor_ticket}}</h1>

            <h3 class="text-dark mr-1 mt-2"><strong> Durasi Pengerjaan : </strong></h3>
            <h3 class="text-dark">
                {{$data->duration_det}}
            </h3>

            <div class="d-flex">
                <h3 class="text-dark mr-2"><strong> Judul Keluhan : </strong></h3>
                <h3 class="text-dark">{{ $data->ticket_title }}</h3>
            </div>
            <h3 class="text-dark mr-1"><strong> Message Ticket : </strong></h3>
            <h3 class="text-dark">{{ $data->ticket_detail }}</h3>

            <h4 class="text-dark">Status Ticket : </h4>

            <div class="mt-1">
                @if ($data->status == 3)
                    <button id="{{ $data->id }}" type="button" class="btn btn-danger btn-delegate">Pending</button>
                @endif
                @if ($data->status == 1)
                    <button id="{{ $data->id }}" type="button" class="btn btn-success btn-delegate">Completed</button>
                @endif
                @if ($data->status == 2)
                    <button id="{{ $data->id }}" type="button" class="btn btn-primary btn-delegate">Progress</button>
                @endif
            </div>

            <h3 class="text-dark mr-1 mt-2"><strong> Pengirim : </strong></h3>
            <h3 class="text-dark">{{ $user->name }}</h3>

            <h3 class="text-dark mr-1 mt-2"><strong> Kategori Ticket : </strong></h3>
            <h3 class="text-dark">
                {{$data->category_detail->name}}
            </h3>


            <h3 class="text-dark mr-1"><strong> Tanggal Dibuat : </strong></h3>
            <h3 class="text-dark">{{ $data->created_at }}</h3>


            <div class="border p-2 round-1">
                <h3 class="text-dark mr-1 mt-2"><strong>Ticket Ini Sedang Ditangani Oleh : </strong></h3>
                @if ($data->delegate_id == null)
                    <h5> Ticket Ini Belum Ditakeover atau didelegasi oleh operator manapun </h5>
                @else
                    <h3> {{ $data->operator->name }} </h3>
                @endif

                @if (Auth::user()->role != 3)
                    <button type="button" id="{{ $data->id }}" class="btn btn-primary btn-delegate">Handover/Delegasikan
                        Ticket
                    </button>
                @endif

            </div>

            @if (Auth::user()->role == 1 || Auth::user()->role == 2)

                <form action="{{ url('admin/ticket/' . $data->id . '/update_status') }}" method="post"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <h3 class="text-dark mt-4">Ubah Status Ticket</h3>
                        <select required class="form-control" name="status" id="">
                            <option @if($data->status==3) selected @endif value="3">Pending</option>
                            <option @if($data->status==2) selected @endif value="2">Progress</option>
                            <option @if($data->status==1) selected @endif value="1">Selesai</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <h3 class="text-dark mt-4">Ubah Prioritas</h3>
                        <select required class="form-control" name="priority" id="">
                            <option>Pilih Prioritas</option>
                            <option @if($data->priority=="HIGH") selected @endif value="HIGH">HIGH 🔥</option>
                            <option @if($data->priority=="MEDIUM") selected @endif value="MEDIUM">MEDIUM</option>
                            <option @if($data->priority=="LOW") selected @endif value="LOW">LOW</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Status</button>
                </form>
                <hr>
            @endif


        </div>
    </div>



    <div class="card border-success">
        <div class="card-header bg-dark">
            <h4 class="mb-0 text-white">Tambahkan Tanggapan</h4>
        </div>
        <div class="card-body">
            <div class="chat-box scrollable position-relative ps-container ps-theme-default"
                 style="height: calc(100vh - 100px);" data-ps-id="1456ff32-4cc1-fff2-5065-9da0363bf007">
                <ul class="chat-list list-style-none ">

                    @forelse ($discussions as $item)

                        @if ($item->user_detail == null)
                            <li class="chat-item list-style-none mt-3">
                                <div class=" d-inline-block pl-3">
                                    <div class="msg d-inline-block mb-1 chat-text">
                                        <h6> {{ $item->message }} pada {{ $item->created_at }} </h6>
                                    </div>
                                </div>
                            </li>
                        @else

                            @if ($item->user_detail->role == 2 || $item->user_detail->role == 1)
                            <!--chat Row admin -->
                                <li class="chat-item list-style-none mt-3">
                                    <div class="chat-content d-inline-block pl-3">
                                        <h6 class="font-weight-medium">{{ $item->user_detail->name }} (Operator)</h6>
                                        <div class="msg p-2 d-inline-block mb-1  chat-text">
                                            <h4> {{ $item->message }} </h4>
                                        </div>
                                        <h6 class="">{{ $item->created_at }}</h6>
                                    </div>
                                </li>
                            @endif
                            @if ($item->user_detail->role == 3)
                                <li class="chat-item odd list-style-none mt-3">
                                    <div class="chat-content d-inline-block pl-3">
                                        <h6 class="font-weight-medium"> {{ $item->user_detail->name }}</h6>
                                        <div class="msg p-2 d-inline-block mb-1 chat-text">
                                            <h4> {{ $item->message }} </h4>
                                        </div>
                                        <h6 class="">{{ $item->created_at }}</h6>
                                    </div>
                                </li>
                            @endif

                        @endif


                    @empty

                        <div class="alert alert-primary" role="alert">
                            <strong>Belum Ada Diskusi/Tanggapan, Silakan Menambahkan Pesan Melalui Kolom
                                Dibawah</strong>
                        </div>
                    @endforelse


                </ul>
                <div class="ps-scrollbar-x-rail" style="left: 0px; bottom: 0px;">
                    <div class="ps-scrollbar-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                </div>
                <div class="ps-scrollbar-y-rail" style="top: 0px; right: 3px;">
                    <div class="ps-scrollbar-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                </div>
            </div>

            <form id="chat-submit" action="{{ url('ticket/discussion' . '/' . $data->id . '/post') }}" method="post">
                @csrf
                <input type="hidden" name="topic" value="{{ $data->id }}">
                <div class="card-body border-top">
                    <div class="row">
                        <div class="col-9">


                            @if (Auth::user()->role == 2 && $data->delegate_id != null)
                                @if (Auth::user()->id == $data->delegate_id)
                                    <div class="input-field mt-0 mb-0">
                                        <input required id="textarea1" name="message" placeholder="Type and enter"
                                               class="form-control border-0" type="text">
                                    </div>
                                @else
                                    <input type="number" name="" class="d-none" required id="">
                                    <p>Kolom Diskusi Ini Hanya Bisa Dijawab Oleh {{ $data->operator->name }}</p>
                                @endif
                            @else

                                @if (Auth::user()->role == 2 && $data->delegate_id == null)
                                    <input type="number" name="" class="d-none" required id="">
                                    <p>Silakan Delegasikan Ticket ke Diri Anda Untuk Merespon Ticket</p>
                                @else
                                    <div class="input-field mt-0 mb-0">
                                        <input required id="textarea1" name="message" placeholder="Type and enter"
                                               class="form-control border-0" type="text">
                                    </div>
                                @endif
                            @endif

                        </div>
                        <div class="col-3">
                            @if (Auth::user()->role == 2 && $data->delegate_id != null)
                                @if (Auth::user()->id == $data->delegate_id)
                                    <a class="btn-circle btn-lg btn-cyan float-right text-white" href="#"
                                       onclick="document.getElementById('chat-submit').submit();;return false;">
                                        <i class="fas fa-paper-plane"></i></a>
                                @else
                                    <input type="number" name="" class="d-none" required id="">
                                    <p>Kolom Diskusi Ini Hanya Bisa Dijawab Oleh {{ $data->operator->name }}</p>
                                @endif
                            @else
                                @if (Auth::user()->role == 2 && $data->delegate_id == null)

                                @else
                                    <a class="btn-circle btn-lg btn-cyan float-right text-white" href="#"
                                       onclick="document.getElementById('chat-submit').submit();;return false;">
                                        <i class="fas fa-paper-plane"></i></a>
                                @endif



                            @endif

                        </div>
                    </div>
                </div>
            </form>
            <hr>

        </div>
    </div>



    @if (Auth::user()->role == 1 || Auth::user()->role == 2)
        <!-- Handover Modal -->
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

                                <button type="submit" id="" class="btn mt-4 btn-primary btn-block">Handover Ticket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>

    @endif






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
                serverSide: true,
                columnDefs: [{
                    orderable: true,
                    targets: 0
                }],
                dom: 'T<"clear">lfrtip<"bottom"B>',
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],

            });


            $('body').on("click", ".btn-add-new", function () {
                var id = $(this).attr("id")
                $(".btn-destroy").attr("id", id)
                $("#insert-modal").modal("show")
            });


            $('body').on("click", ".btn-delegate", function () {
                var id = $(this).attr("id")
                document.getElementById("id_ticket_delegate").value = id;
                $("#modal-delegate").modal("show")
            });


            // Edit & Update
            $('body').on("click", ".btn-edit", function () {
                var id = $(this).attr("id")
                $.ajax({
                    url: "{{ URL::to('/') }}/mutabaah/" + id + "/fetch",
                    method: "GET",
                    success: function (response) {
                        $("#edit-modal").modal("show")
                        console.log(response)
                        $("#id").val(response.id)
                        $("#name").val(response.judul)
                        $("#edit_date").val(response.tanggal)
                        $("#role").val(response.role)
                    }
                })
            });

            // Reset Password
            $('body').on("click", ".btn-res-pass", function () {
                var id = $(this).attr("id")
                $(".btn-reset").attr("id", id)
                $("#reset-password-modal").modal("show")
            });

        });
    </script>




@endsection
