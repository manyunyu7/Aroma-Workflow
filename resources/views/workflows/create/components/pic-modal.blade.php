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
                        <small class="form-text text-muted">The role determines the approval level in the
                            workflow</small>
                    </div>

                    <div id="reviewer-approver-section" class="mt-3 d-none">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            When selecting a Reviewer-Maker, you'll also need to select a corresponding
                            Reviewer-Approver.
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
