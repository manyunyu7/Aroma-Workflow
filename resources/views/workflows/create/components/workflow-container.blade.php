<div class="workflow-container mb-4">
    <div class="current-workflow">
        <button type="button" class="btn btn-success btn-sm mb-3" id="add-pic-btn">
            <i class="fas fa-plus"></i> Add Approver
        </button>

        <div id="pic-container" class="mb-3">
            <!-- The first row always has the creator (current user) -->
            <div class="pic-entry" data-role="Creator" data-user-id="{{ $user->id }}">
                <span class="role-badge role-creator">Creator</span>
                <div class="row mt-2">
                    <div class="col-md-4">
                        <strong>{{ $user->name }}</strong>
                        <input type="hidden" name="pics[0][user_id]" value="{{ $user->id }}">
                        <input type="hidden" name="pics[0][role]" value="Creator">
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">{{ $user->unit_kerja }}</small>
                        <input type="hidden" name="pics[0][jabatan]"
                            value="{{ $user->jabatan ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input"
                                name="pics[0][digital_signature]" value="1"
                                {{ old('pics.0.digital_signature') ? 'checked' : '' }}>
                            <label class="form-check-label">Use Digital Signature</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <!-- No remove button for creator -->
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <textarea name="pics[0][notes]" class="form-control" placeholder="Notes (optional)">{{ old('pics.0.notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
