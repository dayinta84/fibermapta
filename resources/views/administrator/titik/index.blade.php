@extends('layouts.dashboard')

@section('title', 'Data Jaringan')
@section('topbar_title', auth()->user()->role === 'teknisi' ? 'Data Jaringan FTTH' : 'Kelola Jaringan FTTH')
@section('topbar_subtitle', auth()->user()->role === 'teknisi'
? 'Lihat titik OLT, ODC, ODP, port, dan jalur jaringan'
: 'Kelola titik OLT, ODC, ODP, port, dan jalur otomatis')

@section('content')

<style>
    .titik-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .titik-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 14px;
        flex-wrap: wrap;
    }

    .titik-title {
        margin: 0;
        font-size: 24px;
        font-weight: 900;
        color: #0f172a;
    }

    .titik-subtitle {
        margin: 6px 0 0;
        font-size: 14px;
        color: #64748b;
        line-height: 1.5;
    }

    .viewer-mode-badge {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        padding: 7px 11px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-size: 12px;
        font-weight: 900;
        margin-top: 10px;
    }

    .titik-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .titik-stat {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .titik-stat span {
        display: block;
        font-size: 12px;
        font-weight: 800;
        color: #64748b;
        margin-bottom: 8px;
    }

    .titik-stat strong {
        display: block;
        font-size: 28px;
        line-height: 1;
        font-weight: 900;
        color: #0f172a;
    }

    .titik-layout {
        display: grid;
        gap: 16px;
        align-items: start;
    }

    .titik-layout.manage-mode {
        grid-template-columns: 390px minmax(0, 1fr);
    }

    .titik-layout.viewer-mode {
        grid-template-columns: minmax(0, 1fr) 330px;
    }

    .titik-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        padding: 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .titik-card-title {
        margin: 0;
        font-size: 19px;
        font-weight: 900;
        color: #0f172a;
    }

    .titik-card-subtitle {
        margin: 6px 0 16px;
        font-size: 13px;
        color: #64748b;
        line-height: 1.5;
    }

    .titik-form {
        display: grid;
        gap: 12px;
        padding: 14px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .field.full {
        grid-column: 1 / -1;
    }

    .field label {
        font-size: 12px;
        font-weight: 900;
        color: #334155;
    }

    .field input,
    .field select,
    .field textarea {
        width: 100%;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        padding: 10px 11px;
        background: #ffffff;
        color: #0f172a;
        font-size: 14px;
        outline: none;
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .field textarea {
        min-height: 76px;
        resize: vertical;
    }

    .field small {
        color: #64748b;
        font-size: 12px;
        line-height: 1.4;
    }

    .hidden-field {
        display: none !important;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-modern-primary,
    .btn-modern-secondary {
        border: 0;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: 0.16s ease;
        white-space: nowrap;
    }

    .btn-modern-primary {
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.18);
    }

    .btn-modern-primary:hover {
        transform: translateY(-1px);
    }

    .btn-modern-secondary {
        color: #334155;
        background: #ffffff;
        border: 1px solid #e5e7eb;
    }

    .btn-modern-secondary:hover {
        background: #f8fafc;
    }

    .titik-alert {
        padding: 12px 14px;
        border-radius: 14px;
        font-size: 14px;
        margin-bottom: 12px;
    }

    .titik-alert.success {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #bbf7d0;
    }

    .titik-alert.error {
        color: #991b1b;
        background: #fee2e2;
        border: 1px solid #fecaca;
    }

    .titik-alert ul {
        margin: 8px 0 0 18px;
    }

    .titik-map-box {
        overflow: hidden;
        border-radius: 18px;
        border: 1px solid #e5e7eb;
        background: #e5e7eb;
        min-height: 545px;
    }

    #map {
        width: 100%;
        height: 545px;
    }

    .titik-layout.viewer-mode .titik-map-box {
        min-height: 520px;
    }

    .titik-layout.viewer-mode #map {
        height: 520px;
    }

    .viewer-panel {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .viewer-info-box {
        padding: 14px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
    }

    .viewer-info-title {
        margin: 0 0 8px;
        font-size: 14px;
        font-weight: 900;
        color: #0f172a;
    }

    .viewer-info-text {
        margin: 0;
        font-size: 13px;
        line-height: 1.55;
        color: #64748b;
    }

    .map-legend-list {
        display: grid;
        gap: 8px;
    }

    .map-legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #334155;
    }

    .map-legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        display: inline-block;
    }

    .map-legend-dot.olt {
        background: #7c3aed;
    }

    .map-legend-dot.odc {
        background: #f59e0b;
    }

    .map-legend-dot.odp {
        background: #10b981;
    }

    .titik-table-wrap {
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        background: #ffffff;
    }

    .titik-table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
    }

    .titik-table th {
        text-align: left;
        padding: 13px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #334155;
        font-size: 13px;
        font-weight: 900;
        white-space: nowrap;
    }

    .titik-table td {
        padding: 14px;
        border-bottom: 1px solid #eef2f7;
        color: #0f172a;
        font-size: 14px;
        vertical-align: top;
    }

    .titik-table tr:hover td {
        background: #fafcff;
    }

    .badge-titik {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 54px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
    }

    .badge-titik.olt {
        color: #6d28d9;
        background: #ede9fe;
    }

    .badge-titik.odc {
        color: #b45309;
        background: #fef3c7;
    }

    .badge-titik.odp {
        color: #047857;
        background: #ecfdf5;
    }

    .port-note {
        margin-top: 5px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.4;
    }

    .route-note {
        margin-top: 5px;
        color: #475569;
        font-size: 12px;
        line-height: 1.45;
    }

    .route-note strong {
        color: #0f172a;
        font-weight: 900;
    }

    .port-summary {
        display: inline-flex;
        flex-direction: column;
        gap: 4px;
    }

    .port-count {
        color: #0f172a;
        font-weight: 800;
        white-space: nowrap;
    }

    .port-filled {
        color: #64748b;
        font-size: 12px;
        line-height: 1.3;
    }

    .action-row {
        display: flex;
        gap: 7px;
        flex-wrap: nowrap;
        align-items: center;
        white-space: nowrap;
    }

    .action-row form {
        margin: 0;
    }

    .icon-btn {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        color: #334155;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 900;
        cursor: pointer;
        text-decoration: none;
        transition: 0.16s ease;
    }

    .icon-btn:hover {
        transform: translateY(-1px);
        background: #f8fafc;
    }

    .icon-btn.detail {
        color: #1d4ed8;
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .icon-btn.detail:hover {
        background: #dbeafe;
    }

    .icon-btn.edit {
        color: #334155;
        background: #ffffff;
    }

    .icon-btn.delete {
        color: #dc2626;
        background: #fee2e2;
        border-color: #fecaca;
    }

    .icon-btn.delete:hover {
        background: #fecaca;
    }

    .empty {
        text-align: center;
        color: #64748b;
    }

    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, 0.45);
    }

    .modal-backdrop.open {
        display: flex;
    }

    .modal-card {
        width: 780px;
        max-width: 100%;
        max-height: 92vh;
        overflow-y: auto;
        background: #ffffff;
        border-radius: 22px;
        box-shadow: 0 30px 80px rgba(15, 23, 42, 0.25);
    }

    .modal-header {
        padding: 18px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
    }

    .modal-title {
        font-size: 18px;
        font-weight: 900;
        color: #0f172a;
    }

    .modal-close {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        border: 0;
        background: #f1f5f9;
        cursor: pointer;
        font-weight: 900;
    }

    .modal-body {
        padding: 20px;
    }

    .edit-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    @media (max-width: 1100px) {
        .titik-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .titik-layout,
        .titik-layout.manage-mode,
        .titik-layout.viewer-mode {
            grid-template-columns: 1fr;
        }

        .titik-map-box,
        .titik-layout.viewer-mode .titik-map-box {
            min-height: 440px;
        }

        #map,
        .titik-layout.viewer-mode #map {
            height: 440px;
        }
    }

    @media (max-width: 700px) {

        .titik-stats,
        .edit-form-grid {
            grid-template-columns: 1fr;
        }

        .form-actions,
        .action-row {
            justify-content: flex-start;
        }
    }
</style>

@php
$role = auth()->user()->role ?? null;
$canManageTitik = in_array($role, ['superadmin', 'admin']);

$jumlahOlt = $titik->where('jenis', 'OLT')->count();
$jumlahOdc = $titik->where('jenis', 'ODC')->count();
$jumlahOdp = $titik->where('jenis', 'ODP')->count();

$splitterOptions = ['1:4', '1:8', '1:16'];

$availableOdcPorts = $portOutOdcKosong->map(function ($port) {
return [
'id' => $port->id,
'odc_id' => $port->titik_jaringan_id,
'odc_nama' => optional($port->titikJaringan)->nama,
'nomor_port' => $port->nomor_port,
'label' => (optional($port->titikJaringan)->nama ?? 'ODC') . ' - OUT ' . $port->nomor_port,
];
})->values();

$titikMapData = $titik->map(function ($item) {
return [
'id' => $item->id,
'jenis' => $item->jenis,
'nama' => $item->nama,
'lat' => $item->lat,
'lng' => $item->lng,
'parent' => optional($item->parent)->nama,
'rasio' => $item->rasio_splitter,
'tx_power' => $item->tx_power,
'jenis_jalur' => $item->jenis_jalur,
'jarak_udara_meter' => $item->jarak_udara_meter,
'panjang_kabel_meter' => $item->panjang_kabel_meter,
];
})->values();

$formatMeterView = function ($meter) {
if ($meter === null || $meter === '') {
return '-';
}

if ($meter >= 1000) {
return number_format($meter / 1000, 2) . ' km';
}

return number_format($meter, 2) . ' m';
};
@endphp

<div class="titik-page">

    <div class="titik-head">
        <div>
            <h1 class="titik-title">
                {{ $canManageTitik ? 'Kelola Titik Jaringan' : 'Data Titik Jaringan' }}
            </h1>

            <p class="titik-subtitle">
                @if($canManageTitik)
                OLT sebagai sumber utama, ODC sebagai splitter distribusi, dan ODP sebagai splitter akhir ke pelanggan.
                @else
                Teknisi dapat melihat lokasi OLT, ODC, ODP, jalur jaringan, dan detail port tanpa mengubah data utama.
                @endif
            </p>

            @if(!$canManageTitik)
            <div class="viewer-mode-badge">
                Mode lihat · akses pengubahan data utama hanya tersedia untuk admin
            </div>
            @endif
        </div>
    </div>

    <div class="titik-stats">
        <div class="titik-stat">
            <span>Total Titik</span>
            <strong>{{ $titik->count() }}</strong>
        </div>

        <div class="titik-stat">
            <span>OLT</span>
            <strong>{{ $jumlahOlt }}</strong>
        </div>

        <div class="titik-stat">
            <span>ODC</span>
            <strong>{{ $jumlahOdc }}</strong>
        </div>

        <div class="titik-stat">
            <span>ODP</span>
            <strong>{{ $jumlahOdp }}</strong>
        </div>
    </div>

    @if(session('success'))
    <div class="titik-alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="titik-alert error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="titik-alert error">
        <strong>Periksa kembali input berikut:</strong>
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="titik-layout {{ $canManageTitik ? 'manage-mode' : 'viewer-mode' }}">

        @if($canManageTitik)
        <div class="titik-card">
            <h2 class="titik-card-title">Tambah Titik Jaringan</h2>
            <p class="titik-card-subtitle">
                Klik peta untuk mengambil koordinat, lalu isi jenis titik sesuai alur OLT → ODC → ODP.
            </p>

            <form class="titik-form" method="POST" action="{{ route('titik.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="field">
                        <label>Jenis Titik</label>
                        <select name="jenis" id="jenis" required>
                            <option value="">Pilih jenis</option>
                            <option value="OLT" @selected(old('jenis')==='OLT' )>OLT</option>
                            <option value="ODC" @selected(old('jenis')==='ODC' )>ODC</option>
                            <option value="ODP" @selected(old('jenis')==='ODP' )>ODP</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Nama Titik</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" placeholder="Contoh: ODC 01" required>
                    </div>

                    <div class="field" id="txPowerField">
                        <label>Tx Power OLT (dBm)</label>
                        <input
                            type="number"
                            step="0.01"
                            name="tx_power"
                            id="tx_power"
                            value="{{ old('tx_power') }}"
                            placeholder="Contoh: 3">
                        <small>Khusus OLT. Daya pancar awal OLT.</small>
                    </div>

                    <div class="field" id="parentField">
                        <label>Parent / Sumber</label>
                        <select name="parent_id" id="parent_id">
                            <option value="">Pilih parent</option>

                            <optgroup label="OLT">
                                @foreach($listOlt as $olt)
                                <option value="{{ $olt->id }}" data-jenis="OLT" @selected(old('parent_id')==$olt->id)>
                                    {{ $olt->nama }}
                                </option>
                                @endforeach
                            </optgroup>

                            <optgroup label="ODC">
                                @foreach($listOdc as $odc)
                                <option value="{{ $odc->id }}" data-jenis="ODC" @selected(old('parent_id')==$odc->id)>
                                    {{ $odc->nama }}
                                </option>
                                @endforeach
                            </optgroup>
                        </select>
                        <small id="parentHelp">OLT tidak memakai parent.</small>
                    </div>

                    <div class="field" id="rasioField">
                        <label>Rasio Splitter</label>
                        <select name="rasio_splitter" id="rasio_splitter">
                            <option value="">Pilih rasio</option>
                            @foreach($splitterOptions as $rasio)
                            <option value="{{ $rasio }}" @selected(old('rasio_splitter')===$rasio)>
                                {{ $rasio }}
                            </option>
                            @endforeach
                        </select>
                        <small>Jumlah OUT otomatis mengikuti rasio splitter.</small>
                    </div>

                    <div class="field" id="portOdcField">
                        <label>Port OUT ODC</label>
                        <select name="port_out_odc_id" id="port_out_odc_id">
                            <option value="">Pilih parent ODC dulu</option>
                        </select>
                        <small>Wajib untuk ODP. Port yang sudah dipakai tidak akan muncul.</small>
                    </div>

                    <div class="field" id="panjangKabelField">
                        <label>Panjang Kabel Manual (meter)</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="panjang_kabel_meter"
                            id="panjang_kabel_meter"
                            value="{{ old('panjang_kabel_meter') }}"
                            placeholder="Kosongkan = otomatis">
                        <small>Jika kosong, sistem memakai estimasi jarak koordinat dari parent ke titik ini.</small>
                    </div>

                    <div class="field">
                        <label>Latitude</label>
                        <input type="number" step="any" name="lat" id="lat" value="{{ old('lat') }}" placeholder="-6.200000" required>
                    </div>

                    <div class="field">
                        <label>Longitude</label>
                        <input type="number" step="any" name="lng" id="lng" value="{{ old('lng') }}" placeholder="106.816666" required>
                    </div>

                    <div class="field full">
                        <label>Keterangan</label>
                        <textarea name="keterangan" placeholder="Keterangan opsional">{{ old('keterangan') }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn-modern-secondary">Reset</button>
                    <button type="submit" class="btn-modern-primary">Simpan Titik</button>
                </div>
            </form>
        </div>
        @endif

        <div class="titik-card">
            <h2 class="titik-card-title">Peta Titik Jaringan</h2>
            <p class="titik-card-subtitle">
                @if($canManageTitik)
                Klik peta untuk mengisi latitude dan longitude saat menambah titik.
                @else
                Peta menampilkan lokasi OLT, ODC, dan ODP yang telah tersimpan pada sistem. Klik marker untuk melihat informasi titik jaringan.
                @endif
            </p>

            <div class="titik-map-box">
                <div id="map"></div>
            </div>
        </div>

        @if(!$canManageTitik)
        <div class="viewer-panel">
            <div class="titik-card">
                <h2 class="titik-card-title">Informasi Akses</h2>
                <p class="titik-card-subtitle">
                    Akun teknisi digunakan untuk melihat data jaringan dan detail port.
                </p>

                <div class="viewer-info-box">
                    <h3 class="viewer-info-title">Hak Akses Teknisi</h3>
                    <p class="viewer-info-text">
                        Teknisi dapat melihat titik jaringan, membuka detail ODC/ODP, melihat status port,
                        serta memperbarui redaman port. Teknisi tidak dapat menambah, mengubah, atau menghapus
                        data utama jaringan.
                    </p>
                </div>
            </div>

            <div class="titik-card">
                <h2 class="titik-card-title">Marker Peta</h2>
                <p class="titik-card-subtitle">
                    Warna marker menunjukkan jenis titik jaringan.
                </p>

                <div class="map-legend-list">
                    <div class="map-legend-item">
                        <span class="map-legend-dot olt"></span>
                        <span>OLT · Sumber utama jaringan</span>
                    </div>

                    <div class="map-legend-item">
                        <span class="map-legend-dot odc"></span>
                        <span>ODC · Distribusi jaringan</span>
                    </div>

                    <div class="map-legend-item">
                        <span class="map-legend-dot odp"></span>
                        <span>ODP · Titik distribusi pelanggan</span>
                    </div>
                </div>
            </div>

            <div class="titik-card">
                <h2 class="titik-card-title">Penggunaan</h2>
                <p class="viewer-info-text">
                    Gunakan tombol detail pada daftar titik untuk membuka detail ODC atau ODP.
                    Pada halaman detail, teknisi dapat melihat port IN/OUT dan memperbarui redaman manual.
                </p>
            </div>
        </div>
        @endif

    </div>

    <div class="titik-card">
        <h2 class="titik-card-title">Daftar Titik</h2>
        <p class="titik-card-subtitle">
            Detail ODC menampilkan Port IN dan OUT distribusi. Detail ODP menampilkan Port IN dan OUT pelanggan.
        </p>

        <div class="titik-table-wrap">
            <table class="titik-table">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Nama</th>
                        <th>Parent / Jalur</th>
                        <th>Rasio / Tx</th>
                        <th>Port</th>
                        <th>Koordinat</th>
                        <th style="width: 132px;">
                            {{ $canManageTitik ? 'Aksi' : 'Detail' }}
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($titik as $item)
                    @php
                    $sourcePort = $item->connectedFromPort;

                    $payload = [
                    'id' => $item->id,
                    'jenis' => $item->jenis,
                    'nama' => $item->nama,
                    'lat' => $item->lat,
                    'lng' => $item->lng,
                    'keterangan' => $item->keterangan,
                    'rasio_splitter' => $item->rasio_splitter,
                    'parent_id' => $item->parent_id,
                    'tx_power' => $item->tx_power,
                    'jarak_udara_meter' => $item->jarak_udara_meter,
                    'panjang_kabel_meter' => $item->panjang_kabel_meter,
                    'jenis_jalur' => $item->jenis_jalur,
                    'port_out_odc_id' => optional($sourcePort)->id,
                    'source_port' => $sourcePort ? [
                    'id' => $sourcePort->id,
                    'odc_id' => $sourcePort->titik_jaringan_id,
                    'odc_nama' => optional($sourcePort->titikJaringan)->nama,
                    'nomor_port' => $sourcePort->nomor_port,
                    'label' => (optional($sourcePort->titikJaringan)->nama ?? 'ODC') . ' - OUT ' . $sourcePort->nomor_port,
                    ] : null,
                    ];

                    $portInCount = $item->ports->where('jenis_port', 'in')->count();
                    $portOutCount = $item->ports->where('jenis_port', 'out')->count();
                    $portOutUsed = $item->ports->where('jenis_port', 'out')->where('status', 'terpakai')->count();
                    $hasDetailLink = in_array($item->jenis, ['ODC', 'ODP']);
                    @endphp

                    <tr>
                        <td>
                            <span class="badge-titik {{ strtolower($item->jenis) }}">
                                {{ $item->jenis }}
                            </span>
                        </td>

                        <td>
                            <strong>{{ $item->nama }}</strong>

                            @if($item->keterangan)
                            <div class="port-note">{{ $item->keterangan }}</div>
                            @endif
                        </td>

                        <td>
                            @if($item->parent)
                            <strong>{{ $item->parent->nama }}</strong>
                            @else
                            -
                            @endif

                            @if($item->jenis_jalur)
                            <div class="route-note">
                                {{ $item->jenis_jalur }} |
                                Kabel: <strong>{{ $formatMeterView($item->panjang_kabel_meter) }}</strong>
                            </div>
                            @endif

                            @if($sourcePort)
                            <div class="port-note">
                                Dari {{ optional($sourcePort->titikJaringan)->nama }} Port {{ $sourcePort->nomor_port }}
                            </div>
                            @endif
                        </td>

                        <td>
                            @if($item->jenis === 'OLT')
                            Tx: {{ $item->tx_power !== null ? number_format($item->tx_power, 2) . ' dBm' : '-' }}
                            @else
                            {{ $item->rasio_splitter ?? '-' }}
                            @endif
                        </td>

                        <td>
                            @if($item->jenis === 'OLT')
                            Tidak ada port
                            @else
                            <div class="port-summary">
                                <span class="port-count">IN {{ $portInCount }} / OUT {{ $portOutCount }}</span>
                                <span class="port-filled">Terpakai {{ $portOutUsed }} port</span>
                            </div>
                            @endif
                        </td>

                        <td>
                            {{ $item->lat }}, {{ $item->lng }}
                        </td>

                        <td>
                            <div class="action-row">
                                @if($item->jenis === 'ODC')
                                <a href="{{ route('titik.detail_odc', $item->id) }}"
                                    class="icon-btn detail"
                                    title="Detail ODC"
                                    aria-label="Detail ODC">
                                    ⓘ
                                </a>
                                @endif

                                @if($item->jenis === 'ODP')
                                <a href="{{ route('titik.detail_odp', $item->id) }}"
                                    class="icon-btn detail"
                                    title="Detail ODP"
                                    aria-label="Detail ODP">
                                    ⓘ
                                </a>
                                @endif

                                @if($canManageTitik)
                                <button type="button"
                                    class="icon-btn edit"
                                    title="Edit Titik"
                                    aria-label="Edit Titik"
                                    data-titik='@json($payload)'
                                    onclick="openEditTitikModal(this)">
                                    ✎
                                </button>

                                <form method="POST"
                                    action="{{ route('titik.destroy', $item->id) }}"
                                    onsubmit="return confirm('Hapus titik jaringan ini?');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        class="icon-btn delete"
                                        title="Hapus Titik"
                                        aria-label="Hapus Titik">
                                        ×
                                    </button>
                                </form>
                                @elseif(!$hasDetailLink)
                                <span class="empty">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="empty">Belum ada titik jaringan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@if($canManageTitik)
<div class="modal-backdrop" id="editTitikModal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-header">
            <div class="modal-title">Edit Titik Jaringan</div>
            <button type="button" class="modal-close" onclick="closeEditTitikModal()">✕</button>
        </div>

        <div class="modal-body">
            <form method="POST" id="editTitikForm" class="titik-form">
                @csrf
                @method('PUT')

                <div class="edit-form-grid">
                    <div class="field">
                        <label>Jenis Titik</label>
                        <select name="jenis" id="edit_jenis" required>
                            <option value="OLT">OLT</option>
                            <option value="ODC">ODC</option>
                            <option value="ODP">ODP</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Nama Titik</label>
                        <input type="text" name="nama" id="edit_nama" required>
                    </div>

                    <div class="field" id="editTxPowerField">
                        <label>Tx Power OLT (dBm)</label>
                        <input
                            type="number"
                            step="0.01"
                            name="tx_power"
                            id="edit_tx_power"
                            placeholder="Contoh: 3">
                        <small>Khusus OLT. Daya pancar awal OLT.</small>
                    </div>

                    <div class="field" id="editParentField">
                        <label>Parent / Sumber</label>
                        <select name="parent_id" id="edit_parent_id">
                            <option value="">Pilih parent</option>

                            <optgroup label="OLT">
                                @foreach($listOlt as $olt)
                                <option value="{{ $olt->id }}" data-jenis="OLT">
                                    {{ $olt->nama }}
                                </option>
                                @endforeach
                            </optgroup>

                            <optgroup label="ODC">
                                @foreach($listOdc as $odc)
                                <option value="{{ $odc->id }}" data-jenis="ODC">
                                    {{ $odc->nama }}
                                </option>
                                @endforeach
                            </optgroup>
                        </select>
                        <small id="editParentHelp">OLT tidak memakai parent.</small>
                    </div>

                    <div class="field" id="editRasioField">
                        <label>Rasio Splitter</label>
                        <select name="rasio_splitter" id="edit_rasio_splitter">
                            <option value="">Pilih rasio</option>
                            @foreach($splitterOptions as $rasio)
                            <option value="{{ $rasio }}">{{ $rasio }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field" id="editPortOdcField">
                        <label>Port OUT ODC</label>
                        <select name="port_out_odc_id" id="edit_port_out_odc_id">
                            <option value="">Pilih parent ODC dulu</option>
                        </select>
                        <small>
                            Kosongkan jika tidak ingin mengganti port lama. Pilih port lain kalau ODP dipindahkan.
                        </small>
                    </div>

                    <div class="field" id="editPanjangKabelField">
                        <label>Panjang Kabel Manual (meter)</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="panjang_kabel_meter"
                            id="edit_panjang_kabel_meter"
                            placeholder="Kosongkan = otomatis">
                        <small>Jika kosong, sistem memakai estimasi jarak koordinat dari parent ke titik ini.</small>
                    </div>

                    <div class="field">
                        <label>Latitude</label>
                        <input type="number" step="any" name="lat" id="edit_lat" required>
                    </div>

                    <div class="field">
                        <label>Longitude</label>
                        <input type="number" step="any" name="lng" id="edit_lng" required>
                    </div>

                    <div class="field full">
                        <label>Keterangan</label>
                        <textarea name="keterangan" id="edit_keterangan"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-modern-secondary" onclick="closeEditTitikModal()">Batal</button>
                    <button type="submit" class="btn-modern-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    const availableOdcPorts = @json($availableOdcPorts);
    const titikMapData = @json($titikMapData);
    const canManageTitik = @json($canManageTitik);

    const jenisSelect = document.getElementById('jenis');
    const parentSelect = document.getElementById('parent_id');
    const rasioField = document.getElementById('rasioField');
    const parentField = document.getElementById('parentField');
    const portOdcField = document.getElementById('portOdcField');
    const portOdcSelect = document.getElementById('port_out_odc_id');
    const parentHelp = document.getElementById('parentHelp');

    const txPowerField = document.getElementById('txPowerField');
    const txPowerInput = document.getElementById('tx_power');
    const panjangKabelField = document.getElementById('panjangKabelField');
    const panjangKabelInput = document.getElementById('panjang_kabel_meter');

    function setParentOptionsByJenis(parentElement, allowedJenis) {
        if (!parentElement) return;

        Array.from(parentElement.options).forEach(function(option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = option.dataset.jenis !== allowedJenis;
        });

        const selectedOption = parentElement.options[parentElement.selectedIndex];

        if (selectedOption && selectedOption.value && selectedOption.dataset.jenis !== allowedJenis) {
            parentElement.value = '';
        }
    }

    function populateOdcPortOptions(selectElement, odcId, selectedPortId = null, currentPort = null) {
        if (!selectElement) return;

        selectElement.innerHTML = '';

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = odcId ? 'Pilih Port OUT ODC' : 'Pilih parent ODC dulu';
        selectElement.appendChild(defaultOption);

        let ports = availableOdcPorts.filter(function(port) {
            return String(port.odc_id) === String(odcId);
        });

        if (currentPort && String(currentPort.odc_id) === String(odcId)) {
            const alreadyExists = ports.some(function(port) {
                return String(port.id) === String(currentPort.id);
            });

            if (!alreadyExists) {
                ports.unshift(currentPort);
            }
        }

        ports.forEach(function(port) {
            const option = document.createElement('option');
            option.value = port.id;
            option.textContent = port.label;

            if (selectedPortId && String(selectedPortId) === String(port.id)) {
                option.selected = true;
            }

            selectElement.appendChild(option);
        });

        if (odcId && ports.length === 0) {
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = 'Tidak ada OUT ODC kosong';
            selectElement.appendChild(emptyOption);
        }
    }

    function updateCreateFormVisibility() {
        if (!jenisSelect) return;

        const jenis = jenisSelect.value;
        const rasioSelect = document.getElementById('rasio_splitter');

        if (!jenis) {
            parentField.classList.add('hidden-field');
            rasioField.classList.add('hidden-field');
            portOdcField.classList.add('hidden-field');
            txPowerField.classList.add('hidden-field');
            panjangKabelField.classList.add('hidden-field');

            parentSelect.required = false;
            portOdcSelect.required = false;
            rasioSelect.required = false;
            txPowerInput.required = false;
            panjangKabelInput.required = false;

            return;
        }

        if (jenis === 'OLT') {
            parentField.classList.add('hidden-field');
            rasioField.classList.add('hidden-field');
            portOdcField.classList.add('hidden-field');
            txPowerField.classList.remove('hidden-field');
            panjangKabelField.classList.add('hidden-field');

            parentSelect.value = '';
            portOdcSelect.value = '';
            rasioSelect.value = '';
            panjangKabelInput.value = '';

            parentSelect.required = false;
            portOdcSelect.required = false;
            rasioSelect.required = false;
            txPowerInput.required = false;
            panjangKabelInput.required = false;
        }

        if (jenis === 'ODC') {
            parentField.classList.remove('hidden-field');
            rasioField.classList.remove('hidden-field');
            portOdcField.classList.add('hidden-field');
            txPowerField.classList.add('hidden-field');
            panjangKabelField.classList.remove('hidden-field');

            txPowerInput.value = '';
            parentHelp.textContent = 'ODC wajib memilih parent OLT.';
            setParentOptionsByJenis(parentSelect, 'OLT');

            parentSelect.required = true;
            portOdcSelect.required = false;
            rasioSelect.required = true;
            txPowerInput.required = false;
            panjangKabelInput.required = false;
            portOdcSelect.value = '';
        }

        if (jenis === 'ODP') {
            parentField.classList.remove('hidden-field');
            rasioField.classList.remove('hidden-field');
            portOdcField.classList.remove('hidden-field');
            txPowerField.classList.add('hidden-field');
            panjangKabelField.classList.remove('hidden-field');

            txPowerInput.value = '';
            parentHelp.textContent = 'ODP wajib memilih parent ODC.';
            setParentOptionsByJenis(parentSelect, 'ODC');
            populateOdcPortOptions(portOdcSelect, parentSelect.value, "{{ old('port_out_odc_id') }}");

            parentSelect.required = true;
            portOdcSelect.required = true;
            rasioSelect.required = true;
            txPowerInput.required = false;
            panjangKabelInput.required = false;
        }
    }

    if (jenisSelect) {
        jenisSelect.addEventListener('change', updateCreateFormVisibility);
    }

    if (parentSelect) {
        parentSelect.addEventListener('change', function() {
            if (jenisSelect && jenisSelect.value === 'ODP') {
                populateOdcPortOptions(portOdcSelect, parentSelect.value);
            }
        });
    }

    updateCreateFormVisibility();

    const editModal = document.getElementById('editTitikModal');
    const editForm = document.getElementById('editTitikForm');

    const editJenis = document.getElementById('edit_jenis');
    const editParent = document.getElementById('edit_parent_id');
    const editRasio = document.getElementById('edit_rasio_splitter');
    const editParentField = document.getElementById('editParentField');
    const editRasioField = document.getElementById('editRasioField');
    const editPortOdcField = document.getElementById('editPortOdcField');
    const editPortOdc = document.getElementById('edit_port_out_odc_id');
    const editParentHelp = document.getElementById('editParentHelp');

    const editTxPowerField = document.getElementById('editTxPowerField');
    const editTxPower = document.getElementById('edit_tx_power');
    const editPanjangKabelField = document.getElementById('editPanjangKabelField');
    const editPanjangKabel = document.getElementById('edit_panjang_kabel_meter');

    let currentEditTitik = null;

    function updateEditFormVisibility() {
        if (!editJenis) return;

        const jenis = editJenis.value;

        if (jenis === 'OLT') {
            editParentField.classList.add('hidden-field');
            editRasioField.classList.add('hidden-field');
            editPortOdcField.classList.add('hidden-field');
            editTxPowerField.classList.remove('hidden-field');
            editPanjangKabelField.classList.add('hidden-field');

            editParent.value = '';
            editRasio.value = '';
            editPortOdc.value = '';
            editPanjangKabel.value = '';

            editParent.required = false;
            editRasio.required = false;
            editPortOdc.required = false;
            editTxPower.required = false;
            editPanjangKabel.required = false;
        }

        if (jenis === 'ODC') {
            editParentField.classList.remove('hidden-field');
            editRasioField.classList.remove('hidden-field');
            editPortOdcField.classList.add('hidden-field');
            editTxPowerField.classList.add('hidden-field');
            editPanjangKabelField.classList.remove('hidden-field');

            editTxPower.value = '';
            editParentHelp.textContent = 'ODC wajib memilih parent OLT.';
            setParentOptionsByJenis(editParent, 'OLT');

            editParent.required = true;
            editRasio.required = true;
            editPortOdc.required = false;
            editTxPower.required = false;
            editPanjangKabel.required = false;
            editPortOdc.value = '';
        }

        if (jenis === 'ODP') {
            editParentField.classList.remove('hidden-field');
            editRasioField.classList.remove('hidden-field');
            editPortOdcField.classList.remove('hidden-field');
            editTxPowerField.classList.add('hidden-field');
            editPanjangKabelField.classList.remove('hidden-field');

            editTxPower.value = '';
            editParentHelp.textContent = 'ODP wajib memilih parent ODC.';
            setParentOptionsByJenis(editParent, 'ODC');

            editParent.required = true;
            editRasio.required = true;
            editPortOdc.required = false;
            editTxPower.required = false;
            editPanjangKabel.required = false;

            populateOdcPortOptions(
                editPortOdc,
                editParent.value,
                currentEditTitik?.port_out_odc_id ?? null,
                currentEditTitik?.source_port ?? null
            );
        }
    }

    function openEditTitikModal(button) {
        const raw = button.getAttribute('data-titik');

        if (!raw || !editModal || !editForm) return;

        currentEditTitik = JSON.parse(raw);

        editForm.action = "{{ url('/titik-jaringan') }}/" + currentEditTitik.id;

        document.getElementById('edit_nama').value = currentEditTitik.nama ?? '';
        document.getElementById('edit_lat').value = currentEditTitik.lat ?? '';
        document.getElementById('edit_lng').value = currentEditTitik.lng ?? '';
        document.getElementById('edit_keterangan').value = currentEditTitik.keterangan ?? '';

        editJenis.value = currentEditTitik.jenis ?? 'OLT';
        editParent.value = currentEditTitik.parent_id ?? '';
        editRasio.value = currentEditTitik.rasio_splitter ?? '';
        editTxPower.value = currentEditTitik.tx_power ?? '';
        editPanjangKabel.value = currentEditTitik.panjang_kabel_meter ?? '';

        updateEditFormVisibility();

        editModal.classList.add('open');
        editModal.setAttribute('aria-hidden', 'false');
    }

    function closeEditTitikModal() {
        if (!editModal) return;

        editModal.classList.remove('open');
        editModal.setAttribute('aria-hidden', 'true');
        currentEditTitik = null;
    }

    if (editJenis) {
        editJenis.addEventListener('change', updateEditFormVisibility);
    }

    if (editParent) {
        editParent.addEventListener('change', function() {
            if (editJenis && editJenis.value === 'ODP') {
                populateOdcPortOptions(
                    editPortOdc,
                    editParent.value,
                    null,
                    currentEditTitik?.source_port ?? null
                );
            }
        });
    }

    window.openEditTitikModal = openEditTitikModal;
    window.closeEditTitikModal = closeEditTitikModal;

    function initTitikMap() {
        const mapElement = document.getElementById('map');

        if (!mapElement || typeof L === 'undefined') {
            return;
        }

        const validPoints = titikMapData.filter(function(item) {
            return item.lat && item.lng;
        });

        const defaultLat = validPoints.length ? Number(validPoints[0].lat) : -6.200000;
        const defaultLng = validPoints.length ? Number(validPoints[0].lng) : 106.816666;

        const map = L.map('map').setView([defaultLat, defaultLng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const markerColors = {
            OLT: '#7c3aed',
            ODC: '#f59e0b',
            ODP: '#10b981'
        };

        const bounds = [];

        validPoints.forEach(function(item) {
            const lat = Number(item.lat);
            const lng = Number(item.lng);

            if (Number.isNaN(lat) || Number.isNaN(lng)) return;

            bounds.push([lat, lng]);

            const color = markerColors[item.jenis] || '#2563eb';

            const marker = L.circleMarker([lat, lng], {
                radius: 9,
                color: color,
                fillColor: color,
                fillOpacity: 0.88,
                weight: 2
            }).addTo(map);

            marker.bindPopup(`
                <div style="min-width:220px;">
                    <strong style="font-size:14px;">${escapeHtml(item.nama)}</strong><br>
                    <span>Jenis: ${escapeHtml(item.jenis)}</span><br>
                    <span>Parent: ${escapeHtml(item.parent || '-')}</span><br>
                    <span>Rasio: ${escapeHtml(item.rasio || '-')}</span><br>
                    <span>Tx Power: ${item.tx_power !== null && item.tx_power !== undefined ? escapeHtml(item.tx_power + ' dBm') : '-'}</span><br>
                    <span>Jalur: ${escapeHtml(item.jenis_jalur || '-')}</span><br>
                    <span>Panjang Kabel: ${escapeHtml(formatMeterJs(item.panjang_kabel_meter))}</span>
                </div>
            `);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, {
                padding: [28, 28]
            });

            setTimeout(function() {
                map.invalidateSize();
                map.fitBounds(bounds, {
                    padding: [28, 28]
                });
            }, 250);
        }

        if (canManageTitik) {
            map.on('click', function(event) {
                const latInput = document.getElementById('lat');
                const lngInput = document.getElementById('lng');

                if (latInput && lngInput) {
                    latInput.value = event.latlng.lat.toFixed(8);
                    lngInput.value = event.latlng.lng.toFixed(8);
                }
            });
        }

        window.dashboardMap = map;
    }

    function formatMeterJs(meter) {
        if (meter === null || meter === undefined || meter === '') {
            return '-';
        }

        const value = Number(meter);

        if (Number.isNaN(value)) {
            return '-';
        }

        if (value >= 1000) {
            return (value / 1000).toFixed(2) + ' km';
        }

        return value.toFixed(2) + ' m';
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';

        return String(text)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    document.addEventListener('DOMContentLoaded', initTitikMap);
</script>
@endpush