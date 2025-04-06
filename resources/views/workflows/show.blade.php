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

        <div class="col-5 align-self-center">
            <div class="customize-input float-right">
                <a href="{{ route('workflows.index') }}" class="btn btn-secondary">Back to List</a>

                @if($workflow->status === 'DRAFT_CREATOR' && $workflow->created_by === Auth::id())
                    <a href="{{ route('workflows.edit', $workflow->id) }}" class="btn btn-warning">Edit Draft</a>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Progress bar styling */
        .workflow-progress {
            margin-bottom: 20px;
        }

        /* Document list styling */
        .document-list {
            margin-top: 10px;
        }

        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            border: 1px solid #eee;
            margin-bottom: 5px;
            border-radius: 4px;
        }

        .document-item:hover {
            background-color: #f8f9fa;
        }

        /* Approval buttons */
        .approval-actions {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        /* Notes textarea */
        textarea[name="notes"] {
            width: 100%;
            resize: vertical;
        }

        /* Status badges */
        .status-badge {
            font-size: 0.9rem;
            padding: 5px 10px;
        }
    </style>
@endpush

@section('page-wrapper')
    <div class="card border-primary">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <h3 class="card-title">Justification Form Details</h3>
                <span class="badge badge-{{ $workflow->status_color }} status-badge">
                    {{ $workflow->formatted_status }} {{$canApprove ? '(You can approve this workflow)' : 'Anda Tidak Berhak Approve'}}
                </span>
            </div>

            <!-- Progress bar -->
            <div class="workflow-progress">
                <div class="progress">
                    <div class="progress-bar bg-success" role="progressbar"
                         style="width: {{ $workflow->progress_percentage }}%"
                         aria-valuenow="{{ $workflow->progress_percentage }}"
                         aria-valuemin="0" aria-valuemax="100">
                        {{ $workflow->progress_percentage }}%
                    </div>
                </div>
                <div class="text-right mt-1">
                    <small>Workflow Progress</small>
                </div>
            </div>

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
                        <select class="form-control" disabled>
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
                        <input type="date" class="form-control" value="{{ $workflow->waktu_penggunaan->format('Y-m-d') }}" readonly>
                    </div>
                    <div class="form-group">
                        <label>Account</label>
                        <select class="form-control" disabled>
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
                        <label>Justification Documents</label>

                        @if ($workflowDocuments->isNotEmpty())
                            <div class="document-list">
                                @foreach ($workflowDocuments as $document)
                                    <div class="document-item">
                                        <div>
                                            @php
                                                $extension = strtolower($document->file_type);
                                                $icon = 'fa-file';

                                                if (in_array($extension, ['pdf'])) {
                                                    $icon = 'fa-file-pdf';
                                                } elseif (in_array($extension, ['doc', 'docx'])) {
                                                    $icon = 'fa-file-word';
                                                } elseif (in_array($extension, ['xls', 'xlsx'])) {
                                                    $icon = 'fa-file-excel';
                                                } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                    $icon = 'fa-file-image';
                                                }
                                            @endphp

                                            <i class="fas {{ $icon }} mr-2"></i>
                                            <span>{{ $document->file_name }}</span>
                                            <small class="text-muted ml-2">({{ $document->file_type }})</small>

                                            @if ($document->uploaded_by)
                                                @php
                                                    $uploader = \App\Models\User::find($document->uploaded_by);
                                                @endphp
                                                <br>
                                                <small class="text-muted">
                                                    Uploaded by: {{ $uploader ? $uploader->name : 'Unknown' }}
                                                    on {{ $document->created_at->format('d M Y H:i') }}
                                                </small>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ url($document->file_path) }}" class="btn btn-sm btn-info" target="_blank">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No documents uploaded.</p>
                        @endif
                    </div>

                    <!-- Approval action form for current user -->
                    @if ($canApprove && $workflow->status === 'WAITING_APPROVAL')
                        <div class="approval-actions">
                            <h5>Approval Action</h5>
                            <hr>

                            <form action="{{ route('workflows.approve', $workflow->id) }}" method="post" id="approvalForm" enctype="multipart/form-data">
                                @csrf

                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Enter your notes..."></textarea>
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="digital_signature" name="digital_signature" value="1">
                                    <label class="form-check-label" for="digital_signature">Use Digital Signature</label>
                                </div>

                                <div class="form-group">
                                    <label for="documents">Attach Documents (Optional)</label>
                                    <input type="file" class="form-control-file" id="documents" name="documents[]" multiple>
                                    <small class="form-text text-muted">You can attach additional documents to support your decision.</small>
                                </div>

                                <div class="d-flex justify-content-between mt-3">
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                                        <i class="fas fa-times-circle"></i> Reject
                                    </button>

                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check-circle"></i> Approve
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <hr>
            <h5>Approval History</h5>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Jabatan</th>
                        <th>Digital Signature</th>
                        <th>Notes</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($workflowApprovals as $approval)
                        @php
                            $user = \App\Models\User::find($approval->user_id);

                            // Define status badge color
                            $statusColor = 'secondary';
                            if ($approval->status === 'APPROVED') {
                                $statusColor = 'success';
                            } elseif ($approval->status === 'REJECTED') {
                                $statusColor = 'danger';
                            } elseif ($approval->status === 'PENDING' && $approval->is_active) {
                                $statusColor = 'warning';
                            }

                            // Determine the date to show
                            $actionDate = null;
                            if ($approval->approved_at) {
                                $actionDate = $approval->approved_at;
                            } elseif ($approval->rejected_at) {
                                $actionDate = $approval->rejected_at;
                            }
                        @endphp
                        <tr>
                            <td>{{ $user ? $user->name : 'Unknown User' }}</td>
                            <td>{{ \App\Models\Workflow::getStatusName($approval->role) }}</td>
                            <td>{{ $user ? $user->jabatan : 'N/A' }}</td>
                            <td>{{ $approval->digital_signature ? 'Yes' : 'No' }}</td>
                            <td>
                                <textarea class="form-control" readonly>{{ $approval->notes ?? 'No notes provided.' }}</textarea>
                            </td>
                            <td>
                                <span class="badge badge-{{ $statusColor }}">
                                    {{ $approval->status }}
                                    @if ($approval->is_active && $approval->status === 'PENDING')
                                        (ACTIVE)
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if ($actionDate)
                                    {{ \Carbon\Carbon::parse($actionDate)->format('d M Y H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No approvals found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Confirm Rejection</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('workflows.reject', $workflow->id) }}" method="post" id="rejectForm" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="reject_notes">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="notes" id="reject_notes" class="form-control" rows="4" required placeholder="Please provide a reason for rejection..."></textarea>
                            <small class="form-text text-muted">This will be visible to the workflow creator.</small>
                        </div>

                        <div class="form-group">
                            <label for="reject_documents">Attach Documents (Optional)</label>
                            <input type="file" class="form-control-file" id="reject_documents" name="documents[]" multiple>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="document.getElementById('rejectForm').submit();">
                        Reject Workflow
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('app-script')
    <script>
        $(document).ready(function() {
            // Transfer notes to rejection form if any were entered
            $('#rejectModal').on('show.bs.modal', function () {
                const approvalNotes = $('#notes').val();
                if (approvalNotes) {
                    $('#reject_notes').val(approvalNotes);
                }
            });
        });
    </script>
@endsection
