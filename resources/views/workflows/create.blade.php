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
    </style>
@endpush

@section('page-wrapper')
    @include('main.components.message')

    <div class="card border-primary">
        <div class="card-body">
            <h3 class="card-title">Justification Form</h3>
            <hr>

            <form action="{{ url('workflows/store') }}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <!-- Bagian 1 -->
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>Nomor Pengajuan</label>
                            <input type="text" class="form-control" required name="nomor_pengajuan"
                                placeholder="Nomor Pengajuan">
                        </div>

                        @php
                            $nik = getAuthNik() ?? null;
                            $employeeDetails = getDetailNaker($nik);
                            $costCenter = $employeeDetails['cost_center_name']['nama_cost_center'] ?? '';
                            $unitKerja = $employeeDetails['unit'] ?? '';
                        @endphp

                        <div class="form-group">
                            <label for="unit_kerja">Unit Kerja</label>
                            <input type="text" class="form-control" id="unit"
                                value="{{ $unitKerja }} - {{ $costCenter }}" readonly>
                            <input type="hidden" name="unit_kerja" value="{{ $unitKerja }}">
                            <input type="hidden" name="cost_center" value="{{ $costCenter }}">
                        </div>

                        <div class="form-group">
                            <label>Jenis Anggaran</label>
                            <select class="form-control" required name="jenis_anggaran">
                                <option value="">-- Pilih Jenis Anggaran --</option>
                                @foreach ($jenisAnggaran as $anggaran)
                                    <option value="{{ $anggaran->id }}">{{ $anggaran->nama }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="form-group">
                            <label>Nama Kegiatan</label>
                            <input type="text" class="form-control" required name="nama_kegiatan"
                                placeholder="Nama Kegiatan">
                        </div>

                        <div class="form-group">
                            <label>Total Nilai</label>
                            <input type="text" class="form-control" required id="total_nilai_display"
                                placeholder="Total Nilai">
                            <input type="hidden" name="total_nilai" id="total_nilai">

                            <script>
                                document.getElementById('total_nilai_display').addEventListener('input', function(e) {
                                    // Remove non-digit characters and the "Rp" prefix
                                    let rawValue = this.value.replace(/[^0-9]/g, '');

                                    if (rawValue === '') {
                                        document.getElementById('total_nilai').value = '';
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
                                    document.getElementById('total_nilai').value = numberValue; // Store the raw number for submission
                                });
                            </script>
                        </div>

                        <div class="form-group">
                            <label>Waktu Penggunaan</label>
                            <input type="date" class="form-control" required name="waktu_penggunaan">
                        </div>

                        <div class="form-group">
                            <label for="account">Account (Chart of Accounts)</label>
                            <select class="form-control select2" id="account" name="account" required>
                                <option value="">-- Select Account --</option>
                                <optgroup label="Assets">
                                    <option value="1001">1001 - Cash & Bank</option>
                                    <option value="1002">1002 - Accounts Receivable</option>
                                </optgroup>
                                <optgroup label="Liabilities">
                                    <option value="2001">2001 - Accounts Payable</option>
                                    <option value="2002">2002 - Bank Loans</option>
                                </optgroup>
                                <optgroup label="Revenue">
                                    <option value="3001">3001 - Broadband Services Revenue</option>
                                    <option value="3002">3002 - Enterprise Solutions Revenue</option>
                                </optgroup>
                                <optgroup label="Expenses">
                                    <option value="5001">5001 - Network Maintenance</option>
                                    <option value="5002">5002 - Marketing & Sales</option>
                                </optgroup>
                            </select>
                        </div>

                    </div>

                    <!-- Bagian 2 -->
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>Justification Document (PDF)</label>
                            <!-- Hidden file input -->
                            <input type="file" name="doc" accept=".pdf" id="fileInput" class="form-control"
                                style="display: none;">

                            <!-- Custom drop zone -->
                            <div id="dropZone" class="drop-zone">
                                Drag and drop a PDF file here or click to select
                            </div>

                            <!-- Buttons container (initially hidden) -->
                            <div id="fileActions" style="display: none; margin-top: 10px;">
                                <button id="viewButton" class="btn btn-primary">View Document</button>
                                <button id="deleteButton" class="btn btn-danger ml-2">Delete Document</button>
                            </div>
                        </div>

                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                const dropZone = document.getElementById("dropZone");
                                const fileInput = document.getElementById("fileInput");
                                const fileActions = document.getElementById("fileActions");
                                const viewButton = document.getElementById("viewButton");
                                const deleteButton = document.getElementById("deleteButton");

                                let selectedFile = null; // To store the selected file

                                // Prevent default drag behaviors
                                ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
                                    dropZone.addEventListener(eventName, preventDefaults, false);
                                });

                                function preventDefaults(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                }

                                // Highlight drop zone when item is dragged over it
                                ["dragenter", "dragover"].forEach((eventName) => {
                                    dropZone.addEventListener(eventName, () => {
                                        dropZone.classList.add("dragover");
                                    }, false);
                                });

                                ["dragleave", "drop"].forEach((eventName) => {
                                    dropZone.addEventListener(eventName, () => {
                                        dropZone.classList.remove("dragover");
                                    }, false);
                                });

                                // Handle dropped files
                                dropZone.addEventListener("drop", (e) => {
                                    const files = e.dataTransfer.files;
                                    if (files.length > 0) {
                                        handleFile(files[0]);
                                    }
                                });

                                // Open file dialog when drop zone is clicked
                                dropZone.addEventListener("click", () => {
                                    fileInput.click(); // Trigger the hidden file input
                                });

                                // Handle file selection via file input
                                fileInput.addEventListener("change", () => {
                                    if (fileInput.files.length > 0) {
                                        handleFile(fileInput.files[0]);
                                    }
                                });

                                // Function to handle file selection
                                function handleFile(file) {
                                    if (file.type === "application/pdf") {
                                        selectedFile = file; // Store the selected file
                                        dropZone.textContent = `File selected: ${file.name}`;
                                        fileActions.style.display = "block"; // Show the action buttons
                                    } else {
                                        alert("Only PDF files are allowed.");
                                    }
                                }

                                // View document button
                                viewButton.addEventListener("click", () => {
                                    if (selectedFile) {
                                        const fileURL = URL.createObjectURL(
                                            selectedFile); // Create a temporary URL for the file
                                        window.open(fileURL, "_blank"); // Open the file in a new tab
                                    } else {
                                        alert("No file selected to view.");
                                    }
                                });

                                // Delete document button
                                deleteButton.addEventListener("click", () => {
                                    selectedFile = null; // Clear the selected file
                                    fileInput.value = ""; // Clear the file input
                                    dropZone.textContent = "Drag and drop a PDF file here or click to select";
                                    fileActions.style.display = "none"; // Hide the action buttons
                                });
                            });
                        </script>
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
                            <th>Jabatan</th> <!-- New Column -->
                            <th>Digital Signature</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="pic-table">
                        <!-- Pre-filled row for the logged-in user -->
                        <tr>
                            <td>
                                {{ getAuthName() }} <!-- Display the logged-in user's name -->
                                <input type="hidden" name="pics[0][user_id]" value="{{ getAuthId() }}">
                            </td>
                            <td>Created By <input type="hidden" name="pics[0][role]" value="CREATOR"></td>
                            <td>
                                {{ $employeeDetails['nama_posisi'] ?? 'N/A' }} <!-- Display the logged-in user's jabatan -->
                                <input type="hidden" name="pics[0][jabatan]"
                                    value="{{ $employeeDetails['nama_posisi'] ?? '' }}">
                            </td>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="pics[0][digital_signature]"
                                        value="1">
                                    <label class="form-check-label">Use Digital Signature</label>
                                </div>
                            </td>
                            <td>
                                <textarea name="pics[0][notes]" placeholder="Enter notes (optional)"></textarea>
                            </td>
                            <td></td> <!-- No remove button for the first PIC -->
                        </tr>
                        <!-- Dynamically Added PIC Rows -->
                        @foreach (old('pics', []) as $index => $pic)
                            @if ($index > 0)
                                <tr class="pic-entry">
                                    <td>
                                        <input type="text" name="pics[{{ $index }}][user_id]"
                                            value="{{ $pic['user_id'] ?? '' }}" placeholder="User ID">
                                    </td>
                                    <td>
                                        <select name="pics[{{ $index }}][role]">
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
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input"
                                                name="pics[{{ $index }}][digital_signature]" value="1"
                                                {{ isset($pic['digital_signature']) && $pic['digital_signature'] ? 'checked' : '' }}>
                                            <label class="form-check-label">Use Digital Signature</label>
                                        </div>
                                    </td>
                                    <td>
                                        <textarea name="pics[{{ $index }}][notes]" placeholder="Enter notes (optional)" disabled></textarea>
                                    </td>
                                    <td>
                                        <button type="button" class="remove-pic-btn">Remove</button>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>

                <button type="submit" class="btn btn-block btn-primary mt-3">Submit Workflow</button>
                <button class="btn btn-block btn-secondary mt-3">Draft</button>
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
                    <label>Select User</label>
                    <select id="user-select" class="form-control" style="width: 100%;"></select>
                    <label class="mt-2">Role</label>
                    <select id="role-select" class="form-control">
                        @foreach (\App\Models\Workflow::getStatuses() as $status)
                            <option value="{{ $status['code'] }}">{{ $status['name'] }}</option>
                        @endforeach
                    </select>
                    <label class="mt-2">Jabatan</label>
                    <span id="jabatan-display">N/A</span> <!-- Display jabatan here -->
                    <input type="hidden" id="jabatan-input"> <!-- Hidden input for jabatan -->
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
            const userSelect = $("#user-select");

            // Initialize Select2 with AJAX
            userSelect.select2({
                ajax: {
                    url: "/meta/find-users",
                    dataType: "json",
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(user => ({
                                id: user.id,
                                text: user.name
                            }))
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2,
                placeholder: "Select a user",
                allowClear: true
            });

            userSelect.on("select2:select", function(e) {
                const nik = e.params.data.id;

                $.get("/meta/fetch-jabatan", {
                    nik: nik
                }, function(response) {
                    if (response.success) {
                        $("#jabatan-display").text(response.nama_posisi); // Display jabatan
                        $("#jabatan-input").val(response
                        .nama_posisi); // Store jabatan in hidden input
                    } else {
                        $("#jabatan-display").text("Position not found");
                        $("#jabatan-input").val(""); // Clear jabatan if not found
                    }
                });
            });

            const picTable = $("#pic-table");
            const picModal = new bootstrap.Modal(document.getElementById("pic-modal"));

            $("#add-pic-btn").click(function() {
                picModal.show();
            });

            let picIndex = 1; // Counter for indexing PICs

            $("#save-pic").click(function() {
                const userId = userSelect.val();
                const userName = userSelect.find("option:selected").text();
                const roleCode = $("#role-select").val();
                const roleName = $("#role-select option:selected").text();
                const jabatan = $("#jabatan-display").text(); // Get jabatan from the modal display

                let editingRow = $("#save-pic").data("editingRow");

                if (editingRow) {
                    // Update existing row
                    editingRow.find("td:eq(0)").html(
                        `${userName} <input type="hidden" name="pics[][user_id]" value="${userId}">`
                    );
                    editingRow.find("td:eq(1)").html(
                        `${roleName} <input type="hidden" name="pics[][role]" value="${roleCode}">`
                    );
                    editingRow.find("td:eq(2)").html(
                        `<span>${jabatan}</span><input type="hidden" name="pics[][jabatan]" value="${jabatan}">`
                    );
                    // Enable/disable Notes field
                    const notesField = editingRow.find("td:eq(4) textarea");
                    if (userId == "{{ getAuthId() }}") {
                        notesField.prop("disabled", false);
                    } else {
                        notesField.prop("disabled", true);
                    }
                    // Reset the editing row reference
                    $("#save-pic").removeData("editingRow");
                } else {
                    // Add new row
                    const newRow = `
                        <tr data-user-id="${userId}" data-role-code="${roleCode}">
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
                                    ${userId != "{{ getAuthId() }}" ? 'disabled' : ''}></textarea>
                            </td>
                            <td>
                                <button type="button" class="btn btn-warning d-none btn-sm edit-pic">Edit</button>
                                <button type="button" class="btn btn-danger btn-sm remove-pic">Remove</button>
                            </td>
                        </tr>
                    `;
                    picTable.append(newRow);
                    picIndex++;
                }
                picModal.hide();
            });

            $(document).on("click", ".edit-pic", function() {
                const row = $(this).closest("tr");
                const userId = row.data("user-id");
                const roleCode = row.data("role-code");
                const jabatan = row.find("td:eq(2) span").text(); // Get jabatan from the table cell

                // Set the modal fields with the current values
                userSelect.val(userId).trigger("change");
                $("#role-select").val(roleCode);
                $("#jabatan-display").text(jabatan); // Display jabatan in the modal
                $("#jabatan-input").val(jabatan); // Store jabatan in hidden input

                // Store the row being edited
                $("#save-pic").data("editingRow", row);

                // Show the modal
                picModal.show();
            });

            $(document).on("click", ".remove-pic", function() {
                $(this).closest("tr").remove();
            });
        });
    </script>
@endsection
