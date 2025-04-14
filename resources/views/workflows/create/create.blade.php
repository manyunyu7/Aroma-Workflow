@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Justification Form</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Justification Form</li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Create</li>
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

        /* PIC row styling */
        .pic-entry {
            position: relative;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #eee;
            border-radius: 4px;
            transition: all 0.3s;
        }

        .pic-entry:hover {
            background-color: #f8f9fa;
        }

        /* Reviewer group styling */
        .reviewer-group {
            background-color: #f0f7ff;
            border-left: 3px solid #0d6efd;
            padding: 10px;
            margin: 10px 0;
            border-radius: 0 4px 4px 0;
        }

        .reviewer-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .reviewer-badge {
            background-color: #0d6efd;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }

        /* Role pill badges */
        .role-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-right: 5px;
        }

        .role-creator {
            background-color: #e3f2fd;
            color: #0d47a1;
        }

        .role-acknowledger {
            background-color: #e8f5e9;
            color: #1b5e20;
        }

        .role-head {
            background-color: #fff3e0;
            color: #e65100;
        }

        .role-reviewer-maker {
            background-color: #f3e5f5;
            color: #6a1b9a;
        }

        .role-reviewer-approver {
            background-color: #ffebee;
            color: #b71c1c;
        }
    </style>
@endpush

@section('page-wrapper')
    @include('main.components.message')

    <div class="card border-primary">
        <div class="card-body">
            <h3 class="card-title">Justification Form</h3>
            <hr>

            <form action="{{ route('workflows.store') }}" method="post" enctype="multipart/form-data" id="workflow-form">
                @csrf

                <div class="row">
                    <!-- Bagian 1 -->
                    <div class="col-md-6 col-12">
                        <div class="form-group d-none">
                            <label>Nomor Pengajuan</label>
                            <input type="text" class="form-control" name="nomor_pengajuan" placeholder="Nomor Pengajuan"
                                value="{{ old('nomor_pengajuan') }}">
                        </div>

                        <div class="form-group">
                            <label>Tanggal Pembuatan</label>
                            <input type="text" class="form-control" name="creation_date"
                                value="{{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}" readonly>
                        </div>

                        @php
                            $nik = getAuthNik() ?? null;
                            $employeeDetails = getDetailNaker($nik);
                            $costCenter = $employeeDetails['cost_center_name']['nama_cost_center'] ?? '';
                            $unitKerja = $employeeDetails['unit'] ?? '';
                        @endphp

                        <div class="form-group">
                            <label for="unit_kerja">Unit Kerja</label>
                            <input type="text" class="form-control" id="unit" value="{{ $unitKerja }}" readonly>
                            <input type="hidden" name="unit_kerja" value="{{ $unitKerja }}">
                        </div>

                        <div class="form-group">
                            <label for="cost_center">Cost Center</label>
                            <input type="text" class="form-control" id="cost_center" value="{{ $costCenter }}"
                                readonly>
                            <input type="hidden" name="cost_center" value="{{ $costCenter }}">
                        </div>

                        <div class="form-group">
                            <label>Jenis Anggaran</label>
                            <select class="form-control" required name="jenis_anggaran">
                                <option value="">-- Pilih Jenis Anggaran --</option>
                                @foreach ($jenisAnggaran as $anggaran)
                                    <option value="{{ $anggaran->id }}"
                                        {{ old('jenis_anggaran') == $anggaran->id ? 'selected' : '' }}>{{ $anggaran->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="form-group">
                            <label>Nama Kegiatan</label>
                            <input type="text" class="form-control" required name="nama_kegiatan"
                                placeholder="Nama Kegiatan" value="{{ old('nama_kegiatan') }}">
                        </div>

                        <div class="form-group">
                            <label>Deskripsi Kegiatan</label>
                            <textarea class="form-control" name="deskripsi_kegiatan" rows="4" placeholder="Masukkan deskripsi kegiatan">{{ old('deskripsi_kegiatan') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Total Nilai</label>
                            <input type="text" class="form-control" required id="total_nilai_display"
                                placeholder="Total Nilai" value="{{ old('total_nilai_display') }}">
                            <input type="hidden" name="total_nilai" id="total_nilai" value="{{ old('total_nilai') }}">

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const displayField = document.getElementById('total_nilai_display');
                                    const valueField = document.getElementById('total_nilai');

                                    // Format initial value if exists
                                    if (valueField.value) {
                                        const numberValue = parseInt(valueField.value, 10);
                                        displayField.value = 'Rp ' + numberValue.toLocaleString('id-ID');
                                    }

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

                                        // When budget changes, check if we need to reset the approval workflow
                                        checkBudgetChanges(numberValue);
                                    });
                                });
                            </script>
                        </div>

                        <div class="form-group">
                            <label>Waktu Penggunaan</label>
                            <input type="month" class="form-control" required name="waktu_penggunaan"
                                value="{{ old('waktu_penggunaan') }}">
                        </div>

                        <div class="form-group">
                            <label for="account">Account (Chart of Accounts)</label>
                            <select class="form-control select2" id="account" name="account" required>
                                <option value="">-- Select Account --</option>
                                <optgroup label="Assets">
                                    <option value="1001" {{ old('account') == '1001' ? 'selected' : '' }}>1001 - Cash &
                                        Bank</option>
                                    <option value="1002" {{ old('account') == '1002' ? 'selected' : '' }}>1002 - Accounts
                                        Receivable</option>
                                </optgroup>
                                <optgroup label="Liabilities">
                                    <option value="2001" {{ old('account') == '2001' ? 'selected' : '' }}>2001 - Accounts
                                        Payable</option>
                                    <option value="2002" {{ old('account') == '2002' ? 'selected' : '' }}>2002 - Bank
                                        Loans</option>
                                </optgroup>
                                <optgroup label="Revenue">
                                    <option value="3001" {{ old('account') == '3001' ? 'selected' : '' }}>3001 -
                                        Broadband Services Revenue</option>
                                    <option value="3002" {{ old('account') == '3002' ? 'selected' : '' }}>3002 -
                                        Enterprise Solutions Revenue</option>
                                </optgroup>
                                <optgroup label="Expenses">
                                    <option value="5001" {{ old('account') == '5001' ? 'selected' : '' }}>5001 - Network
                                        Maintenance</option>
                                    <option value="5002" {{ old('account') == '5002' ? 'selected' : '' }}>5002 -
                                        Marketing & Sales</option>
                                </optgroup>
                            </select>
                        </div>

                    </div>

                    <!-- Bagian 2 -->
                    <div class="col-12">
                        <div class="form-group">
                            <label>Documents (PDF, DOC, XLS, Images)</label>

                            <!-- Single file upload button -->
                            <div class="custom-file mb-3">
                                <input type="file" class="custom-file-input" id="singleDocument"
                                    name="single_document" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                <label class="custom-file-label" for="singleDocument">Choose a file</label>
                            </div>

                            <button type="button" id="addDocumentBtn" class="btn btn-primary btn-sm mb-3">
                                <i class="fas fa-plus"></i> Add to Document List
                            </button>

                            <!-- Document list container with manual reordering -->
                            <div id="documentList" class="document-list mt-3">
                                <!-- Documents will be populated here -->
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const singleDocumentInput = document.getElementById('singleDocument');
                                    const fileLabel = document.querySelector('.custom-file-label');
                                    const documentList = document.getElementById('documentList');
                                    const addDocumentBtn = document.getElementById('addDocumentBtn');
                                    let documentItemsCount = 0;
                                    let selectedFile = null;

                                    // Format file size
                                    function formatFileSize(bytes) {
                                        if (bytes < 1024) return bytes + ' B';
                                        else if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
                                        else return (bytes / 1048576).toFixed(2) + ' MB';
                                    }

                                    // Get icon based on file extension
                                    function getFileIcon(extension) {
                                        const iconMap = {
                                            'pdf': 'fa-file-pdf',
                                            'doc': 'fa-file-word',
                                            'docx': 'fa-file-word',
                                            'xls': 'fa-file-excel',
                                            'xlsx': 'fa-file-excel',
                                            'jpg': 'fa-file-image',
                                            'jpeg': 'fa-file-image',
                                            'png': 'fa-file-image'
                                        };

                                        return iconMap[extension.toLowerCase()] || 'fa-file';
                                    }

                                    // Move item up in the list
                                    function moveItemUp(itemId) {
                                        const item = document.getElementById(itemId);
                                        const prevItem = item.previousElementSibling;

                                        if (prevItem) {
                                            documentList.insertBefore(item, prevItem);
                                            updateSequenceValues();
                                        }
                                    }

                                    // Move item down in the list
                                    function moveItemDown(itemId) {
                                        const item = document.getElementById(itemId);
                                        const nextItem = item.nextElementSibling;

                                        if (nextItem) {
                                            documentList.insertBefore(nextItem, item);
                                            updateSequenceValues();
                                        }
                                    }

                                    // Update sequence hidden fields after reordering
                                    function updateSequenceValues() {
                                        const items = documentList.querySelectorAll('.document-item');
                                        items.forEach((item, index) => {
                                            // Update sequence input
                                            const sequenceInput = item.querySelector('input[name^="document_sequence"]');
                                            if (sequenceInput) {
                                                sequenceInput.value = index;
                                            }

                                            // Update visible sequence number
                                            const seqBadge = item.querySelector('.sequence-badge');
                                            if (seqBadge) {
                                                seqBadge.textContent = index + 1;
                                            }

                                            // Enable/disable up button
                                            const upBtn = item.querySelector('.move-up-btn');
                                            if (upBtn) {
                                                upBtn.disabled = index === 0;
                                            }

                                            // Enable/disable down button
                                            const downBtn = item.querySelector('.move-down-btn');
                                            if (downBtn) {
                                                downBtn.disabled = index === items.length - 1;
                                            }
                                        });

                                        // If no items, clear the file input
                                        if (items.length === 0) {
                                            fileLabel.textContent = 'Choose a file';
                                        }
                                    }

                                    // Preview file
                                    function previewFile(file) {
                                        const extension = file.name.split('.').pop().toLowerCase();
                                        const modal = document.getElementById('filePreviewModal');
                                        const modalBody = modal.querySelector('.modal-body');
                                        const modalTitle = modal.querySelector('.modal-title');

                                        modalTitle.textContent = file.name;

                                        if (['jpg', 'jpeg', 'png'].includes(extension)) {
                                            const reader = new FileReader();
                                            reader.onload = function(e) {
                                                modalBody.innerHTML =
                                                    `<img src="${e.target.result}" class="img-fluid" alt="${file.name}">`;
                                                $(modal).modal('show');
                                            };
                                            reader.readAsDataURL(file);
                                        } else if (extension === 'pdf') {
                                            // For PDFs, create an object URL and embed it
                                            const objectUrl = URL.createObjectURL(file);
                                            modalBody.innerHTML = `
                <div class="pdf-container" style="height: 500px;">
                    <object data="${objectUrl}" type="application/pdf" width="100%" height="100%">
                        <p>Your browser doesn't support PDF preview.
                        <a href="${objectUrl}" target="_blank">Click here to view PDF</a>.</p>
                    </object>
                </div>
            `;
                                            $(modal).modal('show');

                                            // Clean up the URL when the modal is closed
                                            $(modal).on('hidden.bs.modal', function() {
                                                URL.revokeObjectURL(objectUrl);
                                            });
                                        } else {
                                            modalBody.innerHTML = `
                <div class="alert alert-info">
                    Preview not available for ${extension.toUpperCase()} files.
                </div>
            `;
                                            $(modal).modal('show');
                                        }
                                    }

                                    // Add file to document list
                                    function addFileToList(file) {
                                        const extension = file.name.split('.').pop().toLowerCase();
                                        const fileIcon = getFileIcon(extension);
                                        const fileSize = formatFileSize(file.size);
                                        const itemId = `document-item-${documentItemsCount}`;
                                        const fileId = documentItemsCount;
                                        documentItemsCount++;

                                        // Get current count of items to set sequence
                                        const currentIndex = documentList.querySelectorAll('.document-item').length;

                                        // Create file item
                                        const fileItem = document.createElement('div');
                                        fileItem.className = 'document-item card p-3 mb-2';
                                        fileItem.id = itemId;
                                        fileItem.setAttribute('data-file-id', fileId);
                                        fileItem.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="sequence-container mr-3">
                        <span class="badge badge-light sequence-badge">${currentIndex + 1}</span>
                    </div>
                    <div>
                        <i class="fas ${fileIcon} mr-2 text-primary"></i>
                        <span class="font-weight-bold">${file.name}</span>
                        <small class="text-muted ml-2">(${fileSize})</small>
                    </div>
                </div>
                <div class="document-actions">
                    <button type="button" class="btn btn-sm btn-outline-secondary ml-1 move-up-btn mr-1" title="Move Up" ${currentIndex === 0 ? 'disabled' : ''}>
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary move-down-btn mr-1" title="Move Down" ${currentIndex === documentList.querySelectorAll('.document-item').length ? 'disabled' : ''}>
                        <i class="fas fa-arrow-down"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info preview-btn mr-1" title="Preview">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-btn" title="Remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-5">
                    <label for="document_categories_${fileId}">Category:</label>
                    <select class="form-control" name="document_categories[${fileId}]" id="document_categories_${fileId}" required>
                        <option value="MAIN">Main Document</option>
                        <option value="SUPPORTING" selected>Supporting Document</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="document_types_${fileId}">Type:</label>
                    <input type="text" class="form-control" name="document_types[${fileId}]" id="document_types_${fileId}" required placeholder="Enter document type">
                </div>
                 <div class="col-md-2">
                    <label for="document_notes_${fileId}">Uploader:</label>
                    <input type="text" class="form-control" name="document_notes[${fileId}]" id="document_notes_${fileId}" value="{{ $user->name }}" readonly>
                </div>
            </div>
            <input type="hidden" name="document_sequence[${fileId}]" value="${currentIndex}">
            <input type="file" name="documents[]" style="display: none;" class="document-file-input" id="document-file-${fileId}">
        `;

                                        documentList.appendChild(fileItem);

                                        // Get the hidden file input and set its files - IMPORTANT CHANGE HERE
                                        const fileInput = fileItem.querySelector('.document-file-input');

                                        // Create a new FileList-like object
                                        const container = new DataTransfer();
                                        container.items.add(file);
                                        fileInput.files = container.files;

                                        // Add event listeners for actions
                                        const previewBtn = fileItem.querySelector('.preview-btn');
                                        previewBtn.addEventListener('click', function() {
                                            previewFile(file);
                                        });

                                        const removeBtn = fileItem.querySelector('.remove-btn');
                                        removeBtn.addEventListener('click', function() {
                                            if (confirm('Are you sure you want to remove this file?')) {
                                                fileItem.remove();
                                                updateSequenceValues();
                                            }
                                        });

                                        const moveUpBtn = fileItem.querySelector('.move-up-btn');
                                        moveUpBtn.addEventListener('click', function() {
                                            moveItemUp(itemId);
                                        });

                                        const moveDownBtn = fileItem.querySelector('.move-down-btn');
                                        moveDownBtn.addEventListener('click', function() {
                                            moveItemDown(itemId);
                                        });

                                        // Update sequence values after adding
                                        updateSequenceValues();

                                        // Reset the single file input
                                        singleDocumentInput.value = '';
                                        fileLabel.textContent = 'Choose a file';
                                        selectedFile = null;
                                    }

                                    // Handle file selection
                                    singleDocumentInput.addEventListener('change', function(e) {
                                        if (this.files.length > 0) {
                                            selectedFile = this.files[0];
                                            fileLabel.textContent = selectedFile.name;
                                        } else {
                                            fileLabel.textContent = 'Choose a file';
                                            selectedFile = null;
                                        }
                                    });

                                    // Add button click handler
                                    addDocumentBtn.addEventListener('click', function() {
                                        if (selectedFile) {
                                            addFileToList(selectedFile);
                                        } else {
                                            alert('Please select a file first');
                                        }
                                    });
                                });
                            </script>
                        </div>
                    </div>

                    <!-- File Preview Modal -->
                    <div class="modal fade" id="filePreviewModal" tabindex="-1" role="dialog"
                        aria-labelledby="filePreviewModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="filePreviewModalLabel">File Preview</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body text-center">
                                    <!-- Preview content will be inserted here -->
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <hr>

                <h5>Approval PICs</h5>
                <p class="text-muted mb-3">Set up the approval workflow for this justification form</p>

                <div class="alert alert-info" id="budget-info-alert">
                    <i class="fas fa-info-circle mr-2"></i>
                    Based on the budget amount, specific approval flow rules will apply.
                </div>

                <div class="workflow-container mb-4">
                    <div class="current-workflow">
                        <button type="button" class="btn btn-success btn-sm mb-3" id="add-pic-btn">
                            <i class="fas fa-plus"></i> Add Approver
                        </button>

                        <div id="pic-container" class="mb-3">
                            <!-- The first row always has the creator (current user) -->
                            <div class="pic-entry" data-role="Creator" data-user-id="{{ $user->id }}">
                                <span class="role-badge role-creator">Creator</span>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <strong>{{ $user->name }}</strong>
                                        <input type="hidden" name="pics[0][user_id]" value="{{ $user->id }}">
                                        <input type="hidden" name="pics[0][role]" value="Creator">
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">{{ $user->unit_kerja }}</small>
                                        <input type="hidden" name="pics[0][jabatan]" value="{{ $user->jabatan ?? '' }}">
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="pics[0][digital_signature]"
                                                value="1" {{ old('pics.0.digital_signature') ? 'checked' : '' }}>
                                            <label class="form-check-label">Use Digital Signature</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <!-- No remove button for creator -->
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <textarea name="pics[0][notes]" class="form-control" placeholder="Notes (optional)">{{ old('pics.0.notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Submit Workflow</button>
                    <button type="button" id="save-draft-btn" class="btn btn-secondary ml-2">Save as Draft</button>
                </div>
            </form>
        </div>
    </div>

    <!-- PIC Modal -->
    <div class="modal fade" id="pic-modal" tabindex="-1" aria-labelledby="picModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="picModalLabel">Add Approver</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Step 1: Select Role -->
                    <div id="step-1" class="step-container">
                        <div class="form-group">
                            <label>Select Role</label>
                            <select id="role-select" class="form-control">
                                <option value="">-- Select Role --</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                            <small class="form-text text-muted">The role determines the approval level in the workflow</small>
                        </div>

                        <div id="reviewer-approver-section" class="mt-3 d-none">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                When selecting a Reviewer-Maker, you'll also need to select a corresponding Reviewer-Approver.
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Select User -->
                    <div id="step-2" class="step-container d-none">
                        <div class="form-group">
                            <label>Select Unit Kerja</label>
                            <select id="unit-kerja-select" class="form-control" style="width: 100%;">
                                <!-- Options will be populated via Select2 -->
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <label>Select Employee</label>
                            <select id="employee-select" class="form-control" style="width: 100%;" disabled>
                                <!-- Options will be populated via AJAX -->
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <label>Jabatan</label>
                            <p id="jabatan-display" class="form-control-static">N/A</p>
                            <input type="hidden" id="jabatan-input">
                        </div>
                    </div>

                    <!-- Step 3: Reviewer-Approver (conditionally shown) -->
                    <div id="step-3" class="step-container d-none">
                        <h5 class="mb-3">Select Reviewer-Approver</h5>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Each Reviewer-Maker must be paired with a Reviewer-Approver.
                        </div>

                        <div class="form-group">
                            <label>Select Unit Kerja for Reviewer-Approver</label>
                            <select id="approver-unit-kerja-select" class="form-control" style="width: 100%;">
                                <!-- Options will be populated via Select2 -->
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <label>Select Reviewer-Approver</label>
                            <select id="approver-select" class="form-control" style="width: 100%;" disabled>
                                <!-- Options will be populated via AJAX -->
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <label>Jabatan</label>
                            <p id="approver-jabatan-display" class="form-control-static">N/A</p>
                            <input type="hidden" id="approver-jabatan-input">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="step-back-btn" class="btn btn-outline-primary d-none">Back</button>
                    <button type="button" id="step-next-btn" class="btn btn-primary">Next</button>
                    <button type="button" id="save-pic-btn" class="btn btn-success d-none">Add to Workflow</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('app-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Global variables
            const picContainer = $("#pic-container");
            const addPicBtn = $("#add-pic-btn");
            const modal = $("#pic-modal");
            const roleSelect = $("#role-select");
            const unitKerjaSelect = $("#unit-kerja-select");
            const employeeSelect = $("#employee-select");
            const approverUnitKerjaSelect = $("#approver-unit-kerja-select");
            const approverSelect = $("#approver-select");
            const stepNextBtn = $("#step-next-btn");
            const stepBackBtn = $("#step-back-btn");
            const savePicBtn = $("#save-pic-btn");

            let picIndex = {{ count(old('pics', [1])) }}; // Start with the next index after existing PICs
            let currentStep = 1;
            let selectedRole = '';
            let selectedUserId = '';
            let selectedUserName = '';
            let selectedUserUnit = '';
            let selectedJabatan = '';
            let approverUserId = '';
            let approverUserName = '';
            let approverUserUnit = '';
            let approverJabatan = '';
            let currentRoles = [];
            let totalBudget = 0;

            // Initialize Select2 components
            unitKerjaSelect.select2({
                dropdownParent: $('#pic-modal'),
                ajax: {
                    url: "/workflow-actions/get-unit-kerja",
                    dataType: "json",
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term || ""
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(unit => ({
                                id: unit.unit_kerja,
                                text: `${unit.unit_kerja} (${unit.employee_count} employees)`
                            }))
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                placeholder: "Select a unit kerja"
            });

            approverUnitKerjaSelect.select2({
                dropdownParent: $('#pic-modal'),
                ajax: {
                    url: "/workflow-actions/get-unit-kerja",
                    dataType: "json",
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term || ""
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(unit => ({
                                id: unit.unit_kerja,
                                text: `${unit.unit_kerja} (${unit.employee_count} employees)`
                            }))
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                placeholder: "Select a unit kerja for reviewer-approver"
            });

            employeeSelect.select2({
                dropdownParent: $('#pic-modal'),
                placeholder: "Select an employee"
            });

            approverSelect.select2({
                dropdownParent: $('#pic-modal'),
                placeholder: "Select a reviewer-approver"
            });

            // Get current roles in the workflow
            function getCurrentRoles() {
                let roles = [];
                $(".pic-entry").each(function() {
                    roles.push($(this).data('role'));
                });
                currentRoles = roles;
                return roles;
            }

            // Check budget changes to update workflow rules
            window.checkBudgetChanges = function(budget) {
                totalBudget = budget;

                let budgetInfoHtml = '<i class="fas fa-info-circle mr-2"></i>';
                if (budget < 500000000) {
                    budgetInfoHtml += 'Budget under 500,000,000 IDR: Acknowledger and Unit Head must be from the same unit.';
                } else {
                    budgetInfoHtml += 'Budget is 500,000,000 IDR or higher: Standard approval rules apply.';
                }

                $("#budget-info-alert").html(budgetInfoHtml);

                // If current workflow violates the new budget rules, show a warning
                validateWorkflowWithBudget(budget);
            };

            // Validate workflow based on budget rules
            function validateWorkflowWithBudget(budget) {
                if (budget < 500000000) {
                    // Check if acknowledger and unit head are from the same unit
                    let acknowledgerEntry = null;
                    let headEntry = null;

                    $(".pic-entry").each(function() {
                        const role = $(this).data('role');
                        if (role === 'Acknowledger') acknowledgerEntry = $(this);
                        if (role === 'Unit Head - Approver') headEntry = $(this);
                    });

                    if (acknowledgerEntry && headEntry) {
                        // Both roles exist, check if they are from the same unit
                        const acknowledgerUnit = acknowledgerEntry.find('small.text-muted').text();
                        const headUnit = headEntry.find('small.text-muted').text();

                        if (acknowledgerUnit !== headUnit) {
                            // Show warning
                            if (!$('#unit-mismatch-warning').length) {
                                const warningHtml = `
                                    <div id="unit-mismatch-warning" class="alert alert-warning mt-3">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        <strong>Warning:</strong> For budgets under 500,000,000 IDR, the Acknowledger and Unit Head must be from the same unit.
                                        Please update your approvers.
                                    </div>
                                `;
                                $('#pic-container').before(warningHtml);
                            }
                        } else {
                            // Remove warning if exists
                            $('#unit-mismatch-warning').remove();
                        }
                    }
                } else {
                    // For higher budgets, remove warning if exists
                    $('#unit-mismatch-warning').remove();
                }
            }

            // Load available roles based on current workflow
            function loadAvailableRoles() {
                const roles = getCurrentRoles();
                const budget = $('#total_nilai').val() || 0;

                // Get available roles from the server
                $.ajax({
                    url: '/workflow-actions/getAvailableRoles',
                    type: 'GET',
                    data: {
                        current_roles: roles,
                        budget: budget
                    },
                    success: function(data) {
                        roleSelect.empty();
                        roleSelect.append('<option value="">-- Select Role --</option>');

                        data.forEach(function(role) {
                            roleSelect.append(`<option value="${role}">${role}</option>`);
                        });

                        // If no roles available, show message
                        if (data.length === 0) {
                            roleSelect.append('<option value="" disabled>No roles available for current workflow</option>');
                            stepNextBtn.prop('disabled', true);
                        } else {
                            stepNextBtn.prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading available roles:', xhr.responseText);
                        roleSelect.empty();
                        roleSelect.append('<option value="">Error loading roles</option>');
                        stepNextBtn.prop('disabled', true);
                    }
                });
            }

            // Load employees based on unit kerja and role
            function loadEmployees(unitKerja, roleValue) {
                const budget = $('#total_nilai').val() || 0;

                $.ajax({
                    url: '/workflow-actions/get-employees',
                    type: 'GET',
                    data: {
                        unit_kerja: unitKerja,
                        role: roleValue,
                        budget: budget
                    },
                    success: function(data) {
                        employeeSelect.empty();
                        employeeSelect.append('<option value="">-- Select Employee --</option>');

                        if (data.length > 0) {
                            data.forEach(function(employee) {
                                employeeSelect.append(`<option value="${employee.id}" data-unit="${employee.unit_kerja}">${employee.name}</option>`);
                            });
                            employeeSelect.prop('disabled', false);
                        } else {
                            employeeSelect.append('<option value="" disabled>No employees found with this role</option>');
                            employeeSelect.prop('disabled', true);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading employees:', xhr.responseText);
                        employeeSelect.empty();
                        employeeSelect.append('<option value="">Error loading employees</option>');
                        employeeSelect.prop('disabled', true);
                    }
                });
            }

            // Load approvers based on unit kerja
            function loadApprovers(unitKerja) {
                const budget = $('#total_nilai').val() || 0;

                $.ajax({
                    url: '/workflow-actions/get-employees',
                    type: 'GET',
                    data: {
                        unit_kerja: unitKerja,
                        role: 'Reviewer-Approver',
                        budget: budget
                    },
                    success: function(data) {
                        approverSelect.empty();
                        approverSelect.append('<option value="">-- Select Reviewer-Approver --</option>');

                        if (data.length > 0) {
                            data.forEach(function(employee) {
                                approverSelect.append(`<option value="${employee.id}" data-unit="${employee.unit_kerja}">${employee.name}</option>`);
                            });
                            approverSelect.prop('disabled', false);
                        } else {
                            approverSelect.append('<option value="" disabled>No reviewer-approvers found</option>');
                            approverSelect.prop('disabled', true);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading approvers:', xhr.responseText);
                        approverSelect.empty();
                        approverSelect.append('<option value="">Error loading approvers</option>');
                        approverSelect.prop('disabled', true);
                    }
                });
            }

            // Fetch employee's position/jabatan
            function fetchJabatan(userId, targetElement, targetInput) {
                $.ajax({
                    url: '/workflow-actions/fetch-jabatan',
                    type: 'GET',
                    data: { user_id: userId },
                    success: function(response) {
                        if (response.success) {
                            targetElement.text(response.nama_posisi);
                            targetInput.val(response.nama_posisi);
                        } else {
                            targetElement.text('Position not found');
                            targetInput.val('');
                        }
                    },
                    error: function() {
                        targetElement.text('Error loading position');
                        targetInput.val('');
                    }
                });
            }

            // Show step in the modal
            function showStep(stepNumber) {
                $('.step-container').addClass('d-none');
                $(`#step-${stepNumber}`).removeClass('d-none');

                // Update buttons based on step
                if (stepNumber === 1) {
                    stepBackBtn.addClass('d-none');
                    stepNextBtn.removeClass('d-none');
                    savePicBtn.addClass('d-none');
                } else if (stepNumber === 2) {
                    stepBackBtn.removeClass('d-none');

                    if (selectedRole === 'Reviewer-Maker') {
                        stepNextBtn.removeClass('d-none');
                        savePicBtn.addClass('d-none');
                    } else {
                        stepNextBtn.addClass('d-none');
                        savePicBtn.removeClass('d-none');
                    }
                } else if (stepNumber === 3) {
                    stepBackBtn.removeClass('d-none');
                    stepNextBtn.addClass('d-none');
                    savePicBtn.removeClass('d-none');
                }

                currentStep = stepNumber;
            }

            // Add PIC entry to the workflow
            function addPicEntry(picData) {
                let cssClass = '';
                switch(picData.role) {
                    case 'Acknowledger':
                        cssClass = 'role-acknowledger';
                        break;
                    case 'Unit Head - Approver':
                        cssClass = 'role-head';
                        break;
                    case 'Reviewer-Maker':
                        cssClass = 'role-reviewer-maker';
                        break;
                    case 'Reviewer-Approver':
                        cssClass = 'role-reviewer-approver';
                        break;
                }

                // Determine where to add the PIC entry
                if (picData.role === 'Reviewer-Approver' && picData.pairedWithMaker) {
                    // Add inside the group with the maker
                    const groupId = `reviewer-group-${picData.pairedWithIndex}`;

                    const approverHtml = `
                        <div class="pic-entry mt-2" data-role="${picData.role}" data-user-id="${picData.userId}">
                            <span class="role-badge ${cssClass}">${picData.role}</span>
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <strong>${picData.userName}</strong>
                                    <input type="hidden" name="pics[${picIndex}][jabatan]" value="${picData.jabatan}">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="pics[${picIndex}][digital_signature]" value="1">
                                        <label class="form-check-label">Use Digital Signature</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-pic">
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <textarea name="pics[${picIndex}][notes]" class="form-control" placeholder="Notes (optional)"></textarea>
                                </div>
                            </div>
                        </div>
                    `;

                    $(`#${groupId}`).append(approverHtml);
                } else if (picData.role === 'Reviewer-Maker') {
                    // Create a new reviewer group
                    const groupHtml = `
                        <div class="reviewer-group" id="reviewer-group-${picIndex}">
                            <div class="reviewer-header">
                                <h6 class="mb-0">Reviewer Group</h6>
                                <span class="reviewer-badge">Maker + Approver</span>
                            </div>

                            <div class="pic-entry" data-role="${picData.role}" data-user-id="${picData.userId}">
                                <span class="role-badge ${cssClass}">${picData.role}</span>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <strong>${picData.userName}</strong>
                                        <input type="hidden" name="pics[${picIndex}][user_id]" value="${picData.userId}">
                                        <input type="hidden" name="pics[${picIndex}][role]" value="${picData.role}">
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">${picData.userUnit}</small>
                                        <input type="hidden" name="pics[${picIndex}][jabatan]" value="${picData.jabatan}">
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="pics[${picIndex}][digital_signature]" value="1">
                                            <label class="form-check-label">Use Digital Signature</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-pic">
                                            <i class="fas fa-times"></i> Remove
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <textarea name="pics[${picIndex}][notes]" class="form-control" placeholder="Notes (optional)"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    picContainer.append(groupHtml);
                } else {
                    // Regular entry (Acknowledger or Unit Head)
                    const entryHtml = `
                        <div class="pic-entry" data-role="${picData.role}" data-user-id="${picData.userId}">
                            <span class="role-badge ${cssClass}">${picData.role}</span>
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <strong>${picData.userName}</strong>
                                    <input type="hidden" name="pics[${picIndex}][user_id]" value="${picData.userId}">
                                    <input type="hidden" name="pics[${picIndex}][role]" value="${picData.role}">
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">${picData.userUnit}</small>
                                    <input type="hidden" name="pics[${picIndex}][jabatan]" value="${picData.jabatan}">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="pics[${picIndex}][digital_signature]" value="1">
                                        <label class="form-check-label">Use Digital Signature</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-pic">
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <textarea name="pics[${picIndex}][notes]" class="form-control" placeholder="Notes (optional)"></textarea>
                                </div>
                            </div>
                        </div>
                    `;

                    picContainer.append(entryHtml);
                }

                picIndex++;

                // Validate workflow after adding
                validateWorkflowWithBudget(totalBudget);
            }

            // Event Handlers

            // Add PIC button
            addPicBtn.click(function() {
                // Reset modal
                unitKerjaSelect.val(null).trigger('change');
                employeeSelect.empty().prop('disabled', true);
                approverUnitKerjaSelect.val(null).trigger('change');
                approverSelect.empty().prop('disabled', true);
                $("#jabatan-display").text("N/A");
                $("#approver-jabatan-display").text("N/A");
                $("#jabatan-input").val("");
                $("#approver-jabatan-input").val("");

                // Load available roles
                loadAvailableRoles();

                // Reset step
                showStep(1);

                // Show modal
                modal.modal('show');
            });

            // Role selection
            roleSelect.change(function() {
                selectedRole = $(this).val();

                // Show/hide reviewer-approver section based on role
                if (selectedRole === 'Reviewer-Maker') {
                    $("#reviewer-approver-section").removeClass('d-none');
                } else {
                    $("#reviewer-approver-section").addClass('d-none');
                }
            });

            // Unit kerja selection
            unitKerjaSelect.on('select2:select', function(e) {
                const unitKerja = e.params.data.id;
                loadEmployees(unitKerja, selectedRole);
            });

            // Employee selection
            employeeSelect.on('change', function() {
                const userId = $(this).val();
                if (userId) {
                    selectedUserId = userId;
                    selectedUserName = $(this).find('option:selected').text();
                    selectedUserUnit = $(this).find('option:selected').data('unit');
                    fetchJabatan(userId, $("#jabatan-display"), $("#jabatan-input"));
                }
            });

            // Approver unit kerja selection
            approverUnitKerjaSelect.on('select2:select', function(e) {
                const unitKerja = e.params.data.id;
                loadApprovers(unitKerja);
            });

            // Approver selection
            approverSelect.on('change', function() {
                const userId = $(this).val();
                if (userId) {
                    approverUserId = userId;
                    approverUserName = $(this).find('option:selected').text();
                    approverUserUnit = $(this).find('option:selected').data('unit');
                    fetchJabatan(userId, $("#approver-jabatan-display"), $("#approver-jabatan-input"));
                }
            });

            // Next step button
            stepNextBtn.click(function() {
                if (currentStep === 1) {
                    if (!selectedRole) {
                        alert('Please select a role first');
                        return;
                    }
                    showStep(2);
                } else if (currentStep === 2) {
                    if (!selectedUserId) {
                        alert('Please select an employee first');
                        return;
                    }
                    selectedJabatan = $("#jabatan-input").val();
                    showStep(3);
                }
            });

            // Back button
            stepBackBtn.click(function() {
                if (currentStep > 1) {
                    showStep(currentStep - 1);
                }
            });

            // Save PIC button
            savePicBtn.click(function() {
                if (currentStep === 2) {
                    if (!selectedUserId) {
                        alert('Please select an employee first');
                        return;
                    }

                    // Add regular PIC (Acknowledger or Unit Head)
                    selectedJabatan = $("#jabatan-input").val();

                    addPicEntry({
                        role: selectedRole,
                        userId: selectedUserId,
                        userName: selectedUserName,
                        userUnit: selectedUserUnit,
                        jabatan: selectedJabatan
                    });

                    modal.modal('hide');
                } else if (currentStep === 3) {
                    if (!approverUserId) {
                        alert('Please select a reviewer-approver first');
                        return;
                    }

                    // Add reviewer-maker
                    selectedJabatan = $("#jabatan-input").val();
                    const makerIndex = picIndex;

                    addPicEntry({
                        role: selectedRole,
                        userId: selectedUserId,
                        userName: selectedUserName,
                        userUnit: selectedUserUnit,
                        jabatan: selectedJabatan
                    });

                    // Add reviewer-approver
                    approverJabatan = $("#approver-jabatan-input").val();

                    addPicEntry({
                        role: 'Reviewer-Approver',
                        userId: approverUserId,
                        userName: approverUserName,
                        userUnit: approverUserUnit,
                        jabatan: approverJabatan,
                        pairedWithMaker: true,
                        pairedWithIndex: makerIndex
                    });

                    modal.modal('hide');
                }
            });

            // Remove PIC button (event delegation)
            $(document).on('click', '.remove-pic', function() {
                const picEntry = $(this).closest('.pic-entry');
                const picRole = picEntry.data('role');

                if (picRole === 'Reviewer-Maker') {
                    // If removing a maker, also remove the entire group including approver
                    $(this).closest('.reviewer-group').remove();
                } else if (picRole === 'Reviewer-Approver') {
                    // If removing an approver, also remove the maker
                    $(this).closest('.reviewer-group').remove();
                } else {
                    // Regular removal
                    picEntry.remove();
                }

                // Revalidate workflow
                validateWorkflowWithBudget(totalBudget);
            });

            // Handle "Save as Draft" button
            $("#save-draft-btn").click(function() {
                // Add a hidden field to indicate this is a draft
                $('<input>').attr({
                    type: 'hidden',
                    name: 'is_draft',
                    value: '1'
                }).appendTo('#workflow-form');

                // Submit the form
                $('#workflow-form').submit();
            });

            // Initialize Select2 for the account select
            $('#account').select2();

            // Initialize the budget value for validation
            totalBudget = parseInt($('#total_nilai').val() || 0);

            // Run initial validation
            validateWorkflowWithBudget(totalBudget);
        });
    </script>
@endsection
