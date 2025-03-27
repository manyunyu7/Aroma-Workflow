@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Justification Form</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Justification Form</li>
                        <li class="breadcrumb-item text-muted" aria-current="page">View</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        /* Style for readonly inputs to look like normal inputs */
        .form-control[readonly] {
            background-color: transparent;
            border-color: #ced4da; /* Keep the same border color */
            color: #495057; /* Keep the same text color */
            opacity: 1; /* Ensure no dimming effect */
        }
        /* Style for the drop zone */
        .drop-zone {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            color: #666;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }
        /* Highlight the drop zone when dragging over it */
        .drop-zone.dragover {
            border-color: #007bff;
            background-color: #f0f8ff;
        }
        /* Button styling */
        #fileActions button {
            margin-right: 5px;
        }
        /* Style for the Notes textarea */
        textarea[name*="[notes]"] {
            width: 100%;
            height: 80px;
            resize: vertical;
        }
    </style>
@endpush

@section('page-wrapper')
    <div class="card border-primary">
        <div class="card-body">
            <h3 class="card-title">Justification Form Details</h3>
            <hr>
            <div class="row">
                <!-- Workflow Details -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Nomor Pengajuan</label>
                        <input type="text" class="form-control" value="{{ $workflow->nomor_pengajuan }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Unit Kerja</label>
                        <input type="text" class="form-control" value="{{ $workflow->unit_kerja }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Cost Center</label>
                        <input type="text" class="form-control" value="{{ $workflow->cost_center }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Jenis Anggaran</label>
                        <select class="form-control" readonly>
                            <option value="" disabled>-- Pilih Jenis Anggaran --</option>
                            @foreach ($jenisAnggaran as $anggaran)
                                <option value="{{ $anggaran->id }}"
                                    {{ $workflow->jenis_anggaran == $anggaran->id ? 'selected' : '' }}>
                                    {{ $anggaran->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Kegiatan</label>
                        <input type="text" class="form-control" value="{{ $workflow->nama_kegiatan }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Total Nilai</label>
                        <input type="text" class="form-control" value="{{ 'Rp ' . number_format($workflow->total_nilai, 0, ',', '.') }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Waktu Penggunaan</label>
                        <input type="date" class="form-control" value="{{ $workflow->waktu_penggunaan }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Account</label>
                        <select class="form-control" readonly>
                            <option value="" disabled>-- Select Account --</option>
                            <optgroup label="Assets">
                                <option value="1001" {{ $workflow->account == '1001' ? 'selected' : '' }}>1001 - Cash & Bank</option>
                                <option value="1002" {{ $workflow->account == '1002' ? 'selected' : '' }}>1002 - Accounts Receivable</option>
                            </optgroup>
                            <optgroup label="Liabilities">
                                <option value="2001" {{ $workflow->account == '2001' ? 'selected' : '' }}>2001 - Accounts Payable</option>
                                <option value="2002" {{ $workflow->account == '2002' ? 'selected' : '' }}>2002 - Bank Loans</option>
                            </optgroup>
                            <optgroup label="Revenue">
                                <option value="3001" {{ $workflow->account == '3001' ? 'selected' : '' }}>3001 - Broadband Services Revenue</option>
                                <option value="3002" {{ $workflow->account == '3002' ? 'selected' : '' }}>3002 - Enterprise Solutions Revenue</option>
                            </optgroup>
                            <optgroup label="Expenses">
                                <option value="5001" {{ $workflow->account == '5001' ? 'selected' : '' }}>5001 - Network Maintenance</option>
                                <option value="5002" {{ $workflow->account == '5002' ? 'selected' : '' }}>5002 - Marketing & Sales</option>
                            </optgroup>
                        </select>
                    </div>
                </div>

                <!-- Document Section -->
                <div class="col-md-6 col-12">
                    <div class="form-group">
                        <label>Justification Document</label>
                        @if ($workflowApproval->isNotEmpty() && $workflowApproval->first()->attachment)
                            <div class="drop-zone">
                                <a href="{{ url($workflowApproval->first()->attachment) }}" target="_blank">
                                    View Document
                                </a>
                            </div>
                        @else
                            <p>No document attached.</p>
                        @endif
                    </div>
                </div>
            </div>

            <hr>
            <h5>Approval PICs</h5>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Jabatan</th>
                        <th>Digital Signature</th>
                        <th>Notes</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($workflowApproval as $approval)
                        @php
                            $detailNaker = getDetailNaker($approval->user_id ?? null);
                        @endphp
                        <tr>
                            <td>{{ $detailNaker['name'] ?? 'N/A' }}</td>
                            <td>{{ $approval->role }}</td>
                            <td>{{ $detailNaker['nama_posisi'] ?? 'N/A' }}</td>
                            <td>{{ $approval->digital_signature ? 'Yes' : 'No' }}</td>
                            <td>
                                <textarea name="notes" class="form-control" readonly>{{ $approval->notes ?? 'N/A' }}</textarea>
                            </td>
                            <td>{{ $approval->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No approvals assigned.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('app-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        // Ensure all select elements are visually styled correctly even when readonly
        document.querySelectorAll('select[readonly]').forEach(select => {
            select.style.pointerEvents = 'none'; // Disable interaction
        });
    </script>
@endsection
