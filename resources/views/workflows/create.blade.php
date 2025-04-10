@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Justification Form</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Justification Form</li>
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
        /* Style for the drop zone */
        .drop-zone {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            color: #666;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        /* Highlight the drop zone when dragging over it */
        .drop-zone.dragover {
            border-color: #007bff;
            background-color: #f0f8ff;
        }

        /* Button styling */
        #fileActions button {
            margin-right: 5px;
        }

        /* Style for the Notes textarea */
        textarea[name*="[notes]"] {
            width: 100%;
            height: 80px;
            resize: vertical;
        }

        /* Document list styling */
        .document-list {
            margin-top: 10px;
        }

        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            border: 1px solid #eee;
            margin-bottom: 5px;
            border-radius: 4px;
        }

        .document-item:hover {
            background-color: #f8f9fa;
        }
    </style>
@endpush

@section('page-wrapper')
    @include('main.components.message')

    <div class="card border-primary">
        <div class="card-body">
            <h3 class="card-title">Justification Form</h3>
            <hr>

            <form action="{{ route('workflows.store') }}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <!-- Bagian 1 -->
                    <div class="col-md-6 col-12">
                        <div class="form-group d-none">
                            <label>Nomor Pengajuan</label>
                            <input type="text" class="form-control" name="nomor_pengajuan" placeholder="Nomor Pengajuan"
                                value="{{ old('nomor_pengajuan') }}">
                        </div>

                        <div class="form-group">
                            <label>Tanggal Pembuatan</label>
                            <input type="text" class="form-control" name="creation_date"
                                value="{{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}" readonly>
                        </div>

                        @php
                            $nik = getAuthNik() ?? null;
                            $employeeDetails = getDetailNaker($nik);
                            $costCenter = $employeeDetails['cost_center_name']['nama_cost_center'] ?? '';
                            $unitKerja = $employeeDetails['unit'] ?? '';
                        @endphp

                        <div class="form-group">
                            <label for="unit_kerja">Unit Kerja</label>
                            <input type="text" class="form-control" id="unit" value="{{ $unitKerja }}" readonly>
                            <input type="hidden" name="unit_kerja" value="{{ $unitKerja }}">
                        </div>

                        <div class="form-group">
                            <label for="cost_center">Cost Center</label>
                            <input type="text" class="form-control" id="cost_center" value="{{ $costCenter }}"
                                readonly>
                            <input type="hidden" name="cost_center" value="{{ $costCenter }}">
                        </div>

                        <div class="form-group">
                            <label>Jenis Anggaran</label>
                            <select class="form-control" required name="jenis_anggaran">
                                <option value="">-- Pilih Jenis Anggaran --</option>
                                @foreach ($jenisAnggaran as $anggaran)
                                    <option value="{{ $anggaran->id }}"
                                        {{ old('jenis_anggaran') == $anggaran->id ? 'selected' : '' }}>{{ $anggaran->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="form-group">
                            <label>Nama Kegiatan</label>
                            <input type="text" class="form-control" required name="nama_kegiatan"
                                placeholder="Nama Kegiatan" value="{{ old('nama_kegiatan') }}">
                        </div>

                        <div class="form-group">
                            <label>Deskripsi Kegiatan</label>
                            <textarea class="form-control" name="deskripsi_kegiatan" rows="4" placeholder="Masukkan deskripsi kegiatan">{{ old('deskripsi_kegiatan') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Total Nilai</label>
                            <input type="text" class="form-control" required id="total_nilai_display"
                                placeholder="Total Nilai" value="{{ old('total_nilai_display') }}">
                            <input type="hidden" name="total_nilai" id="total_nilai" value="{{ old('total_nilai') }}">

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const displayField = document.getElementById('total_nilai_display');
                                    const valueField = document.getElementById('total_nilai');

                                    // Format initial value if exists
                                    if (valueField.value) {
                                        const numberValue = parseInt(valueField.value, 10);
                                        displayField.value = 'Rp ' + numberValue.toLocaleString('id-ID');
                                    }

                                    displayField.addEventListener('input', function(e) {
                                        // Remove non-digit characters and the "Rp" prefix
                                        let rawValue = this.value.replace(/[^0-9]/g, '');

                                        if (rawValue === '') {
                                            valueField.value = '';
                                            this.value = '';
                                            return;
                                        }

                                        const numberValue = parseInt(rawValue, 10);

                                        // Format with thousand separators and add "Rp"
                                        const formattedValue = 'Rp ' + numberValue.toLocaleString('id-ID', {
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        });

                                        this.value = formattedValue; // Display the formatted value with "Rp"
                                        valueField.value = numberValue; // Store the raw number for submission
                                    });
                                });
                            </script>
                        </div>

                        <div class="form-group">
                            <label>Waktu Penggunaan</label>
                            <input type="date" class="form-control" required name="waktu_penggunaan"
                                value="{{ old('waktu_penggunaan') }}">
                        </div>

                        <div class="form-group">
                            <label for="account">Account (Chart of Accounts)</label>
                            <select class="form-control select2" id="account" name="account" required>
                                <option value="">-- Select Account --</option>
                                <optgroup label="Assets">
                                    <option value="1001" {{ old('account') == '1001' ? 'selected' : '' }}>1001 - Cash &
                                        Bank</option>
                                    <option value="1002" {{ old('account') == '1002' ? 'selected' : '' }}>1002 - Accounts
                                        Receivable</option>
                                </optgroup>
                                <optgroup label="Liabilities">
                                    <option value="2001" {{ old('account') == '2001' ? 'selected' : '' }}>2001 - Accounts
                                        Payable</option>
                                    <option value="2002" {{ old('account') == '2002' ? 'selected' : '' }}>2002 - Bank
                                        Loans</option>
                                </optgroup>
                                <optgroup label="Revenue">
                                    <option value="3001" {{ old('account') == '3001' ? 'selected' : '' }}>3001 -
                                        Broadband Services Revenue</option>
                                    <option value="3002" {{ old('account') == '3002' ? 'selected' : '' }}>3002 -
                                        Enterprise Solutions Revenue</option>
                                </optgroup>
                                <optgroup label="Expenses">
                                    <option value="5001" {{ old('account') == '5001' ? 'selected' : '' }}>5001 - Network
                                        Maintenance</option>
                                    <option value="5002" {{ old('account') == '5002' ? 'selected' : '' }}>5002 -
                                        Marketing & Sales</option>
                                </optgroup>
                            </select>
                        </div>

                    </div>

                    <!-- Bagian 2 -->
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>Documents (PDF, DOC, XLS, Images)</label>

                            <!-- Multiple file upload -->
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="documents" name="documents[]"
                                    multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                <label class="custom-file-label" for="documents">Choose files</label>
                            </div>

                            <!-- Document list container -->
                            <div id="documentList" class="document-list mt-3"></div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const fileInput = document.getElementById('documents');
                                    const fileLabel = document.querySelector('.custom-file-label');
                                    const documentList = document.getElementById('documentList');

                                    fileInput.addEventListener('change', function(e) {
                                        // Update the file label
                                        if (this.files.length > 0) {
                                            fileLabel.textContent = `${this.files.length} files selected`;
                                        } else {
                                            fileLabel.textContent = 'Choose files';
                                        }

                                        // Clear previous list
                                        documentList.innerHTML = '';

                                        // Add each file to the list with category and type selectors
                                        Array.from(this.files).forEach((file, index) => {
                                            const fileSize = (file.size / 1024).toFixed(2) + ' KB';
                                            const fileItem = document.createElement('div');
                                            fileItem.className = 'document-item card p-3 mb-2';
                                            fileItem.innerHTML = `
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div>
                                                        <i class="fas fa-file mr-2"></i>
                                                        <span>${file.name}</span>
                                                        <small class="text-muted ml-2">(${fileSize})</small>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label for="document_categories_${index}">Category:</label>
                                                        <select class="form-control" name="document_categories[${index}]" id="document_categories_${index}" required>
                                                            <option value="MAIN">Main Document</option>
                                                            <option value="SUPPORTING" selected>Supporting Document</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="document_types_${index}">Type:</label>
                                                        <select class="form-control" name="document_types[${index}]" id="document_types_${index}" required>
                                                            <option value="JUSTIFICATION_DOC">Justification Document</option>
                                                            <option value="REVIEW_DOC">Review Document</option>
                                                            <option value="OTHER" selected>Other</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            `;
                                            documentList.appendChild(fileItem);
                                        });
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>

                <hr>

                <h5>Approval PICs</h5>
                <button type="button" class="btn btn-success btn-sm" id="add-pic-btn">+ Add PIC</button>

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Jabatan</th>
                            <th>Digital Signature</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="pic-table">
                        <!-- Pre-filled row for the logged-in user -->
                        <tr>
                            <td>
                                {{ $user->name }} <!-- Display the logged-in user's name -->
                                <input type="hidden" name="pics[0][user_id]" value="{{ $user->id }}">
                            </td>
                            <td>Created By <input type="hidden" name="pics[0][role]" value="CREATOR"></td>
                            <td>
                                {{ $user->jabatan ?? 'N/A' }} <!-- Display the logged-in user's jabatan -->
                                <input type="hidden" name="pics[0][jabatan]" value="{{ $user->jabatan ?? '' }}">
                            </td>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="pics[0][digital_signature]"
                                        value="1" {{ old('pics.0.digital_signature') ? 'checked' : '' }}>
                                    <label class="form-check-label">Use Digital Signature</label>
                                </div>
                            </td>
                            <td>
                                <textarea name="pics[0][notes]" placeholder="Enter notes (optional)">{{ old('pics.0.notes') }}</textarea>
                            </td>
                            <td></td> <!-- No remove button for the first PIC -->
                        </tr>
                        <!-- Dynamically Added PIC Rows -->
                        @foreach (old('pics', []) as $index => $pic)
                            @if ($index > 0)
                                <tr class="pic-entry">
                                    <td>
                                        <input type="text" class="form-control"
                                            name="pics[{{ $index }}][user_id]"
                                            value="{{ $pic['user_id'] ?? '' }}" placeholder="User ID">
                                    </td>
                                    <td>
                                        <select name="pics[{{ $index }}][role]" class="form-control">
                                            <option value="">-- Select Role --</option>
                                            @foreach (\App\Models\Workflow::getStatuses() as $status)
                                                <option value="{{ $status['code'] }}"
                                                    {{ isset($pic['role']) && $pic['role'] == $status['code'] ? 'selected' : '' }}>
                                                    {{ $status['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <span class="jabatan-display">{{ $pic['jabatan'] ?? 'N/A' }}</span>
                                        <input type="hidden" name="pics[{{ $index }}][jabatan]"
                                            value="{{ $pic['jabatan'] ?? '' }}">
                                    </td>
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input"
                                                name="pics[{{ $index }}][digital_signature]" value="1"
                                                {{ isset($pic['digital_signature']) && $pic['digital_signature'] ? 'checked' : '' }}>
                                            <label class="form-check-label">Use Digital Signature</label>
                                        </div>
                                    </td>
                                    <td>
                                        <textarea name="pics[{{ $index }}][notes]" placeholder="Enter notes (optional)">{{ $pic['notes'] ?? '' }}</textarea>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-pic">Remove</button>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Submit Workflow</button>
                    <button type="button" id="save-draft-btn" class="btn btn-secondary ml-2">Save as Draft</button>
                </div>
            </form>
        </div>
    </div>

    <!-- PIC Modal -->
    <div class="modal fade" id="pic-modal" tabindex="-1" aria-labelledby="picModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="picModalLabel">Add PIC</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Unit Kerja</label>
                        <select id="unit-kerja-select" class="form-control" style="width: 100%;"></select>
                    </div>

                    <div class="form-group mt-3">
                        <label>Select Employee</label>
                        <select id="employee-select" class="form-control" style="width: 100%;" disabled></select>
                    </div>

                    <div class="form-group mt-3">
                        <label>Role</label>
                        <select id="role-select" class="form-control" disabled></select>
                    </div>

                    <div class="form-group mt-3">
                        <label>Jabatan</label>
                        <p id="jabatan-display" class="form-control-static">N/A</p>
                        <input type="hidden" id="jabatan-input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="save-pic" class="btn btn-primary">Save PIC</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('app-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            const unitKerjaSelect = $("#unit-kerja-select");
            const employeeSelect = $("#employee-select");
            const roleSelect = $("#role-select");
            const picTable = $("#pic-table");
            const modal = new bootstrap.Modal(document.getElementById("pic-modal"));
            let picIndex = {{ count(old('pics', [1])) }}; // Start with the next index after existing PICs

            // Initialize the unit kerja select with Select2
            unitKerjaSelect.select2({
                ajax: {
                    url: "/workflow-actions/get-unit-kerja",
                    dataType: "json",
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term || ""
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(unit => ({
                                id: unit.unit_kerja,
                                text: `${unit.unit_kerja} (${unit.employee_count} employees)`
                            }))
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0, // Show all on empty search
                placeholder: "Select a unit kerja",
                allowClear: true,
                dropdownParent: $('#pic-modal')
            });

            // Initialize the employee select with Select2
            employeeSelect.select2({
                placeholder: "Select an employee",
                allowClear: true,
                dropdownParent: $('#pic-modal')
            });

            // When unit kerja is selected, load employees
            unitKerjaSelect.on("select2:select", function(e) {
                const unitKerja = e.params.data.id;

                // Clear and enable employee select
                employeeSelect.empty().prop("disabled", false);

                // Load employees for this unit
                $.get("/workflow-actions/get-employees", {
                    unit_kerja: unitKerja
                }, function(data) {
                    if (data.length > 0) {
                        // Add empty option first
                        employeeSelect.append(new Option('-- Select Employee --', '', true, true));

                        // Add employees to select
                        data.forEach(employee => {
                            employeeSelect.append(new Option(employee.name, employee.id,
                                false, false));
                        });
                    } else {
                        employeeSelect.append(new Option('No employees found', '', true, true));
                        employeeSelect.prop("disabled", true);
                    }
                }).fail(function(xhr, status, error) {
                    console.error("Error fetching employees:", error);
                    employeeSelect.append(new Option('Error loading employees', '', true, true));
                });
            });

            // When employee is selected, load their jabatan and roles
            employeeSelect.on("select2:select", function(e) {
                const userId = e.params.data.id;

                // Get employee's jabatan
                $.get("/workflow-actions/fetch-jabatan", {
                    user_id: userId
                }, function(response) {
                    if (response.success) {
                        $("#jabatan-display").text(response.nama_posisi);
                        $("#jabatan-input").val(response.nama_posisi);
                    } else {
                        $("#jabatan-display").text("Position not found");
                        $("#jabatan-input").val("");
                    }
                }).fail(function(xhr, status, error) {
                    console.error("Error fetching jabatan:", error);
                    $("#jabatan-display").text("Error loading position");
                });

                // Get employee's roles
                $.get("/workflow-actions/get-user-roles", {
                    user_id: userId
                }, function(roles) {
                    // Clear and enable role select
                    roleSelect.empty().prop("disabled", false);

                    if (roles && roles.length > 0) {
                        roles.forEach(function(role) {
                            const displayName = role.role_name || role.role;
                            roleSelect.append(new Option(displayName, role.role, false,
                                false));
                        });
                    } else {
                        // If no roles found, use the default workflow roles
                        @foreach (\App\Models\Workflow::getStatuses() as $status)
                            @if ($status['code'] != 'CREATOR')
                                roleSelect.append(new Option("{{ $status['name'] }}",
                                    "{{ $status['code'] }}", false, false));
                            @endif
                        @endforeach
                    }

                    // Set first option as selected
                    if (roleSelect.find("option").length > 0) {
                        roleSelect.val(roleSelect.find("option:first").val());
                    }
                }).fail(function(xhr, status, error) {
                    console.error("Error fetching roles:", error);
                    roleSelect.append(new Option('Error loading roles', '', true, true));
                });
            });

            $("#add-pic-btn").click(function() {
                // Reset all form fields
                unitKerjaSelect.val(null).trigger('change');
                employeeSelect.empty().prop("disabled", true);
                roleSelect.empty().prop("disabled", true);
                $("#jabatan-display").text("N/A");
                $("#jabatan-input").val("");

                // Remove any editing data
                $("#save-pic").removeData("editingRow");

                // Show the modal
                modal.show();
            });

            $("#save-pic").click(function() {
                const userId = employeeSelect.val();
                const userName = employeeSelect.find("option:selected").text();
                const roleCode = roleSelect.val();
                const roleName = roleSelect.find("option:selected").text();
                const jabatan = $("#jabatan-input").val() || 'N/A';

                if (!userId || !roleCode) {
                    alert("Please select unit kerja, employee, and role");
                    return;
                }

                let editingRow = $("#save-pic").data("editingRow");

                if (editingRow) {
                    // Update existing row
                    editingRow.find("td:eq(0)").html(
                        `${userName} <input type="hidden" name="pics[${editingRow.data('index')}][user_id]" value="${userId}">`
                    );
                    editingRow.find("td:eq(1)").html(
                        `${roleName} <input type="hidden" name="pics[${editingRow.data('index')}][role]" value="${roleCode}">`
                    );
                    editingRow.find("td:eq(2)").html(
                        `<span>${jabatan}</span><input type="hidden" name="pics[${editingRow.data('index')}][jabatan]" value="${jabatan}">`
                    );

                    // Enable/disable Notes field based on whether it's the current user
                    const notesField = editingRow.find("td:eq(4) textarea");
                    const isCurrentUser = (userId == "{{ $user->id }}");
                    notesField.prop("disabled", !isCurrentUser);
                } else {
                    // Determine if this is the current user
                    const isCurrentUser = (userId == "{{ $user->id }}");

                    // Add new row
                    const newRow = $(`
                        <tr data-index="${picIndex}" data-user-id="${userId}" data-role-code="${roleCode}">
                            <td>${userName} <input type="hidden" name="pics[${picIndex}][user_id]" value="${userId}"></td>
                            <td>${roleName} <input type="hidden" name="pics[${picIndex}][role]" value="${roleCode}"></td>
                            <td>
                                <span>${jabatan}</span>
                                <input type="hidden" name="pics[${picIndex}][jabatan]" value="${jabatan}">
                            </td>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="pics[${picIndex}][digital_signature]" value="1">
                                    <label class="form-check-label">Use Digital Signature</label>
                                </div>
                            </td>
                            <td>
                                <textarea name="pics[${picIndex}][notes]" placeholder="Enter notes (optional)"
                                    ${!isCurrentUser ? 'disabled' : ''}></textarea>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-pic">Remove</button>
                            </td>
                        </tr>
                    `);

                    picTable.append(newRow);
                    picIndex++;
                }

                modal.hide();
            });

            // Event delegation for remove buttons
            $(document).on("click", ".remove-pic", function() {
                $(this).closest("tr").remove();
            });

            // Handle "Save as Draft" button
            $("#save-draft-btn").click(function() {
                // Add a hidden field to indicate this is a draft
                $('<input>').attr({
                    type: 'hidden',
                    name: 'is_draft',
                    value: '1'
                }).appendTo('form');

                // Submit the form
                $('form').submit();
            });

            // Initialize Select2 for the account select
            $('#account').select2();
        });
    </script>
@endsection
