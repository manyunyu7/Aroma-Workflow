resources/views/workflows/create/scripts/document-handling.blade.php

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

        // Initialize the drag and drop functionality
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

        // Handle selected files from file input
        fileInput.addEventListener('change', () => {
            handleFiles(fileInput.files);
        });

        // Add more files button
        addMoreBtn.addEventListener('click', () => {
            fileInput.click();
        });

        // Clear all files button
        clearAllBtn.addEventListener('click', () => {
            uploadedFiles = [];
            documentList.innerHTML = '';
            fileInput.value = '';
            fileActions.style.display = 'none';
            dropZone.style.display = 'block';

            // Reset the drop zone to original state
            dropZone.style.height = '';
            dropZone.style.padding = '';
            dropZone.innerHTML = `
                <p><i class="fas fa-cloud-upload-alt fa-2x mb-2"></i></p>
                <p>Drag & drop files here or click to browse</p>
            `;
        });

        // Handle the uploaded files
        function handleFiles(files) {
            if (files.length > 0) {
                // Show file actions
                fileActions.style.display = 'block';

                // Process each file
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];

                    // Check if file is already in the list
                    if (uploadedFiles.some(f => f.name === file.name && f.size === file.size)) {
                        continue;
                    }

                    // Add file to array
                    uploadedFiles.push(file);

                    // Create document item from template
                    const template = document.getElementById('documentItemTemplate');
                    const documentItem = template.cloneNode(true);
                    documentItem.id = '';
                    documentItem.style.display = 'block';

                    // Set file name and size
                    const nameElement = documentItem.querySelector('.document-name');
                    const sizeElement = documentItem.querySelector('.document-size');
                    nameElement.textContent = file.name;
                    sizeElement.textContent = formatFileSize(file.size);

                    // Add remove button event
                    const removeBtn = documentItem.querySelector('.remove-document');
                    removeBtn.addEventListener('click', () => {
                        // Remove from list
                        documentList.removeChild(documentItem);

                        // Remove from array
                        const fileIndex = uploadedFiles.findIndex(f => f.name === file.name && f.size === file.size);
                        if (fileIndex !== -1) {
                            uploadedFiles.splice(fileIndex, 1);
                        }

                        // Hide actions if no files left
                        if (uploadedFiles.length === 0) {
                            fileActions.style.display = 'none';

                            // Reset the drop zone to original state
                            dropZone.style.height = '';
                            dropZone.style.padding = '';
                            dropZone.innerHTML = `
                                <p><i class="fas fa-cloud-upload-alt fa-2x mb-2"></i></p>
                                <p>Drag & drop files here or click to browse</p>
                            `;
                        }
                    });

                    // Add to document list
                    documentList.appendChild(documentItem);
                }

                // Minimize the drop zone
                dropZone.style.height = 'auto';
                dropZone.style.padding = '10px';
                dropZone.innerHTML = '<p><i class="fas fa-cloud-upload-alt"></i> Drop files here or click to browse</p>';
            }
        }

        // Format file size (bytes to KB/MB)
        function formatFileSize(bytes) {
            if (bytes < 1024) {
                return bytes + ' B';
            } else if (bytes < 1048576) {
                return (bytes / 1024).toFixed(1) + ' KB';
            } else {
                return (bytes / 1048576).toFixed(1) + ' MB';
            }
        }

        // Check for existing files
        function checkExistingFiles() {
            // If there are files already attached (e.g., when editing a form)
            // this could be populated from server-side data
            const existingFiles = document.querySelectorAll('[name^="existing_documents"]');

            if (existingFiles.length > 0) {
                fileActions.style.display = 'block';

                // Minimize the drop zone
                dropZone.style.height = 'auto';
                dropZone.style.padding = '10px';
                dropZone.innerHTML = '<p><i class="fas fa-cloud-upload-alt"></i> Drop files here or click to browse</p>';
            }
        }

        // Initialize with any existing files
        checkExistingFiles();
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        initDocumentHandling();
    });
    </script>
