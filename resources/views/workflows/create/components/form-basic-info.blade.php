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
    $costCenterId = $employeeDetails['cost_center_id'] ?? '';
    $unitKerja = $employeeDetails['unit'] ?? '';

    // Fetch cost center list from API
    $costCenterUrl = "http://10.204.222.12/backend-fista/costcenter/getlist";
    $costCenterResponse = Http::get($costCenterUrl);
    $costCenterData = $costCenterResponse->json()['data'] ?? [];

    // Check if user's costCenterId exists in API data
    $costCenterExists = false;
    $unitCcId = null;
    foreach ($costCenterData as $cc) {
        if ($cc['cc_id'] == $costCenterId) {
            $costCenterExists = true;
            $unitCcId = $cc['unit_cc_id'];
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
                <option value="{{ $cc['cc_name'] }}"
                    data-cc-id="{{ $cc['cc_id'] }}"
                    data-unit-cc-id="{{ $cc['unit_cc_id'] }}">
                    {{ $cc['cc_name'] }} ({{ $cc['cc_id'] }})
                </option>
            @endforeach
        </select>
        <input type="hidden" name="cost_center_id" id="cost_center_id">
        <input type="hidden" name="unit_cc_id" id="unit_cc_id">
        <div class="alert alert-warning mt-2">
            <small>Perhatian: Cost Center ID Anda tidak ditemukan. Silakan pilih Cost Center secara manual.</small>
        </div>
    @endif
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
</div>

<div class="form-group">
    <label>Waktu Penggunaan</label>
    <input type="month" class="form-control" required name="waktu_penggunaan"
        value="{{ old('waktu_penggunaan') }}">
</div>

<div class="form-group">
    <label for="account">Account (Chart of Accounts)</label><br>
    @if ($costCenterExists && !empty($accountList))
        <select class="form-control select2" id="account" name="account">
            <option value="">-- Select Account --</option>
            @foreach ($accountList as $account)
                <option value="{{ $account['account_id'] }}" {{ old('account') == $account['account_id'] ? 'selected' : '' }}>
                    {{ $account['account_id'] }} - {{ $account['account_name'] }}
                </option>
            @endforeach
        </select>
    @elseif(!$costCenterExists)
        <select class="form-control select2" id="account" name="account">
            <option value="">-- Pilih Cost Center terlebih dahulu --</option>
        </select>
    @else
        <select class="form-control select2" id="account" name="account" >
            <option value="">-- No accounts available --</option>
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
                    data: { unit_cc_id: unitCcId },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        var accountSelect = $('#account');
                        accountSelect.empty();
                        accountSelect.append('<option value="">-- Select Account --</option>');

                        if (response && response.length > 0) {
                            $.each(response, function(index, account) {
                                accountSelect.append('<option value="' + account.account_id + '">' +
                                    account.account_id + ' - ' + account.account_name + '</option>');
                            });
                            accountSelect.prop('disabled', false);
                        } else {
                            accountSelect.append('<option value="">No accounts available</option>');
                            accountSelect.prop('disabled', true);
                        }
                    },
                    error: function() {
                        alert('Failed to fetch account list. Please try again.');
                    }
                });
            } else {
                $('#account').empty().append('<option value="">-- Pilih Cost Center terlebih dahulu --</option>').prop('disabled', true);
            }
        });
        @endif
    });
</script>
@endpush
