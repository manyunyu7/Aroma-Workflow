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
</div>

<div class="form-group">
    <label>Waktu Penggunaan</label>
    <input type="month" class="form-control" required name="waktu_penggunaan"
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
