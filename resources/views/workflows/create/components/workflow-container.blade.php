{{-- resources/views/workflows/create/components/workflow-container.blade.php --}}
<!-- Updated Workflow Container with Table Structure -->
<div class="workflow-container mb-4">
    <div class="current-workflow">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Approval Workflow</h5>
            <button type="button" id="add-pic-btn" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Add Approver
            </button>
        </div>

        <div id="pic-container" class="mb-3">
            <!-- Table structure for approvers -->
            <table class="table table-bordered">
                <thead class="bg-light">
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Jabatan</th>
                        <th>Digital Signature</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="pic-table-body">
                    <!-- The first row always has the creator (current user) -->
                    <tr class="pic-entry" data-role="Creator" data-user-id="{{ $user->id }}">
                        <td>
                            <strong>{{ $user->name }}</strong>
                            <small class="d-block text-muted">{{ $user->unit_kerja }}</small>
                            <input type="hidden" name="pics[0][user_id]" value="{{ $user->id }}">
                        </td>
                        <td>
                            <span class="role-badge role-creator">Creator</span>
                            <input type="hidden" name="pics[0][role]" value="Creator">
                        </td>
                        <td>
                            {{ $user->jabatan ?? 'N/A' }}
                            <input type="hidden" name="pics[0][jabatan]" value="{{ $user->jabatan ?? '' }}">
                        </td>
                        <td>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="pics[0][digital_signature]" value="1" {{ old('pics.0.digital_signature') ? 'checked' : '' }}>
                                <label class="form-check-label">Use Digital Signature</label>
                            </div>
                        </td>
                        <td>
                            <textarea name="pics[0][notes]" class="form-control form-control-sm" placeholder="Notes (optional)" rows="2">{{ old('pics.0.notes') }}</textarea>
                        </td>
                        <td>
                            <!-- No remove button for creator -->
                            <span class="badge badge-secondary">Required</span>
                        </td>
                    </tr>

                    <!-- Additional approvers will be added here via JavaScript -->
                </tbody>
            </table>

            <!-- Empty state message will only show when there's no additional approvers -->
            <div id="empty-workflow-message" class="text-center py-4 d-none">
                <i class="fas fa-user-clock fa-2x text-muted mb-2"></i>
                <p class="text-muted">No additional approvers added yet. Click "Add Approver" to build your workflow.</p>
            </div>
        </div>
    </div>
</div>
