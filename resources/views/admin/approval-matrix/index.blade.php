@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Approval Matrix</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Approval Matrix</li>
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
                    <label for="filter-status">Status</label>
                    <select id="filter-status" class="form-control form-control-sm">
                        <option value="All" selected>All</option>
                        <option value="Active">Active</option>
                        <option value="Not Active">Not Active</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter-name">Name:</label>
                    <input type="text" id="filter-name" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 align-self-end text-right">
                    <a href="{{ route('admin.approval-matrix.create') }}" class="btn btn-primary btn-sm">Add Matrix</a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="table_data" class="table table-sm table-hover table-bordered display compact" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Budget Range (IDR)</th>
                            <th class="d-none">Approvers</th>
                            <th>Description</th>
                            <th>Created by</th>
                            <th>Created Date</th>
                            <th>Edited by</th>
                            <th>Edited Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($matrices as $index => $matrix)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $matrix->name }}</td>
                                <td>
                                    {{ number_format($matrix->min_budget, 0, ',', '.') }}
                                    <i class="fas fa-arrow-right mx-1"></i>
                                    @if($matrix->max_budget !== null)
                                        {{ number_format($matrix->max_budget, 0, ',', '.') }}
                                    @else
                                        <span class="text-success">Unlimited</span>
                                    @endif
                                </td>
                                <td class="d-none">
                                    @foreach ($matrix->approvers as $approver)
                                        <span class="badge badge-info">{{ $approver }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $matrix->description ?? '-' }}</td>
                                <td>{{ $matrix->creator_name }}</td>
                                <td>{{ $matrix->created_at->format('d-M-Y') }}</td>
                                <td>{{ $matrix->editor_name ?? $matrix->creator_name }}</td>
                                <td>
                                    @if ($matrix->updated_at != $matrix->created_at)
                                        {{ $matrix->updated_at->format('d-M-Y') }}
                                    @else
                                        {{ $matrix->created_at->format('d-M-Y') }}
                                    @endif
                                </td>
                                <td>
                                    @if ($matrix->status == 'Active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Not Active</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.approval-matrix.edit', $matrix->id) }}"
                                        class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('admin.approval-matrix.destroy', $matrix->id) }}" method="POST"
                                          class="d-inline" onsubmit="return confirm('Are you sure you want to delete this matrix?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">No approval matrices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <div class="note">
                    <strong>Note:</strong><br>
                    <span class="bg-warning">Budget ranges should not overlap to prevent approval confusion.</span><br>
                    <span class="bg-warning">Matrices are shown by default in ascending order of budget amount.</span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('app-script')
    <script type="text/javascript"
        src="https://cdn.datatables.net/v/bs4-4.1.1/jszip-2.5.0/dt-1.10.23/b-1.6.5/b-colvis-1.6.5/b-flash-1.6.5/b-html5-1.6.5/b-print-1.6.5/cr-1.5.3/r-2.2.7/sb-1.0.1/sp-1.2.2/datatables.min.js">
    </script>

    <script type="text/javascript">
        $(function() {
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

            // Custom filtering function
            $.fn.dataTable.ext.search.push(
                function(settings, data) {
                    // Get filter values
                    var status = $('#filter-status').val();
                    var name = $('#filter-name').val().toLowerCase();

                    // Check status match
                    var statusMatch = status === 'All' || data[9].includes(status);

                    // Check name match
                    var nameMatch = name === '' || data[1].toLowerCase().includes(name);

                    // Return true only if all filters pass
                    return statusMatch && nameMatch;
                }
            );

            // Apply filters on change
            $('#filter-status, #filter-name').on('change keyup', function() {
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
            margin-bottom: 2px;
            display: inline-block;
        }

        /* Highlight for notes */
        .note span.bg-warning {
            display: inline-block;
            padding: 2px 5px;
            margin-top: 5px;
        }

        /* Make the button look better */
        .btn-sm {
            margin: 0 2px;
        }
    </style>
@endsection
