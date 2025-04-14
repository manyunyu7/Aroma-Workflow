<div class="drop-zone" id="dropZone">
    <p><i class="fas fa-cloud-upload-alt fa-2x mb-2"></i></p>
    <p>Drag & drop files here or click to browse</p>
    <input type="file" id="fileInput" name="documents[]" multiple style="display: none;">
</div>

<div id="fileActions" class="mt-2" style="display: none;">
    <button type="button" class="btn btn-sm btn-primary" id="addMoreBtn">
        <i class="fas fa-plus"></i> Add More Files
    </button>
    <button type="button" class="btn btn-sm btn-danger" id="clearAllBtn">
        <i class="fas fa-trash"></i> Clear All
    </button>
</div>

<div id="documentList" class="document-list"></div>

<!-- Template for document item (hidden) -->
<div id="documentItemTemplate" style="display: none;">
    <div class="document-item">
        <div class="document-info">
            <i class="fas fa-file mr-2"></i>
            <span class="document-name"></span>
            <span class="document-size text-muted ml-2"></span>
        </div>
        <div class="document-actions">
            <button type="button" class="btn btn-sm btn-outline-danger remove-document">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>
