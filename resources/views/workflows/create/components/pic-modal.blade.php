<!-- Refactored Modal without steps -->
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
                <!-- Consolidated Form -->
                <div class="form-group">
                    <label>Select Role</label>
                    <select id="role-select" class="form-control">
                        <option value="">-- Select Role --</option>
                        <!-- Options will be populated via JavaScript -->
                    </select>
                    <small class="form-text text-muted">The role determines the approval level in the workflow</small>
                </div>

                <!-- User Selection Section - Shown when role is selected -->
                <div id="user-selection-container" class="mt-4 d-none">
                    <hr>
                    <h5 class="mb-3">Select Approver</h5>

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

                <!-- Reviewer-Approver Section - Only shown when Reviewer-Maker is selected -->
                <div id="reviewer-approver-section" class="mt-4 d-none">
                    <hr>
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
                <button type="button" id="save-pic-btn" class="btn btn-success d-none">Add to Workflow</button>
            </div>
        </div>
    </div>
</div>
