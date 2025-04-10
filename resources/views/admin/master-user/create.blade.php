@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Master User</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Master User</li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Create</li>
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
            <h3 class="card-title">Master User</h3>
            <hr>

            <form action="{{ route('admin.master-user.store') }}" method="post" id="masterUserForm">
                @csrf

                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>NIK</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik"
                                    name="nik" value="{{ old('nik') }}" placeholder="Enter NIK">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" id="checkNikBtn">Check</button>
                                </div>
                                @error('nik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" class="form-control" id="nama" readonly>
                        </div>

                        <div class="form-group">
                            <label>Unit Kerja</label>
                            <input type="text" class="form-control" id="unit_kerja" readonly>
                        </div>

                        <div class="form-group">
                            <label>Jabatan</label>
                            <input type="text" class="form-control" id="jabatan" readonly>
                        </div>

                        <div class="form-group">
                            <label>Object ID</label>
                            <input type="text" class="form-control" id="object_id" name="object_id" readonly>
                        </div>

                        <div class="form-group">
                            <label>Created Date</label>
                            <input type="text" class="form-control" value="{{ now()->format('d-M-Y') }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Created By</label>
                            <input type="text" class="form-control" value="{{ getAuthName() }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <div class="d-flex">
                                <div class="form-check mr-3">
                                    <input type="radio" class="form-check-input" name="status" value="Active"
                                        id="statusActive" checked>
                                    <label class="form-check-label" for="statusActive">Active</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="status" value="Not Active"
                                        id="statusNotActive">
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

                                <!-- Role Selection Modal -->
                                <div id="roleModal" class="modal fade" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Select Role</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div id="roleOptions">
                                                    <div class="mb-3" data-role="Admin">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span>Admin</span>
                                                            <button type="button" class="btn btn-sm btn-primary select-role">Select</button>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3" data-role="Creator">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span>Creator</span>
                                                            <button type="button" class="btn btn-sm btn-primary select-role">Select</button>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3" data-role="Acknowledger">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span>Acknowledger</span>
                                                            <button type="button" class="btn btn-sm btn-primary select-role">Select</button>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3" data-role="Unit Head - Approver">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span>Unit Head - Approver</span>
                                                            <button type="button" class="btn btn-sm btn-primary select-role">Select</button>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3" data-role="Reviewer-Maker">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span>Reviewer-Maker</span>
                                                            <button type="button" class="btn btn-sm btn-primary select-role">Select</button>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3" data-role="Reviewer-Approver">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span>Reviewer-Approver</span>
                                                            <button type="button" class="btn btn-sm btn-primary select-role">Select</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Budget Modal -->
                                <div id="budgetModal" class="modal fade" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Set Budget Limits for <span id="roleName"></span></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="minBudget">Minimum Budget (IDR)</label>
                                                    <input type="number" class="form-control" id="minBudget" placeholder="0" min="0">
                                                </div>
                                                <div class="form-group">
                                                    <label for="maxBudget">Maximum Budget (IDR)</label>
                                                    <input type="number" class="form-control" id="maxBudget" placeholder="Enter maximum budget" min="0">
                                                    <small class="form-text text-muted">Leave empty for unlimited budget.</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="button" class="btn btn-primary" id="saveBudgetBtn">Save</button>
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
                                                <th>Min Budget (IDR)</th>
                                                <th>Max Budget (IDR)</th>
                                                <th>Action</th>
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
            // Handle checking NIK
            $('#checkNikBtn').click(function() {
                const nik = $('#nik').val();
                if (!nik) {
                    alert('Please enter a NIK');
                    return;
                }

                // Show loading
                $(this).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Checking...'
                );
                $(this).prop('disabled', true);

                // Make AJAX request to get user details
                $.ajax({
                    url: "{{ route('admin.get-user-details') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        nik: nik
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#nama').val(response.data.name);
                            $('#unit_kerja').val(response.data.unit_kerja);
                            $('#jabatan').val(response.data.jabatan);
                            $('#object_id').val(response.data.object_id);
                        } else {
                            alert('User not found');
                            $('#nama').val('');
                            $('#unit_kerja').val('');
                            $('#jabatan').val('');
                            $('#object_id').val('');
                        }
                    },
                    error: function() {
                        alert('Error fetching user details');
                    },
                    complete: function() {
                        // Reset button
                        $('#checkNikBtn').html('Check');
                        $('#checkNikBtn').prop('disabled', false);
                    }
                });
            });

            // Role management
            let roleCount = 0;
            const selectedRoles = new Map(); // Changed from Set to Map to store additional data

            function updateRolesTable() {
                const tbody = $('#roleTable tbody');
                tbody.empty();

                if (selectedRoles.size === 0) {
                    tbody.append('<tr><td colspan="5" class="text-center">No roles selected</td></tr>');
                    return;
                }

                let counter = 1;
                selectedRoles.forEach((budgetInfo, role) => {
                    const row = `
                <tr>
                    <td>${counter}</td>
                    <td>${role}</td>
                    <td>${formatCurrency(budgetInfo.minBudget || 0)}</td>
                    <td>${budgetInfo.maxBudget ? formatCurrency(budgetInfo.maxBudget) : 'Unlimited'}</td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm edit-budget mr-1" data-role="${role}">Edit Budget</button>
                        <button type="button" class="btn btn-danger btn-sm delete-role" data-role="${role}">Delete</button>
                        <input type="hidden" name="roles[${role}][role]" value="${role}">
                        <input type="hidden" name="roles[${role}][min_budget]" value="${budgetInfo.minBudget || 0}">
                        <input type="hidden" name="roles[${role}][max_budget]" value="${budgetInfo.maxBudget || ''}">
                    </td>
                </tr>
            `;
                    tbody.append(row);
                    counter++;
                });
            }

            function formatCurrency(amount) {
                return new Intl.NumberFormat('id-ID').format(amount);
            }

            // Initialize roles table
            updateRolesTable();

            // Show role modal
            $('#addRoleBtn').click(function() {
                $('#roleModal').modal('show');
            });

            // Add role when clicked in modal
            $(document).on('click', '.select-role', function() {
                const roleDiv = $(this).closest('div[data-role]');
                const role = roleDiv.data('role');

                if (!selectedRoles.has(role)) {
                    // Show budget modal
                    $('#roleName').text(role);
                    $('#minBudget').val('0');
                    $('#maxBudget').val('');
                    $('#roleModal').modal('hide');
                    $('#budgetModal').modal('show');

                    // Store the role temporarily
                    $('#saveBudgetBtn').data('role', role);
                } else {
                    alert('This role is already selected.');
                    $('#roleModal').modal('hide');
                }
            });

            // Save budget limits
            $('#saveBudgetBtn').click(function() {
                const role = $(this).data('role');
                const minBudget = $('#minBudget').val() || 0;
                const maxBudget = $('#maxBudget').val() || null;

                if (minBudget < 0) {
                    alert('Minimum budget cannot be negative.');
                    return;
                }

                if (maxBudget !== null && maxBudget !== '' && parseFloat(maxBudget) < parseFloat(
                    minBudget)) {
                    alert('Maximum budget must be greater than or equal to minimum budget.');
                    return;
                }

                // Save role with budget limits
                selectedRoles.set(role, {
                    minBudget: minBudget,
                    maxBudget: maxBudget
                });

                updateRolesTable();
                $('#budgetModal').modal('hide');
            });

            // Edit budget when edit button is clicked
            $(document).on('click', '.edit-budget', function() {
                const role = $(this).data('role');
                const budgetInfo = selectedRoles.get(role);

                $('#roleName').text(role);
                $('#minBudget').val(budgetInfo.minBudget || 0);
                $('#maxBudget').val(budgetInfo.maxBudget || '');
                $('#saveBudgetBtn').data('role', role);
                $('#budgetModal').modal('show');
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
                // Check if NIK is provided
                if (!$('#nik').val()) {
                    e.preventDefault();
                    alert('Please enter a NIK');
                    return;
                }

                // Check if user details were fetched
                if (!$('#nama').val()) {
                    e.preventDefault();
                    alert('Please check the NIK to fetch user details');
                    return;
                }

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
