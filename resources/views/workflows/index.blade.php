@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Workflow Approval</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Workflow</li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Manage</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="col-5 align-self-center">
            <div class="customize-input float-right">
                <a href="{{ route('workflows.create') }}" class="btn btn-primary">Tambah Workflow</a>
            </div>
        </div>
    </div>
@endsection

@section('page-wrapper')

    @include('main.components.message')

    <div class="card border-primary">
        <div class="card-body">
            <h4 class="card-title">Workflows</h4>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="filter_unit_kerja">Unit Kerja</label>
                    <select id="filter_unit_kerja" class="form-control">
                        <option value="">-- Semua Unit Kerja --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter_jenis_anggaran">Jenis Anggaran</label>
                    <select id="filter_jenis_anggaran" class="form-control">
                        <option value="">-- Semua Jenis Anggaran --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter_status">Status</label>
                    <select id="filter_status" class="form-control">
                        <option value="">-- Semua Status --</option>
                        <option value="Draft Creator">Draft Creator</option>
                        <option value="Draft Reviewer">Draft Reviewer</option>
                        <option value="Waiting">Waiting Approval</option>
                        <option value="Digital Signing">Digital Signing</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table id="table_data" class="table table-hover table-bordered display no-wrap" style="width:100%">
                    <thead class="">
                        <tr>
                            <th>No</th>
                            <th>Nomor Pengajuan</th>
                            <th>Unit Kerja</th>
                            <th>Nama Kegiatan</th>
                            <th>Jenis Anggaran</th>
                            <th>Total Nilai</th>
                            <th>Creator</th>
                            <th>Reviewer</th>
                            <th>Disetujui</th>
                            <th>Waktu Penggunaan</th>
                            <th>Cost Center</th>
                            <th>Account</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($workflows as $workflow)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $workflow->nomor_pengajuan }}</td>
                                <td>{{ $workflow->unit_kerja }}</td>
                                <td>{{ $workflow->nama_kegiatan }}</td>
                                <td>{{ $workflow->jenisAnggaran->nama }}</td>
                                <td>{{ number_format($workflow->total_nilai, 0, ',', '.') }}</td>
                                <!-- Creator -->
                                <td>
                                    @php
                                        $creator = $workflow->approvals->firstWhere('role', 'CREATOR');
                                    @endphp
                                    @if ($creator)
                                        <span class="badge badge-info">
                                            <i class="fas fa-user"></i> {{ $creator->user->name }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>

                                <!-- Reviewer -->
                                <td>
                                    @foreach ($workflow->approvals->whereIn('role', ['REVIEWED_BY_MAKER', 'REVIEWED_BY_APPROVER']) as $reviewer)
                                        <div class="mb-1">
                                            <span class="badge badge-warning">
                                                <i class="fas fa-user-check"></i> {{ $reviewer->user->name }}
                                            </span>
                                        </div>
                                    @endforeach
                                </td>

                                <!-- Disetujui -->
                                <td>
                                    @foreach ($workflow->approvals->whereIn('role', ['APPROVED_BY_HEAD_UNIT', 'ACKNOWLEDGED_BY_SPV']) as $approval)
                                        <div class="mb-1">
                                            <span class="badge badge-success">
                                                <i class="fas fa-user-shield"></i> {{ $approval->user->name }}
                                            </span>
                                        </div>
                                    @endforeach
                                </td>

                                <td>{{ $workflow->waktu_penggunaan }}</td>
                                <td>{{ $workflow->cost_center }}</td>
                                <td>{{ $workflow->account }}</td>
                                <td>Waiting Approval</td>
                                <td>
                                    <a href="{{ route('workflows.show', $workflow->id) }}"
                                        class="btn btn-info btn-sm">Detail</a>
                                    <a href="{{ route('workflows.edit', $workflow->id) }}"
                                        class="btn btn-warning btn-sm">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center">Tidak ada data workflow.</td>
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


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let table = document.getElementById("table_data");
            let rows = Array.from(table.getElementsByTagName("tbody")[0].getElementsByTagName("tr"));

            let unitKerjaFilter = document.getElementById("filter_unit_kerja");
            let jenisAnggaranFilter = document.getElementById("filter_jenis_anggaran");
            let statusFilter = document.getElementById("filter_status");

            // Ambil data unik untuk filter dari tabel
            let unitKerjaSet = new Set();
            let jenisAnggaranSet = new Set();

            rows.forEach(row => {
                let unitKerja = row.cells[2].innerText.trim();
                let jenisAnggaran = row.cells[4].innerText.trim();

                unitKerjaSet.add(unitKerja);
                jenisAnggaranSet.add(jenisAnggaran);
            });

            // Isi dropdown filter secara dinamis
            unitKerjaSet.forEach(unit => {
                let option = new Option(unit, unit);
                unitKerjaFilter.add(option);
            });

            jenisAnggaranSet.forEach(jenis => {
                let option = new Option(jenis, jenis);
                jenisAnggaranFilter.add(option);
            });

            // Event listener untuk filter
            function filterTable() {
                let unitKerjaValue = unitKerjaFilter.value.toLowerCase();
                let jenisAnggaranValue = jenisAnggaranFilter.value.toLowerCase();
                let statusValue = statusFilter.value.toLowerCase();

                rows.forEach(row => {
                    let unitKerja = row.cells[2].innerText.toLowerCase().trim();
                    let jenisAnggaran = row.cells[4].innerText.toLowerCase().trim();
                    let status = row.cells[12].innerText.toLowerCase().trim();

                    let show =
                        (unitKerjaValue === "" || unitKerja.includes(unitKerjaValue)) &&
                        (jenisAnggaranValue === "" || jenisAnggaran.includes(jenisAnggaranValue)) &&
                        (statusValue === "" || status.includes(statusValue));

                    row.style.display = show ? "" : "none";
                });
            }

            // Pasang event listener ke dropdown filter
            unitKerjaFilter.addEventListener("change", filterTable);
            jenisAnggaranFilter.addEventListener("change", filterTable);
            statusFilter.addEventListener("change", filterTable);
        });
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
