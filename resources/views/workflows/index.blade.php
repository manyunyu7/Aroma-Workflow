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

@push('styles')
    <style>
        .badge-waiting {
            background-color: #fd7e14;
            color: white;
        }
        .badge-draft {
            background-color: #6c757d;
            color: white;
        }
        .badge-completed {
            background-color: #28a745;
            color: white;
        }
        .filter-section {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
@endpush

@section('page-wrapper')

    @include('main.components.message')

    <div class="card border-primary">
        <div class="card-body">
            <h4 class="card-title">Workflows</h4>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="filter_unit_kerja">Unit Kerja</label>
                        <select id="filter_unit_kerja" class="form-control">
                            <option value="">-- Semua Unit Kerja --</option>
                            @php
                                $unitKerjas = $workflows->pluck('unit_kerja')->unique()->sort()->values()->all();
                            @endphp
                            @foreach($unitKerjas as $unit)
                                <option value="{{ $unit }}">{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter_jenis_anggaran">Jenis Anggaran</label>
                        <select id="filter_jenis_anggaran" class="form-control">
                            <option value="">-- Semua Jenis Anggaran --</option>
                            @php
                                $jenisAnggarans = $workflows->pluck('jenisAnggaran.nama')->unique()->sort()->values()->all();
                            @endphp
                            @foreach($jenisAnggarans as $jenis)
                                <option value="{{ $jenis }}">{{ $jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filter_status">Status</label>
                        <select id="filter_status" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <option value="DRAFT_CREATOR">Draft (Creator)</option>
                            <option value="DRAFT_REVIEWER">Draft (Reviewer)</option>
                            <option value="WAITING_APPROVAL">Waiting Approval</option>
                            <option value="DIGITAL_SIGNING">Digital Signing</option>
                            <option value="COMPLETED">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" id="search" class="form-control" placeholder="Search by nomor pengajuan, nama kegiatan...">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button id="resetFilters" class="btn btn-secondary btn-block">Reset Filters</button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="table_data" class="table table-hover table-bordered display no-wrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Pengajuan</th>
                            <th>Unit Kerja</th>
                            <th>Nama Kegiatan</th>
                            <th>Jenis Anggaran</th>
                            <th>Total Nilai</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Approvers</th>
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
                                <td>{{ $workflow->jenisAnggaran->nama ?? 'N/A' }}</td>
                                <td>{{ number_format($workflow->total_nilai, 0, ',', '.') }}</td>
                                <td>{{ $workflow->created_at->format('d M Y') }}</td>
                                <td>
                                    @php
                                        $statusColor = 'secondary';
                                        switch($workflow->status) {
                                            case 'WAITING_APPROVAL':
                                                $statusColor = 'warning';
                                                break;
                                            case 'COMPLETED':
                                                $statusColor = 'success';
                                                break;
                                            case 'DRAFT_CREATOR':
                                            case 'DRAFT_REVIEWER':
                                                $statusColor = 'secondary';
                                                break;
                                            case 'DIGITAL_SIGNING':
                                                $statusColor = 'primary';
                                                break;
                                        }
                                    @endphp
                                    <span class="badge badge-{{ $statusColor }}">
                                        {{ $workflow->formatted_status }}
                                    </span>

                                    <!-- Progress bar -->
                                    <div class="progress mt-1" style="height: 5px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                             style="width: {{ $workflow->progress_percentage }}%"
                                             aria-valuenow="{{ $workflow->progress_percentage }}"
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <!-- Approvers with status -->
                                    @foreach ($workflow->approvals as $approval)
                                        @php
                                            $user = \App\Models\User::find($approval->user_id);

                                            // Define badge color based on status
                                            $approvalColor = 'secondary';
                                            if ($approval->status === 'APPROVED') {
                                                $approvalColor = 'success';
                                            } elseif ($approval->status === 'REJECTED') {
                                                $approvalColor = 'danger';
                                            } elseif ($approval->status === 'PENDING' && $approval->is_active) {
                                                $approvalColor = 'warning';
                                            }
                                        @endphp

                                        <div class="mb-1">
                                            <span class="badge badge-{{ $approvalColor }}">
                                                <i class="fas fa-user"></i>
                                                {{ $user ? $user->name : 'Unknown' }}
                                                ({{ \App\Models\Workflow::getStatusName($approval->role) }})
                                            </span>
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('workflows.show', $workflow->id) }}"
                                        class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>

                                    @if($workflow->status === 'DRAFT_CREATOR' && $workflow->created_by === Auth::id())
                                        <a href="{{ route('workflows.edit', $workflow->id) }}"
                                            class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                        <form action="{{ route('workflows.destroy', $workflow->id) }}" method="POST"
                                              class="d-inline" onsubmit="return confirm('Are you sure you want to delete this draft?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    @endif

                                    @if($workflow->status === 'WAITING_APPROVAL')
                                        @php
                                            // Check if current user has an active approval
                                            $hasActiveApproval = $workflow->approvals->where('user_id', Auth::id())
                                                                 ->where('is_active', 1)
                                                                 ->where('status', 'PENDING')
                                                                 ->isNotEmpty();
                                        @endphp

                                        @if($hasActiveApproval)
                                            <a href="{{ route('workflows.show', $workflow->id) }}#approval"
                                               class="btn btn-success btn-sm"><i class="fas fa-check-circle"></i> Approve</a>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada data workflow.</td>
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
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#table_data').DataTable({
                processing: true,
                serverSide: false,
                dom: 'lBfrtip',
                buttons: ['copy', 'excel', 'pdf', 'print'],
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[6, 'desc']] // Sort by date column by default
            });

            // Filter function
            function applyFilters() {
                const unitKerja = $('#filter_unit_kerja').val().toLowerCase();
                const jenisAnggaran = $('#filter_jenis_anggaran').val().toLowerCase();
                const status = $('#filter_status').val();
                const search = $('#search').val().toLowerCase();

                table.search('').columns().search('').draw();

                // Apply filters using custom filtering
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        const rowUnitKerja = data[2].toLowerCase(); // Unit Kerja column
                        const rowJenisAnggaran = data[4].toLowerCase(); // Jenis Anggaran column
                        const rowStatus = data[7]; // Status column (contains HTML)
                        const rowNomor = data[1].toLowerCase(); // Nomor Pengajuan column
                        const rowNamaKegiatan = data[3].toLowerCase(); // Nama Kegiatan column

                        // Check if row status contains our status value
                        const statusMatch = status === '' || rowStatus.includes(status);

                        // Check unit kerja match
                        const unitMatch = unitKerja === '' || rowUnitKerja.includes(unitKerja);

                        // Check jenis anggaran match
                        const jenisMatch = jenisAnggaran === '' || rowJenisAnggaran.includes(jenisAnggaran);

                        // Check search text match
                        const searchMatch = search === '' ||
                            rowNomor.includes(search) ||
                            rowNamaKegiatan.includes(search);

                        return statusMatch && unitMatch && jenisMatch && searchMatch;
                    }
                );

                table.draw();

                // Remove the custom filtering function
                $.fn.dataTable.ext.search.pop();
            }

            // Attach event listeners to all filters
            $('#filter_unit_kerja, #filter_jenis_anggaran, #filter_status').change(applyFilters);
            $('#search').on('keyup', applyFilters);

            // Reset all filters
            $('#resetFilters').click(function() {
                $('#filter_unit_kerja, #filter_jenis_anggaran, #filter_status').val('');
                $('#search').val('');
                table.search('').columns().search('').draw();
            });
        });
    </script>
@endsection
