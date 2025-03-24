@extends('main.app')

@section('page-breadcrumb')
    <div class="row">
        <div class="col-7 align-self-center">
            <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Workflow</h4>
            <div class="d-flex align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item text-muted active" aria-current="page">Workflow</li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
@endpush

@section('page-wrapper')
    @include('main.components.message')

    <div class="card border-primary">
        <div class="card-body">
            <h3 class="card-title">New Workflow</h3>
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

                        <div class="form-group">
                            <label for="unit_kerja">Unit Kerja</label>
                            <select class="form-control" id="unit" name="unit_kerja" required>
                                <option value="">-- Select Operational Unit --</option>

                                <optgroup label="Regional Infrastructure & Service Delivery (RISD)">
                                    <option value="RISD Regional 1 (Sumatera)">RISD Regional 1 - Sumatera</option>
                                    <option value="RISD Regional 2 (Jabodetabek)">RISD Regional 2 - Jabodetabek</option>
                                    <option value="RISD Regional 3 (Jawa Barat)">RISD Regional 3 - Jawa Barat</option>
                                    <option value="RISD Regional 4 (Jawa Tengah & DIY)">RISD Regional 4 - Jawa Tengah & DIY
                                    </option>
                                    <option value="RISD Regional 5 (Jawa Timur, Bali, Nusa Tenggara)">RISD Regional 5 - Jawa
                                        Timur, Bali, Nusa Tenggara</option>
                                    <option value="RISD Regional 6 (Kalimantan)">RISD Regional 6 - Kalimantan</option>
                                    <option value="RISD Regional 7 (Sulawesi, Maluku, Papua)">RISD Regional 7 - Sulawesi,
                                        Maluku, Papua</option>
                                </optgroup>

                                <optgroup label="Fixed Broadband Access & Transport Division">
                                    <option value="Fiber To The Home (FTTH) Deployment">FTTH Deployment</option>
                                    <option value="Metro Ethernet & IP/MPLS Backbone">Metro Ethernet & IP/MPLS Backbone
                                    </option>
                                    <option value="Gigabit Passive Optical Network (GPON) Maintenance">GPON Maintenance
                                    </option>
                                    <option value="Data Center & Cloud Infrastructure">Data Center & Cloud Infrastructure
                                    </option>
                                    <option value="5G & Next-Gen Wireless Backhaul">5G & Next-Gen Wireless Backhaul</option>
                                    <option value="International Submarine Cable Operations">International Submarine Cable
                                        Operations</option>
                                </optgroup>

                                <optgroup label="Network Operations Center (NOC) & Cybersecurity">
                                    <option value="NOC Tier 1 (Real-time Monitoring)">NOC Tier 1 - Real-time Monitoring
                                    </option>
                                    <option value="NOC Tier 2 (Incident Response & Troubleshooting)">NOC Tier 2 - Incident
                                        Response & Troubleshooting</option>
                                    <option value="NOC Tier 3 (Root Cause Analysis & Escalation)">NOC Tier 3 - Root Cause
                                        Analysis & Escalation</option>
                                    <option value="Cyber Threat Intelligence & Risk Mitigation">Cyber Threat Intelligence &
                                        Risk Mitigation</option>
                                    <option value="Network Penetration Testing & Compliance">Network Penetration Testing &
                                        Compliance</option>
                                    <option value="Cloud & On-Prem Security Operations">Cloud & On-Prem Security Operations
                                    </option>
                                </optgroup>

                                <optgroup label="IT & Business Support Systems">
                                    <option value="OSS/BSS (Operational & Business Support Systems)">OSS/BSS - Operational &
                                        Business Support Systems</option>
                                    <option value="CRM & Digital Customer Experience">CRM & Digital Customer Experience
                                    </option>
                                    <option value="Automated Network Provisioning & Self-Healing">Automated Network
                                        Provisioning & Self-Healing</option>
                                    <option value="IoT & Smart Infrastructure Integration">IoT & Smart Infrastructure
                                        Integration</option>
                                    <option value="AI-Driven Predictive Maintenance">AI-Driven Predictive Maintenance
                                    </option>
                                    <option value="Cloud-Native DevOps & CI/CD Pipelines">Cloud-Native DevOps & CI/CD
                                        Pipelines</option>
                                </optgroup>

                                <optgroup label="Enterprise Solutions & Strategic Innovations">
                                    <option value="5G Private Networks & Enterprise Edge Computing">5G Private Networks &
                                        Enterprise Edge Computing</option>
                                    <option value="Multi-Cloud Orchestration & API Management">Multi-Cloud Orchestration &
                                        API Management</option>
                                    <option value="Blockchain for Secure Telco Transactions">Blockchain for Secure Telco
                                        Transactions</option>
                                    <option value="Quantum-Safe Network Architecture">Quantum-Safe Network Architecture
                                    </option>
                                    <option value="AI-Driven Smart City Infrastructure">AI-Driven Smart City Infrastructure
                                    </option>
                                    <option value="Satellite & High-Altitude Platform Stations (HAPS)">Satellite & HAPS
                                        (High-Altitude Platform Stations)</option>
                                </optgroup>
                            </select>
                        </div>


                        <div class="form-group">
                            <label>Nama Kegiatan</label>
                            <input type="text" class="form-control" required name="nama_kegiatan"
                                placeholder="Nama Kegiatan">
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
                    </div>

                    <!-- Bagian 2 -->
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>Total Nilai</label>
                            <input type="text" class="form-control" required id="total_nilai_display"
                                placeholder="Total Nilai">
                            <input type="hidden" name="total_nilai" id="total_nilai">
                        </div>

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


                        <div class="form-group">
                            <label>Justification Document (PDF)</label>
                            <input type="file" name="doc" accept=".pdf" class="form-control">
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
                            <th>Digital Signature</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="pic-table">
                        <!-- Dynamic Rows -->
                        <!-- Pre-filled row for the logged-in user -->
                        <tr>
                            <td>
                                {{ getAuthName() }} <!-- Display the logged-in user's name -->
                                <input type="hidden" name="pics[0][user_id]" value="{{ getAuthId() }}">
                            </td>
                            <td>Created By <input type="hidden" name="pics[0][role]" value="CREATOR"></td>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="pics[0][digital_signature]"
                                        value="1">
                                    <label class="form-check-label">Use Digital Signature</label>
                                </div>
                            </td>
                            <td></td> <!-- No remove button for the first PIC -->
                        </tr>

                        <!-- Dynamically Added PIC Rows -->
                        @foreach (old('pics', []) as $index => $pic)
                            @if ($index > 0)
                                <!-- Skip the predefined PIC at index 0 -->
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

            const picTable = $("#pic-table");
            const picModal = new bootstrap.Modal(document.getElementById("pic-modal"));

            $("#add-pic-btn").click(function() {
                picModal.show();
            });

            let picIndex = 1; // Counter for indexing PICs

            $("#save-pic").click(function() {
                const userId = userSelect.val();
                console.log("choosing " + userId);
                const userName = userSelect.find("option:selected").text();
                const roleCode = $("#role-select").val();
                const roleName = $("#role-select option:selected").text();
                let editingRow = $("#save-pic").data("editingRow");

                if (editingRow) {
                        // Update existing row
                        editingRow.find("td:eq(0)").html(
                            `${userName} <input type="hidden" name="pics[][user_id]" value="${userId}">`);
                        editingRow.find("td:eq(1)").html(
                            `${roleName} <input type="hidden" name="pics[][role]" value="${roleCode}">`);
                        // Reset the editing row reference
                        $("#save-pic").removeData("editingRow");
                } else {
                    // Add new row
                    const newRow = `
                        <tr data-user-id="${userId}" data-role-code="${roleCode}">
                            <td>${userName} <input type="hidden" name="pics[${picIndex}][user_id]" value="${userId}"></td>
                            <td>${roleName} <input type="hidden" name="pics[${picIndex}][role]" value="${roleCode}"></td>
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="pics[${picIndex}][digital_signature]" value="1">
                                    <label class="form-check-label">Use Digital Signature</label>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm edit-pic">Edit</button>
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

                // Set the modal fields with the current values
                userSelect.val(userId).trigger("change");
                $("#role-select").val(roleCode);

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
