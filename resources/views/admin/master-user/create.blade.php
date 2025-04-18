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

        /* Styles for search results table */
        #searchResultsTable tbody tr {
            cursor: pointer;
        }

        #searchResultsTable tbody tr:hover {
            background-color: #f0f0f0;
        }

        #searchResultsTable tbody tr.table-primary {
            background-color: #b8daff;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .search-tabs .nav-link.active {
            background-color: #007bff;
            color: white;
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
                            <label>User Search</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Search by name or unit"
                                    id="searchNameInput" aria-label="Search term">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" id="searchUserBtn">Search</button>
                                </div>
                            </div>
                        </div>

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

                                <div class="mt-3">
                                    <table id="roleTable" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Role</th>
                                                <th>Min Budget (IDR)</th>
                                                <th>Max Budget (IDR)</th>
                                                <th>Approval Matrix</th>
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

    <!-- Role Selection Modal -->
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
                    <h5 class="modal-title">Set Budget Limits for <span id="roleName"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="minBudget_display">Minimum Budget (IDR)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="text" class="form-control" id="minBudget_display" placeholder="0">
                            <input type="hidden" id="minBudget" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="maxBudget_display">Maximum Budget (IDR)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="text" class="form-control" id="maxBudget_display"
                                placeholder="Leave empty for unlimited">
                            <input type="hidden" id="maxBudget" value="">
                        </div>
                        <small class="form-text text-muted">Values will be automatically
                            formatted with thousand separators as you type. Leave empty for
                            unlimited budget.</small>
                    </div>

                    <div class="form-group mt-4">
                        <label>Approval Matrix (Optional)</label>
                        <select class="form-control" id="approval_matrix_id" name="approval_matrix_id">
                            <option value="">-- Select Approval Matrix --</option>
                            @foreach (\App\Models\ApprovalMatrix::where('status', 'Active')->get() as $matrix)
                                <option value="{{ $matrix->id }}" data-min="{{ $matrix->min_budget }}"
                                    data-max="{{ $matrix->max_budget }}">
                                    {{ $matrix->name }} ({{ number_format($matrix->min_budget) }} -
                                    {{ $matrix->max_budget ? number_format($matrix->max_budget) : 'Unlimited' }})
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Selecting an approval matrix will automatically set the min/max
                            budget values.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveBudgetBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Search Modal -->
    <div id="searchModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Search User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body position-relative">
                    <!-- Name search section -->
                    <div class="form-group">
                        <label for="searchNameModal">Search by Name</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="searchNameModal"
                                placeholder="Enter name to search">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="searchModalBtn">Search</button>
                            </div>
                        </div>
                    </div>

                    <!-- Unit filter section (appears after search results) -->
                    <div id="unitFilterSection" class="form-group mt-3 d-none">
                        <label for="unitFilter">Filter by Unit Kerja</label>
                        <input type="text" class="form-control" id="unitFilter"
                            placeholder="Enter unit kerja to filter results">
                    </div>

                    <div id="searchResults" class="mt-4">
                        <table id="searchResultsTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>NIK</th>
                                    <th>Name</th>
                                    <th>Unit Kerja</th>
                                    <th>Jabatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Search results will be populated here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Loading overlay -->
                    <div id="loadingOverlay" class="loading-overlay d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ===== BUDGET FORMATTING FUNCTIONALITY =====
        // Default budget values
        const DEFAULT_MIN_BUDGET = 1;
        const DEFAULT_MAX_BUDGET = 500000000;

        // Function to format number with thousand separators
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Function to remove all non-numeric characters
        function unformatNumber(str) {
            return str.replace(/[^\d]/g, '');
        }

        // Function to format currency in table display
        function formatCurrency(amount) {
            if (amount === null || amount === '') return 'Unlimited';
            return new Intl.NumberFormat('id-ID').format(amount);
        }

        $(document).ready(function() {
            // Format display fields on input for minBudget
            $('#minBudget_display').on('input', function() {
                // Store cursor position
                const cursorPos = this.selectionStart;
                const oldLength = this.value.length;

                // Get raw value and update hidden field
                const rawValue = unformatNumber($(this).val());
                $('#minBudget').val(rawValue);

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

            // Same logic for maxBudget
            $('#maxBudget_display').on('input', function() {
                const cursorPos = this.selectionStart;
                const oldLength = this.value.length;

                const rawValue = unformatNumber($(this).val());
                $('#maxBudget').val(rawValue);

                if (rawValue) {
                    const formattedValue = formatNumber(rawValue);
                    $(this).val(formattedValue);

                    const newLength = formattedValue.length;
                    const newCursorPos = cursorPos + (newLength - oldLength);
                    this.setSelectionRange(newCursorPos, newCursorPos);
                }
            });

            // Connect approval matrix selection to budget values
            $('#approval_matrix_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                if (selectedOption.val()) {
                    const minBudget = selectedOption.data('min');
                    const maxBudget = selectedOption.data('max');

                    // Update both hidden and display fields
                    $('#minBudget').val(minBudget);
                    $('#minBudget_display').val(formatNumber(minBudget));

                    if (maxBudget) {
                        $('#maxBudget').val(maxBudget);
                        $('#maxBudget_display').val(formatNumber(maxBudget));
                    } else {
                        $('#maxBudget').val('');
                        $('#maxBudget_display').val('');
                    }
                }
            });
        });
    </script>
@endpush

@push('scripts')
<script>
    // ===== USER SEARCH FUNCTIONALITY =====
    $(document).ready(function() {
        // Store all search results for filtering
        let allSearchResults = [];

        // Open search modal when search button is clicked
        $('#searchUserBtn').click(function() {
            $('#searchModal').modal('show');
            $('#searchNameModal').val($('#searchNameInput').val());
        });

        // Transfer search text from input to modal when opened
        $('#searchModal').on('shown.bs.modal', function() {
            $('#searchNameModal').val($('#searchNameInput').val());
            // Auto-focus the search input
            $('#searchNameModal').focus();

            // Reset unit filter
            $('#unitFilter').val('');
            $('#unitFilterSection').addClass('d-none');

            // If there's already a search term, trigger search automatically
            if ($('#searchNameModal').val().trim() !== '') {
                $('#searchModalBtn').click();
            }
        });

        // Transfer search text from modal to input when closed
        $('#searchModal').on('hidden.bs.modal', function() {
            $('#searchNameInput').val($('#searchNameModal').val());
        });

        // Function to perform search by name
        function performSearch(searchTerm) {
            if (searchTerm === '') {
                alert('Please enter a name to search');
                return;
            }

            // Show loading overlay
            $('#loadingOverlay').removeClass('d-none');

            // Make AJAX request to search for users
            $.ajax({
                url: "{{ route('admin.search-employees') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    param: searchTerm
                },
                success: function(response) {
                    // Store the full results for filtering
                    allSearchResults = response.data || [];

                    // Display the results
                    displaySearchResults(allSearchResults);

                    // Show unit filter if we have results
                    if (allSearchResults.length > 0) {
                        $('#unitFilterSection').removeClass('d-none');
                    } else {
                        $('#unitFilterSection').addClass('d-none');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error searching for users: ' + error);
                    console.error('Search error:', xhr.responseText);
                },
                complete: function() {
                    // Hide loading overlay
                    $('#loadingOverlay').addClass('d-none');
                }
            });
        }

        // Function to display search results in the table
        function displaySearchResults(results) {
            const tbody = $('#searchResultsTable tbody');
            tbody.empty();

            if (results && results.length > 0) {
                // Populate search results table
                results.forEach(function(employee) {
                    const personal = employee.personal || {};
                    const detail = employee.detail || {};

                    // Extract values properly based on the provided JSON structure
                    let unit = '';
                    let position = '';
                    let objectId = '';

                    // For direct properties in detail
                    if (detail.unit) {
                        unit = detail.unit;
                        position = detail.nama_posisi || '';
                        objectId = detail.object_id || '';
                    }
                    // For properties in detail.payload
                    else if (detail.payload) {
                        unit = detail.payload.unit || '';
                        position = detail.payload.nama_posisi || '';
                        objectId = detail.payload.object_id || '';
                    }

                    const row = `
                    <tr data-nik="${personal.nik || ''}" data-name="${personal.name || ''}"
                        data-unit="${unit}" data-position="${position}"
                        data-objectid="${objectId}">
                        <td>${personal.nik || '-'}</td>
                        <td>${personal.name || '-'}</td>
                        <td>${unit || '-'}</td>
                        <td>${position || '-'}</td>
                    </tr>
                    `;
                    tbody.append(row);
                });
            } else {
                tbody.append('<tr><td colspan="4" class="text-center">No results found</td></tr>');
            }
        }

        // Handle unit filter input
        $('#unitFilter').on('input', function() {
            const filterText = $(this).val().toLowerCase().trim();

            if (filterText === '') {
                // If filter is empty, show all results
                displaySearchResults(allSearchResults);
                return;
            }

            // Filter the results by unit
            const filteredResults = allSearchResults.filter(function(employee) {
                const detail = employee.detail || {};
                let unit = '';

                // Extract unit from appropriate place in the detail object
                if (detail.unit) {
                    unit = detail.unit;
                } else if (detail.payload && detail.payload.unit) {
                    unit = detail.payload.unit;
                }

                return unit.toLowerCase().includes(filterText);
            });

            // Display filtered results
            displaySearchResults(filteredResults);
        });

        // Search button click handler
        $('#searchModalBtn').click(function() {
            const searchTerm = $('#searchNameModal').val().trim();
            performSearch(searchTerm);
        });

        // Handle row selection in search results
        $(document).on('click', '#searchResultsTable tbody tr', function() {
            // Skip if this is a "no results" row
            if ($(this).find('td[colspan]').length > 0) return;

            // Get user data from selected row
            const nik = $(this).data('nik');
            const name = $(this).data('name');
            const unit = $(this).data('unit');
            const position = $(this).data('position');
            const objectId = $(this).data('objectid');

            // Populate form fields directly from search results
            $('#nik').val(nik);
            $('#nama').val(name);
            $('#unit_kerja').val(unit || ''); // Handle potential undefined
            $('#jabatan').val(position || ''); // Handle potential undefined
            $('#object_id').val(objectId || '');

            // Close the modal
            $('#searchModal').modal('hide');

            // Highlight the row to show it's selected
            $('#searchResultsTable tbody tr').removeClass('table-primary');
            $(this).addClass('table-primary');

            // If any data is missing, automatically perform a detailed check
            if (nik && (!unit || !position || !objectId)) {
                // Simulate clicking the check button to get complete data
                $('#checkNikBtn').trigger('click');
            }
        });

        // Allow pressing Enter in search field to trigger search
        $('#searchNameModal').keypress(function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                $('#searchModalBtn').click();
            }
        });

        $('#searchNameInput').keypress(function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                $('#searchUserBtn').click();
            }
        });
    });
</script>
@endpush

@push('scripts')
    <script>
        // ===== ROLE MANAGEMENT FUNCTIONALITY =====
        $(document).ready(function() {
            let roleCount = 0;
            const selectedRoles = new Map(); // Changed from Set to Map to store additional data

            function updateRolesTable() {
                const tbody = $('#roleTable tbody');
                tbody.empty();

                if (selectedRoles.size === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center">No roles selected</td></tr>');
                    return;
                }

                let counter = 1;
                selectedRoles.forEach((roleInfo, role) => {
                    const matrixName = roleInfo.approvalMatrixId ?
                        $('#approval_matrix_id option[value="' + roleInfo.approvalMatrixId + '"]').text() :
                        'Not selected';

                    const row = `
            <tr>
                <td>${counter}</td>
                <td>${role}</td>
                <td>${formatCurrency(roleInfo.minBudget || 0)}</td>
                <td>${roleInfo.maxBudget ? formatCurrency(roleInfo.maxBudget) : 'Unlimited'}</td>
                <td>${matrixName}</td>
                <td>
                    <button type="button" class="btn btn-info btn-sm edit-budget mr-1" data-role="${role}">Edit Budget</button>
                    <button type="button" class="btn btn-danger btn-sm delete-role" data-role="${role}">Delete</button>
                    <input type="hidden" name="roles[${role}][role]" value="${role}">
                    <input type="hidden" name="roles[${role}][min_budget]" value="${roleInfo.minBudget || 0}">
                    <input type="hidden" name="roles[${role}][max_budget]" value="${roleInfo.maxBudget || ''}">
                    <input type="hidden" name="roles[${role}][approval_matrix_id]" value="${roleInfo.approvalMatrixId || ''}">
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
            $(document).on('click', '.select-role', function() {
                const roleDiv = $(this).closest('div[data-role]');
                const role = roleDiv.data('role');

                if (!selectedRoles.has(role)) {
                    // Show budget modal
                    $('#roleName').text(role);

                    // Set default budget values
                    $('#minBudget').val(DEFAULT_MIN_BUDGET.toString());
                    $('#minBudget_display').val(formatNumber(DEFAULT_MIN_BUDGET));
                    $('#maxBudget').val(DEFAULT_MAX_BUDGET.toString());
                    $('#maxBudget_display').val(formatNumber(DEFAULT_MAX_BUDGET));

                    // Reset approval matrix selection
                    $('#approval_matrix_id').val('');

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
                const approvalMatrixId = $('#approval_matrix_id').val() || null;

                if (parseInt(minBudget, 10) < 0) {
                    alert('Minimum budget cannot be negative.');
                    return;
                }

                if (maxBudget !== null && maxBudget !== '' && parseInt(maxBudget, 10) < parseInt(minBudget,
                        10)) {
                    alert('Maximum budget must be greater than or equal to minimum budget.');
                    return;
                }

                // Save role with budget limits and approval matrix info
                selectedRoles.set(role, {
                    minBudget: minBudget,
                    maxBudget: maxBudget,
                    approvalMatrixId: approvalMatrixId
                });

                updateRolesTable();
                $('#budgetModal').modal('hide');
            });

            // Edit budget when edit button is clicked
            $(document).on('click', '.edit-budget', function() {
                const role = $(this).data('role');
                const roleInfo = selectedRoles.get(role);

                $('#roleName').text(role);

                // Set both hidden and display fields
                $('#minBudget').val(roleInfo.minBudget || 0);
                $('#minBudget_display').val(formatNumber(roleInfo.minBudget || 0));

                $('#maxBudget').val(roleInfo.maxBudget || '');
                if (roleInfo.maxBudget) {
                    $('#maxBudget_display').val(formatNumber(roleInfo.maxBudget));
                } else {
                    $('#maxBudget_display').val('');
                }

                // Set approval matrix selection
                $('#approval_matrix_id').val(roleInfo.approvalMatrixId || '');

                $('#saveBudgetBtn').data('role', role);
                $('#budgetModal').modal('show');
            });

            // Remove role when delete button is clicked
            $(document).on('click', '.delete-role', function() {
                const role = $(this).data('role');
                selectedRoles.delete(role);
                updateRolesTable();
            });
        });
    </script>
@endpush

@push('scripts')
    <script>
        // ===== NIK VALIDATION AND FORM SUBMISSION =====
        $(document).ready(function() {
            // Handle checking NIK for validation and complete data
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

                // First check if user already exists in the database
                $.ajax({
                    url: "{{ route('admin.check-user-exists') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        nik: nik
                    },
                    success: function(response) {
                        if (response.exists) {
                            alert('This user is already in the database!');
                            $('#nama').val('');
                            $('#unit_kerja').val('');
                            $('#jabatan').val('');
                            $('#object_id').val('');

                            // Reset button
                            $('#checkNikBtn').html('Check');
                            $('#checkNikBtn').prop('disabled', false);
                        } else {
                            // If user doesn't exist, proceed to fetch details
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
                                        $('#unit_kerja').val(response.data
                                            .unit_kerja);
                                        $('#jabatan').val(response.data.jabatan);
                                        $('#object_id').val(response.data
                                        .object_id);
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
                        }
                    },
                    error: function() {
                        alert('Error checking if user exists');
                        $('#checkNikBtn').html('Check');
                        $('#checkNikBtn').prop('disabled', false);
                    }
                });
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
@endpush

@push('scripts')
    <script>
        // ===== MAIN DOCUMENT READY FUNCTION =====
        $(document).ready(function() {
            // Initialize Select2 for approval matrix dropdown
            $('#approval_matrix_id').select2({
                placeholder: "Select an approval matrix",
                allowClear: true,
                dropdownParent: $('#budgetModal')
            });

            // Declare selectedRoles in global scope for access across script files
            window.selectedRoles = window.selectedRoles || new Map();
        });
    </script>
@endpush
