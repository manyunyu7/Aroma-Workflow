{{-- resources/views/workflows/edit.blade.php --}}
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
    @include('workflows.create.components.styles')
@endpush

@section('page-wrapper')
    @include('main.components.message')

    <div class="card border-primary">
        <div class="card-body">
            <h3 class="card-title">Edit Justification Form</h3>
            <p class="text-muted">Workflow ID: {{ $workflow->id }} | Status: {{ $workflow->status }}</p>
            <hr>

            <form action="{{ route('workflows.update', $workflow->id) }}" method="post" enctype="multipart/form-data" id="workflow-form">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Form Section 1 -->
                    <div class="col-md-6 col-12">
                        @include('workflows.create.components.form-basic-info')
                    </div>

                    <!-- Document Upload Section -->
                    <div class="col-12">
                        <div class="form-group">
                            <label>Documents (PDF)</label>
                            @include('workflows.create.components.document-upload-component')
                        </div>
                    </div>

                    <hr>

                    <div class="col-12 d-none">
                        <!-- Approval PIC Section -->
                        <h5>Approval PICs</h5>
                        <p class="text-muted mb-3">Set up the approval workflow for this justification form</p>

                        <div class="alert alert-info" id="budget-category-alert">
                            <i class="fas fa-info-circle mr-2"></i>
                            Please enter a budget amount to see applicable approval flow.
                        </div>

                        <div class="alert alert-info" id="budget-info-alert">
                            <i class="fas fa-info-circle mr-2"></i>
                            Based on the budget amount, specific approval flow rules will apply.
                        </div>
                    </div>

                    <div class="col-12">
                        @include('workflows.create.components.workflow-container')
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Update Workflow</button>
                        <button type="button" id="save-draft-btn" class="btn btn-secondary ml-2">Save as Draft</button>
                        <a href="{{ route('workflows.index') }}" class="btn btn-outline-secondary ml-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- PIC Modal -->
    @include('workflows.create.components.pic-modal')
@endsection

@section('app-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- Initialize core components -->
    <script>
        $(document).ready(function() {
            // Initialize Select2 for the account select
            $('#account').select2();

            // Initialize currency formatter
            initCurrencyFormatter();

            <!-- Initialize workflow components -->
            initWorkflowComponents();

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
        });

        // Load existing workflow PICs/approvers
        @if(isset($workflowApprovals) && count($workflowApprovals) > 0)
            // Clear any existing approvers except the creator (which is always there)
            $('#pic-table-body tr.pic-entry:not([data-role="Creator"])').remove();

            // Add each approval as a PIC entry
            @foreach($workflowApprovals as $index => $approval)
                @if($approval->role !== 'CREATOR') // Creator is already added
                    @php
                        $user = App\Models\User::find($approval->user_id);
                        $userName = $user ? $user->name : 'Unknown User';
                        $userUnit = $user ? $user->unit_kerja : 'Unknown Unit';
                        $jabatan = $user ? $user->jabatan : '';
                        // Get the display role
                        $roleDisplay = '';
                        if ($approval->role === 'CREATOR') $roleDisplay = 'Creator';
                        elseif ($approval->role === 'ACKNOWLEDGED_BY_SPV') $roleDisplay = 'Acknowledger';
                        elseif ($approval->role === 'APPROVED_BY_HEAD_UNIT') $roleDisplay = 'Unit Head - Approver';
                        elseif ($approval->role === 'REVIEWED_BY_MAKER') $roleDisplay = 'Reviewer-Maker';
                        elseif ($approval->role === 'REVIEWED_BY_APPROVER') $roleDisplay = 'Reviewer-Approver';
                        else $roleDisplay = $approval->role;

                        $digitalSignature = $approval->digital_signature ? 'checked' : '';
                        $notes = $approval->notes ?? '';
                    @endphp

                    // Add to the table, but don't use addPicEntry since it has side effects
                    // Instead, directly append the HTML
                    const approverHtml_{{ $index }} = `
                        <tr class="pic-entry" data-role="{{ $roleDisplay }}" data-user-id="{{ $approval->user_id }}">
                            <td>
                                <strong>{{ $userName }}</strong>
                                <small class="d-block text-muted">{{ $userUnit }}</small>
                                <input type="hidden" name="pics[{{ $index }}][user_id]" value="{{ $approval->user_id }}">
                            </td>
                            <td>
                                <span class="role-badge
                                    @if($roleDisplay == 'Acknowledger') role-acknowledger
                                    @elseif($roleDisplay == 'Unit Head - Approver') role-head
                                    @elseif($roleDisplay == 'Reviewer-Maker') role-reviewer-maker
                                    @elseif($roleDisplay == 'Reviewer-Approver') role-reviewer-approver
                                    @endif">{{ $roleDisplay }}</span>
                                <input type="hidden" name="pics[{{ $index }}][role]" value="{{ $roleDisplay }}">
                            </td>
                            <td>
                                {{ $jabatan }}
                                <input type="hidden" name="pics[{{ $index }}][jabatan]" value="{{ $jabatan }}">
                            </td>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="pics[{{ $index }}][digital_signature]" value="1" {{ $digitalSignature }}>
                                    <label class="form-check-label">Use Digital Signature</label>
                                </div>
                            </td>
                            <td>
                                <textarea name="pics[{{ $index }}][notes]" class="form-control form-control-sm" placeholder="Notes (optional)" rows="2">{{ $notes }}</textarea>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-pic">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                    $('#pic-table-body').append(approverHtml_{{ $index }});
                @endif
            @endforeach

            // Update display state (show empty message if needed)
            if ($("#pic-table-body tr.pic-entry").length > 1) {
                $("#empty-workflow-message").addClass('d-none');
            } else {
                $("#empty-workflow-message").removeClass('d-none');
            }
        @endif
    </script>

    <!-- Include component scripts -->
    @include('workflows.create.scripts.currency-formatter')
    @include('workflows.create.scripts.main-script')
    @include('workflows.create.scripts.workflow-components')
    @include('workflows.create.scripts.document-handling')

    <!-- Additional script for handling existing documents -->
    <script>
        // Function to add existing document to the list
        function addExistingDocumentToList(document) {
            const documentList = document.getElementById('documentList');
            const documentTableHeader = document.getElementById('documentTableHeader');

            // Make sure the table header is visible
            documentTableHeader.style.display = '';

            // Get file extension and icon
            const extension = document.fileType.toLowerCase();
            const fileIcon = getFileIcon(extension);

            // Create a new table row
            const row = document.createElement('tr');
            row.setAttribute('data-document-id', document.id);

            // Set row content for existing document
            row.innerHTML = `
                <td>${documentList.querySelectorAll('tr').length + 1}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="fas ${fileIcon} mr-2 text-primary"></i>
                        <span>${document.fileName}.${document.fileType}</span>
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control" value="${document.documentName}" readonly>
                    <input type="hidden" name="existing_document_ids[]" value="${document.id}">
                </td>
                <td>
                    <select class="form-control" name="existing_document_categories[${document.id}]">
                        <option value="MAIN" ${document.category === 'MAIN' ? 'selected' : ''}>Main Document</option>
                        <option value="SUPPORTING" ${document.category === 'SUPPORTING' ? 'selected' : ''}>Supporting Document</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control" value="${document.uploader}" readonly>
                </td>
                <td>${document.fileSize}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="/${document.filePath}" target="_blank" class="btn btn-outline-info preview-btn" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger remove-existing-doc" title="Remove" data-document-id="${document.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <input type="hidden" name="document_sequence[${document.id}]" value="${documentList.querySelectorAll('tr').length}">
                </td>
            `;

            documentList.appendChild(row);

            // Add event listener for removing existing document
            const removeBtn = row.querySelector('.remove-existing-doc');
            removeBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to remove this document?')) {
                    // Add the document ID to the list of documents to remove
                    const removeInput = document.createElement('input');
                    removeInput.type = 'hidden';
                    removeInput.name = 'remove_documents[]';
                    removeInput.value = document.id;
                    document.getElementById('workflow-form').appendChild(removeInput);

                    // Remove the row from the table
                    row.remove();

                    // Update sequence numbers
                    updateSequenceNumbers();

                    // Update table header visibility
                    updateTableHeaderVisibility();
                }
            });
        }

        // Helper function to get file icon based on extension (used in addExistingDocumentToList)
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

        // Update sequence numbers after reordering
        function updateSequenceNumbers() {
            const rows = document.querySelectorAll('#documentList tr');
            rows.forEach((row, index) => {
                // Update sequence number in the first cell
                const seqCell = row.querySelector('td:first-child');
                if (seqCell) {
                    seqCell.textContent = index + 1;
                }

                // Update hidden sequence input
                const sequenceInput = row.querySelector('input[name^="document_sequence"]');
                if (sequenceInput) {
                    sequenceInput.value = index;
                }
            });
        }

        // Update table header visibility
        function updateTableHeaderVisibility() {
            const documentList = document.getElementById('documentList');
            const documentTableHeader = document.getElementById('documentTableHeader');
            const hasRows = documentList.querySelectorAll('tr').length > 0;
            documentTableHeader.style.display = hasRows ? '' : 'none';
        }
    </script>
@endsection
