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
