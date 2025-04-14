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
