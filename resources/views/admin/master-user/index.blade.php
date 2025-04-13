@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Master User</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Master User</li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Manage</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@endsection

@section('page-wrapper')

    @include('main.components.message')

    <div class="card border-primary">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2">
                    <label for="filter-role">Roles:</label>
                    <select id="filter-role" class="form-control form-control-sm" multiple>
                        <!-- Roles will be populated dynamically via JavaScript -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter-status">Status</label>
                    <select id="filter-status" class="form-control form-control-sm">
                        <option value="All" selected>All</option>
                        <option value="Active">Active</option>
                        <option value="Not Active">Not Active</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter-unit">Unit Kerja:</label>
                    <select id="filter-unit" class="form-control form-control-sm">
                        <option value="">All</option>
                        <!-- Units will be populated dynamically via JavaScript -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter-nama">Nama:</label>
                    <input type="text" id="filter-nama" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label for="filter-nik">NIK:</label>
                    <input type="text" id="filter-nik" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 align-self-end text-right">
                    <a href="{{ route('admin.master-user.create') }}" class="btn btn-primary btn-sm">Add User</a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="table_data" class="table table-sm table-hover table-bordered display compact" style="width:100%">
                    <!-- Add this to the thead section in the index view -->
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Role</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Unit Kerja</th>
                            <th>Jabatan</th>
                            <th>Created by</th>
                            <th>Created Date</th>
                            <th>Edited by</th>
                            <th>Edited Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($masterUsers as $index => $user)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        <div class="role-item mb-2">
                                            <span class="badge badge-info">{{ $role->role }}</span>
                                            @if($role->min_budget !== null || $role->max_budget !== null)
                                                <div class="budget-range small text-muted mt-1">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                     {{ number_format($role->min_budget ?? 0, 0, ',', '.') }}
                                                    <i class="fas fa-arrow-right mx-1"></i>
                                                    @if($role->max_budget !== null)
                                                         {{ number_format($role->max_budget, 0, ',', '.') }}
                                                    @else
                                                        <span class="text-success">Unlimited</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                                <td>{{ $user->nik }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->unit_kerja }}</td>
                                <td>{{ $user->jabatan }}</td>
                                <td>
                                    @if ($user->creator_name)
                                        {{ $user->creator_name }}
                                    @elseif ($user->created_by)
                                        {{ $user->created_by }} <span class="text-warning">(Deleted User)</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d-M-Y') }}</td>
                                <td>
                                    @if ($user->editor_name)
                                        {{ $user->editor_name }}
                                    @elseif ($user->edited_by)
                                        {{ $user->edited_by }} <span class="text-warning">(Deleted User)</span>
                                    @else
                                        {{ $user->creator_name ?? ($user->created_by ?? '-') }}
                                    @endif
                                </td>
                                <td>
                                    @if ($user->updated_at != $user->created_at)
                                        {{ $user->updated_at->format('d-M-Y') }}
                                    @else
                                        {{ $user->created_at->format('d-M-Y') }}
                                    @endif
                                </td>
                                <td>
                                    @if ($user->status == 'Active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Not Active</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.master-user.edit', $user->id) }}"
                                        class="btn btn-warning btn-sm">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">No master users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <div class="note">
                    <strong>Note:</strong><br>
                    <span class="bg-warning">Master user consist of the Admin and Creator</span><br>
                    <span class="bg-warning">Grouped by Status Active/Not Active. Default: Active</span>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('app-script')
    <script type="text/javascript"
        src="https://cdn.datatables.net/v/bs4-4.1.1/jszip-2.5.0/dt-1.10.23/b-1.6.5/b-colvis-1.6.5/b-flash-1.6.5/b-html5-1.6.5/b-print-1.6.5/cr-1.5.3/r-2.2.7/sb-1.0.1/sp-1.2.2/datatables.min.js">
    </script>

    <!-- Add Select2 for better multiple select -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script type="text/javascript">
        $(function() {
            // Collect available roles from the table
            var availableRoles = [];
            $('#table_data tbody tr td:nth-child(2) .badge').each(function() {
                var role = $(this).text().trim();
                if ($.inArray(role, availableRoles) === -1) {
                    availableRoles.push(role);
                }
            });

            // Sort roles alphabetically
            availableRoles.sort();

            // Populate the roles dropdown
            var roleSelect = $('#filter-role');
            $.each(availableRoles, function(i, role) {
                roleSelect.append($('<option>', {
                    value: role,
                    text: role
                }));
            });

            // Collect available unit_kerja from the table
            var availableUnits = [];
            $('#table_data tbody tr td:nth-child(5)').each(function() {
                var unit = $(this).text().trim();
                if (unit && $.inArray(unit, availableUnits) === -1) {
                    availableUnits.push(unit);
                }
            });

            // Sort units alphabetically
            availableUnits.sort();

            // Populate the unit_kerja dropdown
            var unitSelect = $('#filter-unit');
            $.each(availableUnits, function(i, unit) {
                unitSelect.append($('<option>', {
                    value: unit,
                    text: unit
                }));
            });

            // Initialize Select2 for multiple role selection
            roleSelect.select2({
                placeholder: "Select roles",
                allowClear: true
            });

            // Initialize Select2 for unit_kerja
            unitSelect.select2({
                placeholder: "Select unit",
                allowClear: true
            });

            // Create a custom filtering function
            $.fn.dataTable.ext.search.push(
                function(settings, data) {
                    // Get filter values
                    var roles = $('#filter-role').val() || [];
                    var status = $('#filter-status').val();
                    var nama = $('#filter-nama').val().toLowerCase();
                    var nik = $('#filter-nik').val().toLowerCase();
                    var unit = $('#filter-unit').val();

                    // Check roles
                    var roleMatch = roles.length === 0;
                    if (!roleMatch) {
                        var rowRoles = data[1].split(' ').map(r => r.trim());
                        roleMatch = roles.some(r => rowRoles.includes(r));
                    }

                    // Check status
                    var statusMatch = status === 'All' || data[10].trim() === status;

                    // Check name
                    var nameMatch = nama === '' || data[3].toLowerCase().includes(nama);

                    // Check NIK
                    var nikMatch = nik === '' || data[2].toLowerCase().includes(nik);

                    // Check unit_kerja
                    var unitMatch = unit === '' || data[4].trim() === unit;

                    // Return true only if all filters pass
                    return roleMatch && statusMatch && nameMatch && nikMatch && unitMatch;
                }
            );

            // Initialize DataTable
            var table = $('#table_data').DataTable({
                processing: true,
                serverSide: false,
                dom: 'rt<"row"<"col-md-6"i><"col-md-6 text-right"B>>p',
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                buttons: [{
                        extend: 'copyHtml5',
                        className: 'btn btn-sm btn-secondary mt-2'
                    },
                    {
                        extend: 'excelHtml5',
                        className: 'btn btn-sm btn-secondary mt-2'
                    },
                    {
                        extend: 'csvHtml5',
                        className: 'btn btn-sm btn-secondary mt-2'
                    }
                ],
                "pageLength": 10
            });

            // Apply filters on change
            $('#filter-status, #filter-nama, #filter-nik, #filter-role, #filter-unit').on('change keyup', function() {
                table.draw();
            });
        });
    </script>

    <style>
        /* Make table more compact */
        .table.compact td,
        .table.compact th {
            padding: 0.3rem;
            font-size: 0.9rem;
        }

        /* Style for the badge spacing */
        .badge {
            margin-right: 2px;
        }

        /* Highlight for notes */
        .note span.bg-warning {
            display: inline-block;
            padding: 2px 5px;
            margin-top: 5px;
        }

        /* Style for Select2 dropdown */
        .select2-container {
            width: 100% !important;
        }

        /* Make the button look better */
        .btn-warning {
            color: #212529;
            font-size: 0.8rem;
            padding: 0.15rem 0.5rem;
        }

        /* Compact pagination and info */
        .dataTables_info,
        .dataTables_paginate {
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
    </style>
@endsection
