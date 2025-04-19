{{-- resources/views/workflows/create/components/document-upload-component.blade.php --}}

{{-- Check if we're in edit mode --}}
@php
    $isEdit = isset($workflow) && $workflow->exists;
    $workflowDocuments = $isEdit ? App\Models\WorkflowDocument::where('workflow_id', $workflow->id)->orderBy('sequence', 'asc')->get() : collect([]);
@endphp

@push('styles')
<style>
    .custom-file-input:lang(en)~.custom-file-label::after {
        content: "Browse";
    }

    .document-row {
        vertical-align: middle;
    }

    .document-actions .btn {
        padding: 0.25rem 0.5rem;
    }

    .pdf-container {
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>
@endpush

   <!-- File Selection -->
   <div class="mb-3">
    <div class="input-group">
        <div class="custom-file">
            <input type="file" class="custom-file-input" id="singleDocument" accept="application/pdf">
            <label class="custom-file-label" for="singleDocument">Choose a file</label>
        </div>
        <div class="input-group-append">
            <button class="btn btn-primary" type="button" id="addDocumentBtn">
                <i class="fas fa-plus"></i> Add
            </button>
        </div>
    </div>
    <small class="text-muted">Allowed file types: PDF</small>
</div>

<!-- Document Table -->
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-light" id="documentTableHeader" style="{{ ($isEdit && $workflowDocuments->count() > 0) ? '' : 'display: none;' }}">
            <tr>
                <th width="5%">#</th>
                <th width="20%">File</th>
                <th width="20%">Document Name</th>
                <th width="15%">Category</th>
                <th width="15%">Uploader</th>
                <th width="10%">Size</th>
                <th width="15%">Actions</th>
            </tr>
        </thead>
        <tbody id="documentList">
            <!-- In edit mode, show existing documents -->
            @if($isEdit && $workflowDocuments->count() > 0)
                @foreach($workflowDocuments as $index => $document)
                    <tr data-document-id="{{ $document->id }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-pdf mr-2 text-primary"></i>
                                <span>{{ $document->file_name }}.{{ $document->file_type }}</span>
                            </div>
                        </td>
                        <td>
                            <input type="text" class="form-control" value="{{ $document->file_name }}" readonly>
                            <input type="hidden" name="existing_document_ids[]" value="{{ $document->id }}">
                        </td>
                        <td>
                            <select class="form-control" name="existing_document_categories[{{ $document->id }}]">
                                <option value="MAIN" {{ $document->document_category === 'MAIN' ? 'selected' : '' }}>Main Document</option>
                                <option value="SUPPORTING" {{ $document->document_category === 'SUPPORTING' || !$document->document_category ? 'selected' : '' }}>Supporting Document</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control" value="{{ App\Models\User::find($document->uploaded_by)->name ?? 'Unknown' }}" readonly>
                        </td>
                        <td>{{ $document->file_size ?? 'N/A' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/{{ $document->file_path }}" target="_blank" class="btn btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger remove-existing-doc" title="Remove" data-document-id="{{ $document->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <input type="hidden" name="document_sequence[{{ $document->id }}]" value="{{ $index }}">
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>

<!-- File Preview Modal -->
<div class="modal fade" id="filePreviewModal" tabindex="-1" role="dialog" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filePreviewModalLabel">File Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Preview content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const singleDocumentInput = document.getElementById('singleDocument');
    const fileLabel = document.querySelector('.custom-file-label');
    const documentList = document.getElementById('documentList');
    const documentTableHeader = document.getElementById('documentTableHeader');
    const addDocumentBtn = document.getElementById('addDocumentBtn');
    let documentItemsCount = {{ $isEdit ? $workflowDocuments->count() : 0 }};
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

    // Update table header visibility
    function updateTableHeaderVisibility() {
        const hasRows = documentList.querySelectorAll('tr').length > 0;
        documentTableHeader.style.display = hasRows ? '' : 'none';
    }

    // Update sequence numbers after reordering
    function updateSequenceNumbers() {
        const rows = documentList.querySelectorAll('tr');
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

    // Move row up in the table
    function moveRowUp(row) {
        const prevRow = row.previousElementSibling;
        if (prevRow) {
            documentList.insertBefore(row, prevRow);
            updateSequenceNumbers();
        }
    }

    // Move row down in the table
    function moveRowDown(row) {
        const nextRow = row.nextElementSibling;
        if (nextRow) {
            documentList.insertBefore(nextRow, row);
            updateSequenceNumbers();
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
                modalBody.innerHTML = `<img src="${e.target.result}" class="img-fluid" alt="${file.name}">`;
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

    // Add file to document list as a table row
    function addFileToList(file) {
        const extension = file.name.split('.').pop().toLowerCase();
        const fileIcon = getFileIcon(extension);
        const fileSize = formatFileSize(file.size);
        const fileId = 'new_' + documentItemsCount;
        documentItemsCount++;

        // Get current count of rows to set sequence
        const currentIndex = documentList.querySelectorAll('tr').length;

        // Create a new table row
        const row = document.createElement('tr');
        row.setAttribute('data-file-id', fileId);

        // Set row content
        row.innerHTML = `
            <td>${currentIndex + 1}</td>
            <td>
                <div class="d-flex align-items-center">
                    <i class="fas ${fileIcon} mr-2 text-primary"></i>
                    <span>${file.name}</span>
                </div>
            </td>
            <td>
                <input type="text" class="form-control" name="document_types[${fileId}]" placeholder="Enter document name" required>
            </td>
            <td>
                <select class="form-control" name="document_categories[${fileId}]" required>
                    <option value="MAIN">Main Document</option>
                    <option value="SUPPORTING" selected>Supporting Document</option>
                </select>
            </td>
            <td>
                <input type="text" class="form-control" name="document_notes[${fileId}]" value="{{ Auth::user()->name }}" readonly>
            </td>
            <td>${fileSize}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary move-up-btn" title="Move Up">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary move-down-btn" title="Move Down">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                    <button type="button" class="btn btn-outline-info preview-btn" title="Preview">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger remove-btn" title="Remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <input type="hidden" name="document_sequence[${fileId}]" value="${currentIndex}">
                <input type="file" name="documents[]" style="display: none;" class="document-file-input" id="document-file-${fileId}">
            </td>
        `;

        documentList.appendChild(row);

        // Show table header when we add the first row
        updateTableHeaderVisibility();

        // Get the hidden file input and set its files
        const fileInput = row.querySelector('.document-file-input');

        // Create a new FileList-like object
        const container = new DataTransfer();
        container.items.add(file);
        fileInput.files = container.files;

        // Add event listeners for actions
        const previewBtn = row.querySelector('.preview-btn');
        previewBtn.addEventListener('click', function() {
            previewFile(file);
        });

        const removeBtn = row.querySelector('.remove-btn');
        removeBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to remove this file?')) {
                row.remove();
                updateSequenceNumbers();
                updateTableHeaderVisibility();
            }
        });

        const moveUpBtn = row.querySelector('.move-up-btn');
        moveUpBtn.addEventListener('click', function() {
            moveRowUp(row);
        });

        const moveDownBtn = row.querySelector('.move-down-btn');
        moveDownBtn.addEventListener('click', function() {
            moveRowDown(row);
        });

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
            // Check file size (5MB limit)
            if (selectedFile.size > 5 * 1024 * 1024) {
                alert('File size exceeds 5MB limit');
                return;
            }

            // Check file extension
            const extension = selectedFile.name.split('.').pop().toLowerCase();
            const allowedExtensions = ['pdf'];

            if (!allowedExtensions.includes(extension)) {
                alert('Invalid file type. Allowed types: PDF');
                return;
            }

            addFileToList(selectedFile);
        } else {
            alert('Please select a file first');
        }
    });

    // Handle removing existing documents (in edit mode)
    $(document).on('click', '.remove-existing-doc', function() {
        if (confirm('Are you sure you want to remove this document?')) {
            const documentId = $(this).data('document-id');

            // Add hidden input to mark this document for removal on the server
            $('<input>').attr({
                type: 'hidden',
                name: 'remove_documents[]',
                value: documentId
            }).appendTo('form');

            // Remove the row from the UI
            $(this).closest('tr').remove();

            // Update the display
            updateSequenceNumbers();
            updateTableHeaderVisibility();
        }
    });

    // Initialize table header visibility on page load
    updateTableHeaderVisibility();
});
</script>
@endpush
