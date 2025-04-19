{{-- resources/views/workflows/edit/scripts/document-handling.blade.php --}}

<script>
    function initDocumentHandling() {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const documentList = document.getElementById('documentList');
        const fileActions = document.getElementById('fileActions');
        const addMoreBtn = document.getElementById('addMoreBtn');
        const clearAllBtn = document.getElementById('clearAllBtn');

        // Files array to track uploaded files
        let uploadedFiles = [];

        // Track existing documents for edit mode
        let existingDocuments = [];

        // Initialize the drag and drop functionality
        if (dropZone) {
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('dragover');
            });

            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('dragover');
            });

            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('dragover');

                const files = e.dataTransfer.files;
                handleFiles(files);
            });

            // Click to browse files
            dropZone.addEventListener('click', () => {
                fileInput.click();
            });
        }

        // Handle selected files from file input
        if (fileInput) {
            fileInput.addEventListener('change', () => {
                handleFiles(fileInput.files);
            });
        }

        // Add more files button
        if (addMoreBtn) {
            addMoreBtn.addEventListener('click', () => {
                fileInput.click();
            });
        }

        // Clear all files button
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', () => {
                if (confirm('Are you sure you want to clear all new documents? This will not remove existing documents.')) {
                    uploadedFiles = [];
                    // Only remove rows that don't have a data-document-id attribute (new uploads)
                    const newDocumentRows = document.querySelectorAll('#documentList tr:not([data-document-id])');
                    newDocumentRows.forEach(row => row.remove());

                    fileInput.value = '';

                    // If no rows left, hide actions
                    if (document.querySelectorAll('#documentList tr').length === 0) {
                        fileActions.style.display = 'none';
                        dropZone.style.display = 'block';
                        // Reset the drop zone to original state
                        dropZone.style.height = '';
                        dropZone.style.padding = '';
                        dropZone.innerHTML = `
                            <p><i class="fas fa-cloud-upload-alt fa-2x mb-2"></i></p>
                            <p>Drag & drop files here or click to browse</p>
                        `;
                    }

                    updateSequenceNumbers();
                    updateTableHeaderVisibility();
                }
            });
        }

        // Handle removing existing documents
        $(document).on('click', '.remove-existing-doc', function() {
            if (confirm('Are you sure you want to remove this document?')) {
                const documentId = $(this).data('document-id');

                // Add a hidden input to track removed documents
                $('<input>').attr({
                    type: 'hidden',
                    name: 'remove_documents[]',
                    value: documentId
                }).appendTo('#workflow-form');

                // Remove the row
                $(this).closest('tr').remove();

                updateSequenceNumbers();
                updateTableHeaderVisibility();
            }
        });

        // Handle the uploaded files
        function handleFiles(files) {
            if (files.length > 0) {
                // Show file actions
                if (fileActions) {
                    fileActions.style.display = 'block';
                }

                // Process each file
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];

                    // Check if file is already in the list
                    if (uploadedFiles.some(f => f.name === file.name && f.size === file.size)) {
                        continue;
                    }

                    // Add file to array
                    uploadedFiles.push(file);

                    // For document component that uses table structure
                    addFileToDocumentTable(file);
                }

                // Minimize the drop zone
                if (dropZone) {
                    dropZone.style.height = 'auto';
                    dropZone.style.padding = '10px';
                    dropZone.innerHTML = '<p><i class="fas fa-cloud-upload-alt"></i> Drop files here or click to browse</p>';
                }
            }
        }

        // Add file to document table structure (used in document-upload-component)
        function addFileToDocumentTable(file) {
            const extension = file.name.split('.').pop().toLowerCase();
            const fileIcon = getFileIcon(extension);
            const fileSize = formatFileSize(file.size);
            const fileId = 'new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

            // Get current count of rows to set sequence
            const currentIndex = documentList.querySelectorAll('tr').length;

            // Create a new table row
            const row = document.createElement('tr');
            row.setAttribute('data-file-id', fileId);

            // Set row content for new file
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

                    // Remove from uploaded files array
                    const fileIndex = uploadedFiles.findIndex(f => f.name === file.name && f.size === file.size);
                    if (fileIndex !== -1) {
                        uploadedFiles.splice(fileIndex, 1);
                    }

                    // Hide actions if no files left
                    if (documentList.querySelectorAll('tr').length === 0) {
                        if (fileActions) fileActions.style.display = 'none';

                        // Reset the drop zone to original state
                        if (dropZone) {
                            dropZone.style.height = '';
                            dropZone.style.padding = '';
                            dropZone.innerHTML = `
                                <p><i class="fas fa-cloud-upload-alt fa-2x mb-2"></i></p>
                                <p>Drag & drop files here or click to browse</p>
                            `;
                        }
                    }
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

        // Format file size (bytes to KB/MB)
        function formatFileSize(bytes) {
            if (bytes < 1024) {
                return bytes + ' B';
            } else if (bytes < 1048576) {
                return (bytes / 1024).toFixed(2) + ' KB';
            } else {
                return (bytes / 1048576).toFixed(2) + ' MB';
            }
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

        // Update table header visibility
        function updateTableHeaderVisibility() {
            const documentTableHeader = document.getElementById('documentTableHeader');
            if (!documentTableHeader) return;

            const hasRows = documentList.querySelectorAll('tr').length > 0;
            documentTableHeader.style.display = hasRows ? '' : 'none';
        }

        // Initialize table header visibility on page load
        updateTableHeaderVisibility();

        // Check for existing files
        function checkExistingFiles() {
            // If there are files already attached (either from create or edit)
            const existingDocRows = document.querySelectorAll('tr[data-document-id]');
            const newDocRows = document.querySelectorAll('tr[data-file-id]');

            if (existingDocRows.length > 0 || newDocRows.length > 0) {
                if (fileActions) fileActions.style.display = 'block';

                // Minimize the drop zone
                if (dropZone) {
                    dropZone.style.height = 'auto';
                    dropZone.style.padding = '10px';
                    dropZone.innerHTML = '<p><i class="fas fa-cloud-upload-alt"></i> Drop files here or click to browse</p>';
                }
            }
        }

        // Add existing document to list for edit mode
        window.addExistingDocumentToList = function(document) {
            if (!documentList) return;

            // Make table header visible
            const documentTableHeader = document.getElementById('documentTableHeader');
            if (documentTableHeader) documentTableHeader.style.display = '';

            // Create a new row for existing document
            const row = document.createElement('tr');
            row.setAttribute('data-document-id', document.id);

            // Get file extension and icon
            const extension = document.fileType.toLowerCase();
            const fileIcon = getFileIcon(extension);

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
                        <option value="SUPPORTING" ${document.category === 'SUPPORTING' || !document.category ? 'selected' : ''}>Supporting Document</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control" value="${document.uploader}" readonly>
                </td>
                <td>${document.fileSize || 'N/A'}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="/${document.filePath}" target="_blank" class="btn btn-outline-info" title="View">
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
            existingDocuments.push(document.id);

            // Show actions bar when we have documents
            if (fileActions) fileActions.style.display = 'block';

            // Minimize the drop zone if we have documents
            if (dropZone) {
                dropZone.style.height = 'auto';
                dropZone.style.padding = '10px';
                dropZone.innerHTML = '<p><i class="fas fa-cloud-upload-alt"></i> Drop files here or click to browse</p>';
            }
        };

        // Initialize with any existing files
        checkExistingFiles();
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        initDocumentHandling();
    });
</script>
