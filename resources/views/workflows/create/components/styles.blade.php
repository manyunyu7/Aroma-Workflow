{{-- resources/views/workflows/create/components/styles.blade.php --}}

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

    /* Workflow styles */
    .pic-entry td {
        vertical-align: middle;
    }

    /* Role badge styles */
    .role-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        color: white;
    }

    .role-acknowledger {
        background-color: #17a2b8;
    }

    .role-head {
        background-color: #6610f2;
    }

    .role-reviewer-maker {
        background-color: #fd7e14;
    }

    .role-reviewer-approver {
        background-color: #20c997;
    }

    .role-creator {
        background-color: #28a745;
    }

    .reviewer-group {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        background-color: #f9f9f9;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .reviewer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eaeaea;
    }

    .reviewer-badge {
        background-color: #6c757d;
        color: white;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 4px;
    }

    /* Modal specific styles */
    #pic-modal .modal-body {
        max-height: 65vh;
        overflow-y: auto;
    }

    #pic-modal hr {
        margin: 20px 0;
        border-color: #eaeaea;
    }

    #role-select {
        font-weight: 500;
    }

    /* Transitions for showing/hiding sections */
    #user-selection-container,
    #reviewer-approver-section {
        transition: all 0.3s ease;
    }

    /* Status indicators for selections */
    .selection-complete {
        color: #28a745;
    }

    .selection-pending {
        color: #dc3545;
    }

    /* Button styles */
    #save-pic-btn {
        transition: opacity 0.3s ease;
    }

    #save-pic-btn.disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    /* Table styles */
    #pic-container table {
        border-collapse: separate;
        border-spacing: 0;
    }

    #pic-container table thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        font-size: 14px;
    }

    #pic-container table tbody tr.pic-entry {
        transition: background-color 0.2s ease;
    }

    #pic-container table tbody tr.pic-entry:hover {
        background-color: #f8f9fa;
    }

    #pic-container table tbody tr[data-role="Creator"] {
        background-color: rgba(40, 167, 69, 0.05);
    }

    #pic-container table tbody tr.reviewer-approver-row {
        background-color: rgba(32, 201, 151, 0.05);
    }

    #pic-container table textarea.form-control-sm {
        min-height: 60px;
        resize: vertical;
    }

    #pic-container table .form-control-sm {
        font-size: 0.875rem;
    }

    /* Empty state styling */
    #empty-workflow-message {
        border: 2px dashed #dee2e6;
        border-radius: 6px;
        color: #6c757d;
    }

    #empty-workflow-message i {
        opacity: 0.6;
    }

    /* Responsive table adjustments */
    @media (max-width: 768px) {
        #pic-container table {
            font-size: 0.85rem;
        }

        #pic-container .role-badge {
            font-size: 10px;
            padding: 3px 6px;
        }
    }
</style>
