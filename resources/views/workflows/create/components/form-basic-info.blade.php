{{-- resources/views/workflows/create/components/form-basic-info.blade.php --}}

{{-- Check if we're in edit mode --}}
@php
    $isEdit = isset($workflow) && $workflow->exists;
@endphp

<div class="form-group {{ $isEdit ? '' : 'd-none' }}">
    <label>Nomor Pengajuan</label>
    <input type="text" class="form-control" name="nomor_pengajuan" placeholder="Nomor Pengajuan"
        value="{{ $isEdit ? $workflow->nomor_pengajuan : old('nomor_pengajuan') }}" readonly>
</div>

<div class="form-group">
    <label>Tanggal Pembuatan</label>
    <input type="text" class="form-control" name="creation_date"
        value="{{ $isEdit ? $workflow->creation_date : \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}" readonly>
</div>

@php
    $nik = getAuthNik() ?? null;
    $employeeDetails = getDetailNaker($nik);

    // Use workflow data for editing, or fallback to API data for creating
    $costCenter = $isEdit ? $workflow->cost_center : $employeeDetails['cost_center_name']['nama_cost_center'] ?? '';
    $costCenterId = $isEdit ? $workflow->cost_center_id : $employeeDetails['cost_center_id'] ?? '';
    $unitKerja = $isEdit ? $workflow->unit_kerja : $employeeDetails['unit'] ?? '';
    $unitCcId = $isEdit ? $workflow->cost_center_account : null;

    // Fetch cost center list from API
    $costCenterUrl = 'http://10.204.222.12/backend-fista/costcenter/getlist';
    $costCenterResponse = Http::get($costCenterUrl);
    $costCenterData = $costCenterResponse->json()['data'] ?? [];

    // Check if costCenterId exists in API data
    $costCenterExists = false;
    foreach ($costCenterData as $cc) {
        if ($cc['cc_id'] == $costCenterId) {
            $costCenterExists = true;
            if (!$unitCcId) {
                $unitCcId = $cc['unit_cc_id'];
            }
            break;
        }
    }

    // If cost center exists, fetch account list
    $accountList = [];
    if ($costCenterExists && $unitCcId) {
        $accountUrl = url("/api/coa/cost-center-account-list?unit_cc_id={$unitCcId}");
        $accountResponse = Http::get($accountUrl);
        $accountList = $accountResponse->json() ?? [];
    }
@endphp

<div class="form-group">
    <label for="unit_kerja">Unit Kerja</label>
    <input type="text" class="form-control" id="unit" value="{{ $unitKerja }}" readonly>
    <input type="hidden" name="unit_kerja" value="{{ $unitKerja }}">
</div>

<div class="form-group">
    <label for="cost_center">Cost Center</label>
    @if ($costCenterExists)
        <input type="text" class="form-control" id="cost_center" value="{{ $costCenter }}" readonly>
        <input type="hidden" name="cost_center" value="{{ $costCenter }}">
        <input type="hidden" name="cost_center_id" value="{{ $costCenterId }}">
        <input type="hidden" name="unit_cc_id" value="{{ $unitCcId }}">
    @else
        <select class="form-control select2" id="cost_center_select" name="cost_center">
            <option value="">-- Pilih Cost Center --</option>
            @foreach ($costCenterData as $cc)
                <option value="{{ $cc['cc_name'] }}" data-cc-id="{{ $cc['cc_id'] }}"
                    data-unit-cc-id="{{ $cc['unit_cc_id'] }}"
                    {{ $isEdit && $workflow->cost_center == $cc['cc_name'] ? 'selected' : '' }}>
                    {{ $cc['cc_name'] }} ({{ $cc['cc_id'] }})
                </option>
            @endforeach
        </select>
        <input type="hidden" name="cost_center_id" id="cost_center_id"
            value="{{ $isEdit ? $workflow->cost_center_id : '' }}">
        <input type="hidden" name="unit_cc_id" id="unit_cc_id"
            value="{{ $isEdit ? $workflow->cost_center_account : '' }}">
        <div class="alert alert-warning mt-2">
            <small>Perhatian: Cost Center ID {{ $isEdit ? 'tidak valid' : 'Anda tidak ditemukan' }}. Silakan pilih Cost
                Center secara manual.</small>
        </div>
    @endif
</div>

<div class="form-group">
    <label>Jenis Anggaran</label>
    <select class="form-control" required name="jenis_anggaran">
        <option value="">-- Pilih Jenis Anggaran --</option>
        @foreach ($jenisAnggaran as $anggaran)
            <option value="{{ $anggaran->id }}"
                {{ ($isEdit && $workflow->jenis_anggaran == $anggaran->id) || old('jenis_anggaran') == $anggaran->id ? 'selected' : '' }}>
                {{ $anggaran->nama }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>Nama Kegiatan</label>
    <input type="text" class="form-control" required name="nama_kegiatan" placeholder="Nama Kegiatan"
        value="{{ $isEdit ? $workflow->nama_kegiatan : old('nama_kegiatan') }}">
</div>

<div class="form-group">
    <label>Deskripsi Kegiatan</label>
    <textarea class="form-control" name="deskripsi_kegiatan" rows="4" placeholder="Masukkan deskripsi kegiatan">{{ $isEdit ? $workflow->deskripsi_kegiatan : old('deskripsi_kegiatan') }}</textarea>
</div>

<div class="form-group">
    <label>Total Nilai</label>
    <input type="text" class="form-control" required id="total_nilai_display" placeholder="Total Nilai"
        value="{{ $isEdit ? number_format($workflow->total_nilai, 0, ',', '.') : old('total_nilai_display') }}">
    <input type="hidden" name="total_nilai" id="total_nilai"
        value="{{ $isEdit ? $workflow->total_nilai : old('total_nilai') }}">
</div>

<div class="form-group">
    <label>Waktu Penggunaan</label>
    <input type="month" class="form-control" required name="waktu_penggunaan"
        value="{{ $isEdit ? substr($workflow->waktu_penggunaan, 0, 7) : old('waktu_penggunaan') }}">
</div>

<div class="form-group">
    <label for="account">Account (Chart of Accounts)</label><br>
    @if ($costCenterExists && !empty($accountList))
        <select class="form-control select2" id="account" name="account">
            <option value="">-- Select Account --</option>
            @foreach ($accountList as $account)
                <option value="{{ $account['account_id'] }}"
                    {{ ($isEdit && $workflow->account == $account['account_id']) || old('account') == $account['account_id'] ? 'selected' : '' }}>
                    {{ $account['account_id'] }} - {{ $account['account_name'] }}
                </option>
            @endforeach
        </select>
    @elseif(!$costCenterExists)
        <select class="form-control select2" id="account" name="account">
            <option value="">-- Pilih Cost Center terlebih dahulu --</option>
            @if ($isEdit && $workflow->account)
                <option value="{{ $workflow->account }}" selected>{{ $workflow->account }} (Current)</option>
            @endif
        </select>
    @else
        <select class="form-control select2" id="account" name="account">
            <option value="">-- No accounts available --</option>
            @if ($isEdit && $workflow->account)
                <option value="{{ $workflow->account }}" selected>{{ $workflow->account }} (Current)</option>
            @endif
        </select>
    @endif
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            // Format currency input
            $('#total_nilai_display').on('input', function() {
                // Remove non-numeric characters
                var value = $(this).val().replace(/[^\d]/g, '');
                // Format with thousand separator
                var formattedValue = new Intl.NumberFormat('id-ID').format(value);
                $(this).val(formattedValue);
                // Store raw value in hidden input
                $('#total_nilai').val(value);
            });

            @if (!$costCenterExists)
                // Handle cost center selection
                $('#cost_center_select').on('change', function() {
                    var selectedOption = $(this).find('option:selected');
                    var unitCcId = selectedOption.data('unit-cc-id');
                    var ccId = selectedOption.data('cc-id');

                    $('#cost_center_id').val(ccId);
                    $('#unit_cc_id').val(unitCcId);

                    // Fetch accounts based on selected cost center
                    if (unitCcId) {
                        $.ajax({
                            url: '/api/coa/cost-center-account-list',
                            type: 'GET',
                            data: {
                                unit_cc_id: unitCcId
                            },
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                var accountSelect = $('#account');
                                accountSelect.empty();
                                accountSelect.append(
                                    '<option value="">-- Select Account --</option>');

                                // Keep current account if exists (for edit mode)
                                var currentAccount = "{{ $isEdit ? $workflow->account : '' }}";
                                var currentAccountFound = false;

                                if (response && response.length > 0) {
                                    $.each(response, function(index, account) {
                                        var isSelected = (account.account_id ==
                                            currentAccount);
                                        if (isSelected) currentAccountFound = true;

                                        accountSelect.append('<option value="' + account
                                            .account_id + '" ' +
                                            (isSelected ? 'selected' : '') + '>' +
                                            account.account_id + ' - ' + account
                                            .account_name + '</option>');
                                    });

                                    // If current account not found in API response but exists, add it
                                    if (currentAccount && !currentAccountFound) {
                                        accountSelect.append('<option value="' +
                                            currentAccount + '" selected>' +
                                            currentAccount + ' (Current)</option>');
                                    }

                                    accountSelect.prop('disabled', false);
                                } else {
                                    // If no accounts but we have a current one, show it
                                    if (currentAccount) {
                                        accountSelect.append('<option value="' +
                                            currentAccount + '" selected>' +
                                            currentAccount + ' (Current)</option>');
                                    } else {
                                        accountSelect.append(
                                            '<option value="">No accounts available</option>'
                                            );
                                    }
                                    accountSelect.prop('disabled', !currentAccount);
                                }
                            },
                            error: function() {
                                alert('Failed to fetch account list. Please try again.');
                            }
                        });
                    } else {
                        var currentAccount = "{{ $isEdit ? $workflow->account : '' }}";
                        if (currentAccount) {
                            $('#account').empty().append(
                                '<option value="">-- Pilih Cost Center terlebih dahulu --</option>' +
                                '<option value="' + currentAccount + '" selected>' + currentAccount +
                                ' (Current)</option>'
                            ).prop('disabled', true);
                        } else {
                            $('#account').empty().append(
                                    '<option value="">-- Pilih Cost Center terlebih dahulu --</option>')
                                .prop('disabled', true);
                        }
                    }
                });

                // Initialize cost center if selected (for edit mode)
                if ($('#cost_center_select').val()) {
                    $('#cost_center_select').trigger('change');
                }
            @endif
        });
    </script>
@endpush
