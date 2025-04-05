@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Master User</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Master User</li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .role-badge {
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 5px;
            padding: 5px 10px;
            background-color: #f0f0f0;
            border-radius: 3px;
        }
        .role-badge .remove-role {
            margin-left: 5px;
            cursor: pointer;
            color: red;
        }
        #roleOptions div {
            cursor: pointer;
            padding: 5px;
            margin-bottom: 5px;
            background-color: #f8f9fa;
            border-radius: 3px;
        }
        #roleOptions div:hover {
            background-color: #e9ecef;
        }
    </style>
@endpush

@section('page-wrapper')
    @include('main.components.message')

    <div class="card border-primary">
        <div class="card-body">
            <h3 class="card-title">Edit Master User</h3>
            <hr>

            <form action="{{ route('admin.master-user.update', $masterUser->id) }}" method="post" id="masterUserForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>NIK</label>
                            <input type="text" class="form-control" value="{{ $masterUser->nik }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" class="form-control" value="{{ $masterUser->name }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Unit Kerja</label>
                            <input type="text" class="form-control" value="{{ $masterUser->unit_kerja }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Jabatan</label>
                            <input type="text" class="form-control" value="{{ $masterUser->jabatan }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Created Date</label>
                            <input type="text" class="form-control" value="{{ $masterUser->created_at->format('d-M-Y') }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Created By</label>
                            <input type="text" class="form-control" value="{{ $masterUser->created_by }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Edited Date</label>
                            <input type="text" class="form-control" value="{{ now()->format('d-M-Y') }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Edited By</label>
                            <input type="text" class="form-control" value="{{ getAuthName() }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <div class="d-flex">
                                <div class="form-check mr-3">
                                    <input type="radio" class="form-check-input" name="status" value="Active" id="statusActive"
                                        {{ $masterUser->status == 'Active' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="statusActive">Active</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="status" value="Not Active" id="statusNotActive"
                                        {{ $masterUser->status == 'Not Active' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="statusNotActive">Not Active</label>
                                </div>
                            </div>
                            @error('status')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Roles</h5>
                                <small class="text-white">1 NIK can be more than 1 role (Creator/Admin)</small>
                            </div>
                            <div class="card-body">
                                <div id="selectedRoles" class="mb-3">
                                    <!-- Selected roles will appear here -->
                                </div>

                                <button type="button" class="btn btn-secondary" id="addRoleBtn">Add Role</button>

                                <div id="roleModal" class="modal fade" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Select Role</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div id="roleOptions">
                                                    <div data-role="Admin">Admin</div>
                                                    <div data-role="Creator">Creator</div>
                                                    <div data-role="Acknowledger">Acknowledger</div>
                                                    <div data-role="Unit Head - Approver">Unit Head - Approver</div>
                                                    <div data-role="Reviewer-Maker">Reviewer-Maker</div>
                                                    <div data-role="Reviewer-Approver">Reviewer-Approver</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <table id="roleTable" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Role</th>
                                                <th>Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Roles will be displayed here -->
                                        </tbody>
                                    </table>
                                </div>

                                @error('roles')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                        <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('app-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Role management
            let roleCount = 0;
            const selectedRoles = new Set();

            // Initialize with existing roles
            @foreach($masterUser->roles as $role)
                selectedRoles.add("{{ $role->role }}");
            @endforeach

            function updateRolesTable() {
                const tbody = $('#roleTable tbody');
                tbody.empty();

                if (selectedRoles.size === 0) {
                    tbody.append('<tr><td colspan="3" class="text-center">No roles selected</td></tr>');
                    return;
                }

                let counter = 1;
                selectedRoles.forEach(role => {
                    const row = `
                        <tr>
                            <td>${counter}</td>
                            <td>${role}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm delete-role" data-role="${role}">Delete</button>
                                <input type="hidden" name="roles[]" value="${role}">
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                    counter++;
                });
            }

            // Initialize roles table
            updateRolesTable();

            // Show role modal
            $('#addRoleBtn').click(function() {
                $('#roleModal').modal('show');
            });

            // Add role when clicked in modal
            $('#roleOptions div').click(function() {
                const role = $(this).data('role');

                if (!selectedRoles.has(role)) {
                    selectedRoles.add(role);
                    updateRolesTable();
                }

                $('#roleModal').modal('hide');
            });

            // Remove role when delete button is clicked
            $(document).on('click', '.delete-role', function() {
                const role = $(this).data('role');
                selectedRoles.delete(role);
                updateRolesTable();
            });

            // Handle cancel button
            $('#cancelBtn').click(function() {
                if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
                    window.location.href = "{{ route('admin.master-user.index') }}";
                }
            });

            // Form submission validation
            $('#masterUserForm').submit(function(e) {
                // Check if at least one role is selected
                if (selectedRoles.size === 0) {
                    e.preventDefault();
                    alert('Please select at least one role');
                    return;
                }
            });
        });
    </script>
@endsection
