@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Edit Justification Form</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Justification Form</li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">

    <style>
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

        /* Document removal checkbox styling */
        .document-remove {
            margin-right: 10px;
        }
    </style>
@endpush

@section('page-wrapper')
    @include('main.components.message')

    <div class="card border-primary">
        <div class="card-body">
            <h3 class="card-title">Edit Justification Form</h3>
            <hr>

            <form action="{{ route('workflows.update', $workflow->id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Bagian 1 -->
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>Nomor Pengajuan</label>
                            <input type="text" class="form-control" required name="nomor_pengajuan"
                                placeholder="Nomor Pengajuan" value="{{ old('nomor_pengajuan', $workflow->nomor_pengajuan) }}">
                        </div>

                        <div class="form-group">
                            <label for="unit_kerja">Unit Kerja</label>
                            <input type="text" class="form-control" id="unit"
                                value="{{ old('unit_kerja', $workflow->unit_kerja) }}" readonly>
                            <input type="hidden" name="unit_kerja" value="{{ old('unit_kerja', $workflow->unit_kerja) }}">
                        </div>

                        <div class="form-group">
                            <label for="cost_center">Cost Center</label>
                            <input type="text" class="form-control" id="cost_center"
                                value="{{ old('cost_center', $workflow->cost_center) }}" readonly>
                            <input type="hidden" name="cost_center" value="{{ old('cost_center', $workflow->cost_center) }}">
                        </div>

                        <div class="form-group">
                            <label>Jenis Anggaran</label>
                            <select class="form-control" required name="jenis_anggaran">
                                <option value="">-- Pilih Jenis Anggaran --</option>
                                @foreach ($jenisAnggaran as $anggaran)
                                    <option value="{{ $anggaran->id }}"
                                        {{ old('jenis_anggaran', $workflow->jenis_anggaran) == $anggaran->id ? 'selected' : '' }}>
                                        {{ $anggaran->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Nama Kegiatan</label>
                            <input type="text" class="form-control" required name="nama_kegiatan"
                                placeholder="Nama Kegiatan" value="{{ old('nama_kegiatan', $workflow->nama_kegiatan) }}">
                        </div>

                        <div class="form-group">
                            <label>Total Nilai</label>
                            <input type="text" class="form-control" required id="total_nilai_display"
                                placeholder="Total Nilai" value="{{ 'Rp ' . number_format($workflow->total_nilai, 0, ',', '.') }}">
                            <input type="hidden" name="total_nilai" id="total_nilai" value="{{ old('total_nilai', $workflow->total_nilai) }}">

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const displayField = document.getElementById('total_nilai_display');
                                    const valueField = document.getElementById('total_nilai');

                                    displayField.addEventListener('input', function(e) {
                                        // Remove non-digit characters and the "Rp" prefix
                                        let rawValue = this.value.replace(/[^0-9]/g, '');

                                        if (rawValue === '') {
                                            valueField.value = '';
                                            this.value = '';
                                            return;
                                        }

                                        const numberValue = parseInt(rawValue, 10);

                                        // Format with thousand separators and add "Rp"
                                        const formattedValue = 'Rp ' + numberValue.toLocaleString('id-ID', {
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        });

                                        this.value = formattedValue; // Display the formatted value with "Rp"
                                        valueField.value = numberValue; // Store the raw number for submission
                                    });
                                });
                            </script>
                        </div>

                        <div class="form-group">
                            <label>Waktu Penggunaan</label>
                            <input type="date" class="form-control" required name="waktu_penggunaan"
                                value="{{ old('waktu_penggunaan', $workflow->waktu_penggunaan->format('Y-m-d')) }}">
                        </div>

                        <div class="form-group">
                            <label for="account">Account (Chart of Accounts)</label>
                            <select class="form-control select2" id="account" name="account" required>
                                <option value="">-- Select Account --</option>
                                <optgroup label="Assets">
                                    <option value="1001" {{ old('account', $workflow->account) == '1001' ? 'selected' : '' }}>1001 - Cash & Bank</option>
                                    <option value="1002" {{ old('account', $workflow->account) == '1002' ? 'selected' : '' }}>1002 - Accounts Receivable</option>
                                </optgroup>
                                <optgroup label="Liabilities">
                                    <option value="2001" {{ old('account', $workflow->account) == '2001' ? 'selected' : '' }}>2001 - Accounts Payable</option>
                                    <option value="2002" {{ old('account', $workflow->account) == '2002' ? 'selected' : '' }}>2002 - Bank Loans</option>
                                </optgroup>
                                <optgroup label="Revenue">
                                    <option value="3001" {{ old('account', $workflow->account) == '3001' ? 'selected' : '' }}>3001 - Broadband Services Revenue</option>
                                    <option value="3002" {{ old('account', $workflow->account) == '3002' ? 'selected' : '' }}>3002 - Enterprise Solutions Revenue</option>
                                </optgroup>
                                <optgroup label="Expenses">
                                    <option value="5001" {{ old('account', $workflow->account) == '5001' ? 'selected' : '' }}>5001 - Network Maintenance</option>
                                    <option value="5002" {{ old('account', $workflow->account) == '5002' ? 'selected' : '' }}>5002 - Marketing & Sales</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <!-- Bagian 2 -->
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>Current Documents</label>

                            @if ($workflowDocuments->isNotEmpty())
                                <div class="document-list">
                                    @foreach ($workflowDocuments as $document)
                                        <div class="document-item">
                                            <div class="d-flex align-items-center">
                                                <div class="form-check document-remove">
                                                    <input type="checkbox" class="form-check-input" name="remove_documents[]" value="{{ $document->id }}" id="remove_doc_{{ $document->id }}">
                                                    <label class="form-check-label" for="remove_doc_{{ $document->id }}">Remove</label>
                                                </div>
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
                                                </div>
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

                            <hr>

                            <label>Upload New Documents</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="new_documents" name="new_documents[]"
                                       multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                <label class="custom-file-label" for="new_documents">Choose files</label>
                            </div>

                            <!-- New document list container -->
                            <div id="newDocumentList" class="document-list"></div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const fileInput = document.getElementById('new_documents');
                                    const fileLabel = document.querySelector('.custom-file-label');
                                    const documentList = document.getElementById('newDocumentList');

                                    fileInput.addEventListener('change', function(e) {
                                        // Update the file label
                                        if (this.files.length > 0) {
                                            fileLabel.textContent = `${this.files.length} files selected`;
                                        } else {
                                            fileLabel.textContent = 'Choose files';
                                        }

                                        // Clear previous list
                                        documentList.innerHTML = '';

                                        // Add each file to the list
                                        Array.from(this.files).forEach((file, index) => {
                                            const fileSize = (file.size / 1024).toFixed(2) + ' KB';
                                            const fileItem = document.createElement('div');
                                            fileItem.className = 'document-item';
                                            fileItem.innerHTML = `
                                                <div>
                                                    <i class="fas fa-file mr-2"></i>
                                                    <span>${file.name}</span>
                                                    <small class="text-muted ml-2">(${fileSize})</small>
                                                </div>
                                            `;
                                            documentList.appendChild(fileItem);
                                        });
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>

                <hr>

                <h5>Approval PICs</h5>
                <button type="button" class="btn btn-success btn-sm" id="add-pic-btn">+ Add PIC</button>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Jabatan</th>
                            <th>Digital Signature</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="pic-table">
                        <!-- Existing PICs -->
                        @foreach ($workflowApprovals as $index => $approval)
                            @php
                                $user = \App\Models\User::find($approval->user_id);
                            @endphp
                            <tr data-index="{{ $index }}" data-user-id="{{ $approval->user_id }}" data-role-code="{{ $approval->role }}">
                                <td>
                                    {{ $user ? $user->name : 'Unknown User' }}
                                    <input type="hidden" name="pics[{{ $index }}][user_id]" value="{{ $approval->user_id }}">
                                </td>
                                <td>
                                    {{ \App\Models\Workflow::getStatusName($approval->role) }}
                                    <input type="hidden" name="pics[{{ $index }}][role]" value="{{ $approval->role }}">
                                </td>
                                <td>
                                    <span>{{ $user ? $user->jabatan : 'N/A' }}</span>
                                    <input type="hidden" name="pics[{{ $index }}][jabatan]" value="{{ $user ? $user->jabatan : '' }}">
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="pics[{{ $index }}][digital_signature]"
                                            value="1" {{ $approval->digital_signature ? 'checked' : '' }}>
                                        <label class="form-check-label">Use Digital Signature</label>
                                    </div>
                                </td>
                                <td>
                                    <textarea name="pics[{{ $index }}][notes]" placeholder="Enter notes (optional)"
                                        {{ $approval->user_id != Auth::id() ? 'disabled' : '' }}>{{ $approval->notes }}</textarea>
                                </td>
                                <td>
                                    @if($approval->role !== 'CREATOR')
                                        <button type="button" class="btn btn-danger btn-sm remove-pic">Remove</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update and Submit Workflow</button>
                    <button type="button" id="save-draft-btn" class="btn btn-secondary ml-2">Save as Draft</button>
                    <a href="{{ route('workflows.index') }}" class="btn btn-link ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- PIC Modal -->
    <div class="modal fade" id="pic-modal" tabindex="-1" aria-labelledby="picModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="picModalLabel">Add PIC</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label>Select User</label>
                    <select id="user-select" class="form-control" style="width: 100%;"></select>
                    <label class="mt-2">Role</label>
                    <select id="role-select" class="form-control">
                        @foreach (\App\Models\Workflow::getStatuses() as $status)
                            @if ($status['code'] != 'CREATOR') {{-- Skip CREATOR role as it's for the logged-in user --}}
                                <option value="{{ $status['code'] }}">{{ $status['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                    <label class="mt-2">Jabatan</label>
                    <span id="jabatan-display">N/A</span> <!-- Display jabatan here -->
                    <input type="hidden" id="jabatan-input"> <!-- Hidden input for jabatan -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="save-pic" class="btn btn-primary">Save PIC</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('app-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            const userSelect = $("#user-select");
            const picTable = $("#pic-table");
            const modal = new bootstrap.Modal(document.getElementById("pic-modal"));
            let picIndex = {{ count($workflowApprovals) }}; // Start with the next index after existing PICs

            // Initialize Select2 with AJAX
            userSelect.select2({
                ajax: {
                    url: "/workflows/find-users",
                    dataType: "json",
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(user => ({
                                id: user.id,
                                text: user.name
                            }))
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2,
                placeholder: "Select a user",
                allowClear: true,
                dropdownParent: $('#pic-modal')
            });

            userSelect.on("select2:select", function(e) {
                const userId = e.params.data.id;

                $.get("/workflows/fetch-jabatan", {
                    user_id: userId
                }, function(response) {
                    if (response.success) {
                        $("#jabatan-display").text(response.nama_posisi); // Display jabatan
                        $("#jabatan-input").val(response.nama_posisi); // Store jabatan in hidden input
                    } else {
                        $("#jabatan-display").text("Position not found");
                        $("#jabatan-input").val(""); // Clear jabatan if not found
                    }
                });
            });

            $("#add-pic-btn").click(function() {
                // Reset the form
                userSelect.val(null).trigger('change');
                $("#role-select").val($("#role-select option:first").val());
                $("#jabatan-display").text("N/A");
                $("#jabatan-input").val("");

                // Remove any editing data
                $("#save-pic").removeData("editingRow");

                // Show the modal
                modal.show();
            });

            $("#save-pic").click(function() {
                const userId = userSelect.val();
                const userName = userSelect.find("option:selected").text();
                const roleCode = $("#role-select").val();
                const roleName = $("#role-select option:selected").text();
                const jabatan = $("#jabatan-input").val() || 'N/A';

                if (!userId || !roleCode) {
                    alert("Please select both user and role");
                    return;
                }

                let editingRow = $("#save-pic").data("editingRow");

                if (editingRow) {
                    // Update existing row
                    editingRow.find("td:eq(0)").html(
                        `${userName} <input type="hidden" name="pics[${editingRow.data('index')}][user_id]" value="${userId}">`
                    );
                    editingRow.find("td:eq(1)").html(
                        `${roleName} <input type="hidden" name="pics[${editingRow.data('index')}][role]" value="${roleCode}">`
                    );
                    editingRow.find("td:eq(2)").html(
                        `<span>${jabatan}</span><input type="hidden" name="pics[${editingRow.data('index')}][jabatan]" value="${jabatan}">`
                    );

                    // Enable/disable Notes field based on whether it's the current user
                    const notesField = editingRow.find("td:eq(4) textarea");
                    const isCurrentUser = (userId == "{{ Auth::id() }}");
                    notesField.prop("disabled", !isCurrentUser);
                } else {
                    // Determine if this is the current user
                    const isCurrentUser = (userId == "{{ Auth::id() }}");

                    // Add new row
                    const newRow = $(`
                        <tr data-index="${picIndex}" data-user-id="${userId}" data-role-code="${roleCode}">
                            <td>${userName} <input type="hidden" name="pics[${picIndex}][user_id]" value="${userId}"></td>
                            <td>${roleName} <input type="hidden" name="pics[${picIndex}][role]" value="${roleCode}"></td>
                            <td>
                                <span>${jabatan}</span>
                                <input type="hidden" name="pics[${picIndex}][jabatan]" value="${jabatan}">
                            </td>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="pics[${picIndex}][digital_signature]" value="1">
                                    <label class="form-check-label">Use Digital Signature</label>
                                </div>
                            </td>
                            <td>
                                <textarea name="pics[${picIndex}][notes]" placeholder="Enter notes (optional)"
                                    ${!isCurrentUser ? 'disabled' : ''}></textarea>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-pic">Remove</button>
                            </td>
                        </tr>
                    `);

                    picTable.append(newRow);
                    picIndex++;
                }

                modal.hide();
            });

            // Event delegation for remove buttons
            $(document).on("click", ".remove-pic", function() {
                $(this).closest("tr").remove();
            });

            // Handle "Save as Draft" button
            $("#save-draft-btn").click(function() {
                // Add a hidden field to indicate this is a draft
                $('<input>').attr({
                    type: 'hidden',
                    name: 'is_draft',
                    value: '1'
                }).appendTo('form');

                // Submit the form
                $('form').submit();
            });

            // Initialize Select2 for the account select
            $('#account').select2();
        });
    </script>
@endsection
