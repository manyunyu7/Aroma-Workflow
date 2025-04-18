@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Approval Matrix</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Approval Matrix</li>
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
        .approver-badge {
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 5px;
            padding: 5px 10px;
            background-color: #f0f0f0;
            border-radius: 3px;
        }

        .approver-badge .remove-approver {
            margin-left: 5px;
            cursor: pointer;
            color: red;
        }

        #approverOptions div {
            cursor: pointer;
            padding: 5px;
            margin-bottom: 5px;
            background-color: #f8f9fa;
            border-radius: 3px;
        }

        #approverOptions div:hover {
            background-color: #e9ecef;
        }

        .table td, .table th {
            vertical-align: middle;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.76563rem;
        }
    </style>
@endpush

@section('page-wrapper')
    @include('main.components.message')

    <div class="card border-primary">
        <div class="card-body">
            <h3 class="card-title">Create Approval Matrix</h3>
            <hr>

            <form action="{{ route('admin.approval-matrix.store') }}" method="post" id="approvalMatrixForm">
                @csrf

                <div class="row">
                    <div class="col-md-12 col-12">
                        <div class="form-group">
                            <label>Matrix Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name') }}" placeholder="Enter matrix name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Budget Range (IDR)</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="min_budget_display">Minimum Budget</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="text" class="form-control @error('min_budget') is-invalid @enderror"
                                               id="min_budget_display" placeholder="0" value="{{ old('min_budget', 0) }}">
                                        <input type="hidden" id="min_budget" name="min_budget" value="{{ old('min_budget', 0) }}">
                                    </div>
                                    @error('min_budget')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="max_budget_display">Maximum Budget</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="text" class="form-control @error('max_budget') is-invalid @enderror"
                                               id="max_budget_display" value="{{ old('max_budget') }}"
                                               placeholder="Leave empty for unlimited">
                                        <input type="hidden" id="max_budget" name="max_budget" value="{{ old('max_budget') }}">
                                    </div>
                                    @error('max_budget')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <small class="form-text text-muted">Values will be automatically formatted with thousand separators as you type</small>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Created Date</label>
                            <input type="text" class="form-control" value="{{ now()->format('d-M-Y') }}" readonly>
                        </div>

                        <div class="form-group">
                            <label>Created By</label>
                            <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
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

                    <div class="col-md-12 col-12 d-none">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Approvers</h5>
                                <small class="text-white">Define the roles that can approve for this budget range</small>
                            </div>
                            <div class="card-body">
                                <div id="selectedApprovers" class="mb-3">
                                    <!-- Selected approvers will appear here -->
                                </div>

                                <button type="button" class="btn btn-secondary" id="addApproverBtn">Add Approver</button>

                                <!-- Approver Selection Modal -->
                                <div id="approverModal" class="modal fade" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Select Approvers</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6>Management Roles</h6>
                                                        <div id="approverOptions" class="management-roles">
                                                            <div class="mb-2" data-approver="VP/OVP Pemilik Program">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>VP/OVP Pemilik Program</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2" data-approver="Direktur Pemilik Program">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>Direktur Pemilik Program</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2" data-approver="VP Finance Plan & Reporting">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>VP Finance Plan & Reporting</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2" data-approver="VP Corp. Strategy">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>VP Corp. Strategy</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2" data-approver="VP Risk Management">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>VP Risk Management</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Director Level</h6>
                                                        <div id="approverOptions" class="director-roles">
                                                            <div class="mb-2" data-approver="Direktur Finance">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>Direktur Finance</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2" data-approver="Direktur Utama">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>Direktur Utama</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2" data-approver="Unit Head - Approver">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>Unit Head - Approver</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <h6>Manager Roles</h6>
                                                        <div id="approverOptions" class="manager-roles">
                                                            <div class="mb-2" data-approver="Mgr. Pemilik Program">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>Mgr. Pemilik Program</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2" data-approver="Mgr. Business Feasibility">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>Mgr. Business Feasibility</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2" data-approver="Mgr. Management Accounting">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>Mgr. Management Accounting</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2" data-approver="Mgr. Governance & Process Evaluation">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>Mgr. Governance & Process Evaluation</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Other Roles</h6>
                                                        <div id="approverOptions" class="other-roles">
                                                            <div class="mb-2" data-approver="Unit Pemilik Program">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>Unit Pemilik Program</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2" data-approver="Reviewer-Maker">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>Reviewer-Maker</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2" data-approver="Reviewer-Approver">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>Reviewer-Approver</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2" data-approver="Acknowledger">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span>Acknowledger</span>
                                                                    <button type="button" class="btn btn-sm btn-primary select-approver">Select</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <table id="approverTable" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Role</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Approvers will be displayed here -->
                                        </tbody>
                                    </table>
                                </div>

                                @error('approvers')
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
            // ===== BUDGET FORMATTING FUNCTIONALITY =====
            // Function to format number with thousand separators
            function formatNumber(num) {
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            // Function to remove all non-numeric characters
            function unformatNumber(str) {
                return str.replace(/[^\d]/g, '');
            }

            // Format display fields on input
            $('#min_budget_display').on('input', function() {
                // Store cursor position
                const cursorPos = this.selectionStart;
                const oldLength = this.value.length;

                // Get raw value and update hidden field
                const rawValue = unformatNumber($(this).val());
                $('#min_budget').val(rawValue);

                // Format the display value
                if (rawValue) {
                    const formattedValue = formatNumber(rawValue);
                    $(this).val(formattedValue);

                    // Adjust cursor position after formatting
                    const newLength = formattedValue.length;
                    const newCursorPos = cursorPos + (newLength - oldLength);
                    this.setSelectionRange(newCursorPos, newCursorPos);
                }
            });

            // Same logic for max budget
            $('#max_budget_display').on('input', function() {
                const cursorPos = this.selectionStart;
                const oldLength = this.value.length;

                const rawValue = unformatNumber($(this).val());
                $('#max_budget').val(rawValue);

                if (rawValue) {
                    const formattedValue = formatNumber(rawValue);
                    $(this).val(formattedValue);

                    const newLength = formattedValue.length;
                    const newCursorPos = cursorPos + (newLength - oldLength);
                    this.setSelectionRange(newCursorPos, newCursorPos);
                }
            });

            // Set initial formatted values
            const minBudgetValue = $('#min_budget').val();
            if (minBudgetValue) {
                $('#min_budget_display').val(formatNumber(minBudgetValue));
            }

            const maxBudgetValue = $('#max_budget').val();
            if (maxBudgetValue) {
                $('#max_budget_display').val(formatNumber(maxBudgetValue));
            }

            // ===== APPROVER MANAGEMENT FUNCTIONALITY =====
            // Approver management
            const selectedApprovers = new Set();

            function updateApproversTable() {
                const tbody = $('#approverTable tbody');
                tbody.empty();

                if (selectedApprovers.size === 0) {
                    tbody.append('<tr><td colspan="3" class="text-center">No approvers selected</td></tr>');
                    return;
                }

                let counter = 1;
                selectedApprovers.forEach(approver => {
                    const row = `
                <tr>
                    <td>${counter}</td>
                    <td>${approver}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm delete-approver" data-approver="${approver}">
                            Delete
                        </button>
                        <input type="hidden" name="approvers[]" value="${approver}">
                    </td>
                </tr>
            `;
                    tbody.append(row);
                    counter++;
                });
            }

            // Initialize approvers table
            updateApproversTable();

            // Show approver modal
            $('#addApproverBtn').click(function() {
                $('#approverModal').modal('show');
            });

            // Add approver when clicked in modal
            $(document).on('click', '.select-approver', function() {
                const approverDiv = $(this).closest('div[data-approver]');
                const approver = approverDiv.data('approver');

                if (!selectedApprovers.has(approver)) {
                    selectedApprovers.add(approver);
                    updateApproversTable();
                    $('#approverModal').modal('hide');
                } else {
                    alert('This approver is already selected.');
                }
            });

            // Remove approver when delete button is clicked
            $(document).on('click', '.delete-approver', function() {
                const approver = $(this).data('approver');
                selectedApprovers.delete(approver);
                updateApproversTable();
            });

            // Handle cancel button
            $('#cancelBtn').click(function() {
                if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
                    window.location.href = "{{ route('admin.approval-matrix.index') }}";
                }
            });

            // ===== FORM SUBMISSION VALIDATION =====
            // Form submission validation
            $('#approvalMatrixForm').submit(function(e) {
                // Check if minimum budget is provided
                if (!$('#min_budget').val()) {
                    e.preventDefault();
                    alert('Please enter a minimum budget');
                    return;
                }

                // Validate budget values
                const minBudget = parseInt($('#min_budget').val(), 10);
                const maxBudget = $('#max_budget').val() ? parseInt($('#max_budget').val(), 10) : null;

                if (minBudget < 0) {
                    e.preventDefault();
                    alert('Minimum budget cannot be negative');
                    return;
                }

                if (maxBudget !== null && maxBudget <= minBudget) {
                    e.preventDefault();
                    alert('Maximum budget must be greater than minimum budget');
                    return;
                }
            });
        });
    </script>
@endsection
