@extends('layouts.dashboard')

@section('title', 'Data Pelanggan')
@section('topbar_title', 'Data Pelanggan')
@section('topbar_subtitle', auth()->user()->role === 'teknisi'
? 'Lihat data pelanggan berdasarkan Port OUT ODP'
: 'Kelola pelanggan berdasarkan Port OUT ODP')

@section('content')

<style>
    .pelanggan-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
        color: #0f172a;
    }

    .pelanggan-hero,
    .pelanggan-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06);
    }

    .pelanggan-hero {
        padding: 20px 22px;
    }

    .pelanggan-card {
        padding: 20px;
    }

    .pelanggan-hero-main {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: start;
        gap: 18px;
    }

    .pelanggan-kicker {
        display: inline-flex;
        align-items: center;
        margin-bottom: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        color: #1d4ed8;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        font-size: 12px;
        font-weight: 900;
    }

    .pelanggan-title {
        margin: 0;
        color: #020617;
        font-size: 34px;
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.5px;
    }

    .pelanggan-subtitle {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.45;
    }

    .pelanggan-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-modern-primary,
    .btn-modern-secondary,
    .btn-modern-soft,
    .btn-modern-danger {
        min-height: 38px;
        border-radius: 13px;
        padding: 9px 13px;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        transition: .16s ease;
        white-space: nowrap;
        border: 0;
    }

    .btn-modern-primary {
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        box-shadow: 0 12px 24px rgba(37, 99, 235, .20);
    }

    .btn-modern-secondary {
        color: #334155;
        background: #ffffff;
        border: 1px solid #e5e7eb;
    }

    .btn-modern-soft {
        color: #1d4ed8;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
    }

    .btn-modern-danger {
        color: #dc2626;
        background: #fee2e2;
        border: 1px solid #fecaca;
    }

    .btn-modern-primary:hover,
    .btn-modern-secondary:hover,
    .btn-modern-soft:hover,
    .btn-modern-danger:hover {
        transform: translateY(-1px);
    }

    .pelanggan-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 18px;
    }

    .pelanggan-stat {
        padding: 12px 13px;
        border-radius: 16px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
    }

    .pelanggan-stat span {
        display: block;
        margin-bottom: 5px;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
    }

    .pelanggan-stat strong {
        display: block;
        color: #020617;
        font-size: 22px;
        line-height: 1;
        font-weight: 900;
    }

    .pelanggan-alert {
        padding: 12px 14px;
        border-radius: 14px;
        font-size: 14px;
        border: 1px solid transparent;
    }

    .pelanggan-alert.success {
        color: #166534;
        background: #dcfce7;
        border-color: #bbf7d0;
    }

    .pelanggan-alert.error {
        color: #991b1b;
        background: #fee2e2;
        border-color: #fecaca;
    }

    .pelanggan-alert ul {
        margin: 8px 0 0 18px;
    }

    .pelanggan-card-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .pelanggan-card-title {
        margin: 0;
        color: #020617;
        font-size: 22px;
        font-weight: 900;
        line-height: 1.2;
    }

    .pelanggan-card-subtitle {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.45;
    }

    .pelanggan-form-wrap {
        display: none;
    }

    .pelanggan-form-wrap.open {
        display: block;
    }

    .pelanggan-form {
        display: grid;
        gap: 14px;
        padding: 16px;
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
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
        color: #334155;
        font-size: 12px;
        font-weight: 900;
    }

    .field input,
    .field select,
    .field textarea {
        width: 100%;
        border: 1px solid #dbe3ef;
        border-radius: 12px;
        padding: 10px 11px;
        color: #0f172a;
        background: #ffffff;
        font-size: 14px;
        outline: none;
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
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

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .table-wrap {
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        background: #ffffff;
    }

    .pelanggan-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
        background: #ffffff;
    }

    .pelanggan-table th {
        text-align: left;
        padding: 13px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .pelanggan-table td {
        padding: 14px;
        border-bottom: 1px solid #eef2f7;
        color: #0f172a;
        font-size: 14px;
        vertical-align: top;
    }

    .pelanggan-table tr:last-child td {
        border-bottom: 0;
    }

    .pelanggan-table tr:hover td {
        background: #f8fafc;
    }

    .pelanggan-name {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .pelanggan-avatar {
        width: 38px;
        height: 38px;
        border-radius: 14px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        flex: 0 0 auto;
    }

    .pelanggan-name strong {
        display: block;
        color: #020617;
        font-size: 14px;
        font-weight: 900;
    }

    .muted-note {
        margin-top: 5px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.4;
    }

    .service-badge,
    .port-badge,
    .coord-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .service-badge {
        color: #334155;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }

    .port-badge {
        color: #1d4ed8;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
    }

    .coord-badge {
        color: #047857;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
    }

    .address-text {
        max-width: 280px;
        color: #475569;
        line-height: 1.45;
    }

    .action-row {
        display: flex;
        gap: 8px;
        flex-wrap: nowrap;
        align-items: center;
    }

    .action-row form {
        margin: 0;
    }

    .icon-btn {
        width: 38px;
        height: 38px;
        border: 1px solid #dbeafe;
        border-radius: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        font-weight: 900;
        line-height: 1;
        transition: .16s ease;
    }

    .icon-btn:hover {
        transform: translateY(-1px);
    }

    .icon-btn.edit {
        color: #1d4ed8;
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .icon-btn.delete {
        color: #dc2626;
        background: #fee2e2;
        border-color: #fecaca;
    }

    .empty-row {
        padding: 38px 16px !important;
        text-align: center;
        color: #64748b;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .empty-state-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: #f1f5f9;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 900;
    }

    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, .45);
        backdrop-filter: blur(4px);
    }

    .modal-backdrop.open {
        display: flex;
    }

    .modal-card {
        width: min(760px, 100%);
        max-height: 92vh;
        overflow-y: auto;
        background: #ffffff;
        border-radius: 22px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 30px 80px rgba(15, 23, 42, .25);
    }

    .modal-header {
        padding: 18px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
    }

    .modal-title {
        color: #020617;
        font-size: 20px;
        font-weight: 900;
    }

    .modal-subtitle {
        margin-top: 4px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.4;
    }

    .modal-close {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 12px;
        background: #f1f5f9;
        cursor: pointer;
        color: #334155;
        font-weight: 900;
    }

    .modal-body {
        padding: 20px;
    }

    @media (max-width: 1100px) {
        .pelanggan-hero-main {
            grid-template-columns: 1fr;
        }

        .pelanggan-actions {
            justify-content: flex-start;
        }

        .pelanggan-stats,
        .form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {

        .pelanggan-stats,
        .form-grid {
            grid-template-columns: 1fr;
        }

        .pelanggan-actions,
        .pelanggan-actions .btn-modern-primary {
            width: 100%;
        }

        .form-actions {
            justify-content: flex-start;
        }

        .form-actions .btn-modern-primary,
        .form-actions .btn-modern-secondary {
            width: 100%;
        }

        .pelanggan-title {
            font-size: 30px;
        }
    }
</style>

@php
$role = auth()->user()->role ?? null;
$canManagePelanggan = in_array($role, ['superadmin', 'admin']);

$totalPelanggan = $pelanggan->count();
$totalOdp = $listOdp->count();
$totalPortKosong = $portOutOdpKosong->count();

$pelangganDenganPort = $pelanggan->filter(function ($item) {
return $item->port !== null;
})->count();

$availableOdpPorts = $portOutOdpKosong->map(function ($port) {
return [
'id' => $port->id,
'odp_id' => $port->titik_jaringan_id,
'odp_nama' => optional($port->titikJaringan)->nama,
'nomor_port' => $port->nomor_port,
'label' => (optional($port->titikJaringan)->nama ?? 'ODP') . ' - OUT ' . $port->nomor_port,
];
})->values();

$oldOdpId = old('odp_id');
$oldPortId = old('port_id');
@endphp

<div class="pelanggan-page">

    <section class="pelanggan-hero">
        <div class="pelanggan-hero-main">
            <div>
                <div class="pelanggan-kicker">Pelanggan / Port OUT ODP</div>

                <h1 class="pelanggan-title">Data Pelanggan</h1>

                <p class="pelanggan-subtitle">
                    Pelanggan wajib masuk ke Port OUT ODP. Port yang sudah dipakai tidak bisa digunakan oleh pelanggan lain.
                </p>
            </div>

            @if($canManagePelanggan)
            <div class="pelanggan-actions">
                <button type="button" class="btn-modern-primary" id="btnTogglePelangganForm">
                    + Tambah Pelanggan
                </button>
            </div>
            @endif
        </div>

        <div class="pelanggan-stats">
            <div class="pelanggan-stat">
                <span>Total Pelanggan</span>
                <strong>{{ $totalPelanggan }}</strong>
            </div>

            <div class="pelanggan-stat">
                <span>Pelanggan Aktif Port</span>
                <strong>{{ $pelangganDenganPort }}</strong>
            </div>

            <div class="pelanggan-stat">
                <span>Total ODP</span>
                <strong>{{ $totalOdp }}</strong>
            </div>

            <div class="pelanggan-stat">
                <span>Port OUT Tersedia</span>
                <strong>{{ $totalPortKosong }}</strong>
            </div>
        </div>
    </section>

    @if(session('success'))
    <div class="pelanggan-alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="pelanggan-alert error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="pelanggan-alert error">
        <strong>Periksa kembali input berikut:</strong>
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($canManagePelanggan)
    <section class="pelanggan-card pelanggan-form-wrap {{ $errors->any() ? 'open' : '' }}" id="pelangganFormWrap">
        <div class="pelanggan-card-head">
            <div>
                <h2 class="pelanggan-card-title">Tambah Pelanggan</h2>
                <p class="pelanggan-card-subtitle">
                    Pilih ODP terlebih dahulu, lalu pilih Port OUT ODP yang masih tersedia.
                </p>
            </div>
        </div>

        <form class="pelanggan-form" method="POST" action="{{ route('pelanggan.store') }}">
            @csrf

            <div class="form-grid">
                <div class="field">
                    <label>ODP</label>
                    <select name="odp_id" id="odp_id" required>
                        <option value="">Pilih ODP</option>
                        @foreach($listOdp as $odp)
                        <option value="{{ $odp->id }}" @selected(old('odp_id')==$odp->id)>
                            {{ $odp->nama }}
                        </option>
                        @endforeach
                    </select>
                    <small>Pilih ODP tempat pelanggan terhubung.</small>
                </div>

                <div class="field">
                    <label>Port OUT ODP</label>
                    <select name="port_id" id="port_id" required>
                        <option value="">Pilih ODP dulu</option>
                    </select>
                    <small>Hanya port kosong yang muncul.</small>
                </div>

                <div class="field">
                    <label>Nama Pelanggan</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Contoh: Ayu">
                </div>

                <div class="field">
                    <label>No. Layanan</label>
                    <input type="text" name="no_layanan" value="{{ old('no_layanan') }}" placeholder="Contoh: 001">
                </div>

                <div class="field">
                    <label>Latitude</label>
                    <input type="number" step="any" name="lat" value="{{ old('lat') }}" placeholder="-7.681234">
                </div>

                <div class="field">
                    <label>Longitude</label>
                    <input type="number" step="any" name="lng" value="{{ old('lng') }}" placeholder="111.550000">
                </div>

                <div class="field full">
                    <label>Alamat</label>
                    <textarea name="alamat" placeholder="Alamat pelanggan">{{ old('alamat') }}</textarea>
                </div>

                <div class="field full">
                    <label>Keterangan</label>
                    <textarea name="keterangan" placeholder="Keterangan opsional">{{ old('keterangan') }}</textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-modern-secondary" id="btnCancelPelangganForm">
                    Batal
                </button>

                <button type="submit" class="btn-modern-primary">
                    Simpan Pelanggan
                </button>
            </div>
        </form>
    </section>
    @endif

    <section class="pelanggan-card">
        <div class="pelanggan-card-head">
            <div>
                <h2 class="pelanggan-card-title">Daftar Pelanggan</h2>
                <p class="pelanggan-card-subtitle">
                    Daftar pelanggan berdasarkan Port OUT ODP yang digunakan.
                </p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="pelanggan-table">
                <thead>
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>No. Layanan</th>
                        <th>ODP / Port</th>
                        <th>Alamat</th>
                        <th>Koordinat</th>
                        @if($canManagePelanggan)
                        <th style="width: 120px;">Aksi</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse($pelanggan as $item)
                    @php
                    $port = $item->port;
                    $odp = $port?->titikJaringan;

                    $currentPortPayload = $port ? [
                    'id' => $port->id,
                    'odp_id' => $port->titik_jaringan_id,
                    'odp_nama' => optional($odp)->nama,
                    'nomor_port' => $port->nomor_port,
                    'label' => (optional($odp)->nama ?? 'ODP') . ' - OUT ' . $port->nomor_port,
                    ] : null;

                    $payload = [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'no_layanan' => $item->no_layanan,
                    'alamat' => $item->alamat,
                    'lat' => $item->lat,
                    'lng' => $item->lng,
                    'keterangan' => $item->keterangan,
                    'odp_id' => optional($odp)->id,
                    'port_id' => optional($port)->id,
                    'current_port' => $currentPortPayload,
                    ];

                    $initial = mb_strtoupper(mb_substr($item->nama, 0, 1));
                    @endphp

                    <tr>
                        <td>
                            <div class="pelanggan-name">
                                <div class="pelanggan-avatar">{{ $initial }}</div>

                                <div>
                                    <strong>{{ $item->nama }}</strong>

                                    @if($item->keterangan)
                                    <div class="muted-note">{{ $item->keterangan }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($item->no_layanan)
                            <span class="service-badge">{{ $item->no_layanan }}</span>
                            @else
                            <span class="muted-note">-</span>
                            @endif
                        </td>

                        <td>
                            @if($port && $odp)
                            <span class="port-badge">
                                {{ $odp->nama }} - OUT {{ $port->nomor_port }}
                            </span>

                            <div class="muted-note">
                                Parent: {{ $odp->parent?->nama ?? '-' }}
                            </div>
                            @else
                            <span class="muted-note">Belum memiliki port</span>
                            @endif
                        </td>

                        <td>
                            <div class="address-text">
                                {{ $item->alamat ?? '-' }}
                            </div>
                        </td>

                        <td>
                            @if($item->lat && $item->lng)
                            <span class="coord-badge">
                                {{ $item->lat }}, {{ $item->lng }}
                            </span>
                            @else
                            <span class="muted-note">-</span>
                            @endif
                        </td>

                        @if($canManagePelanggan)
                        <td>
                            <div class="action-row">
                                <button
                                    type="button"
                                    class="icon-btn edit"
                                    title="Edit pelanggan"
                                    data-pelanggan='@json($payload)'
                                    onclick="openEditPelangganModal(this)">
                                    ✎
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('pelanggan.destroy', $item->id) }}"
                                    onsubmit="return confirm('Hapus pelanggan {{ $item->nama }}? Port akan kembali kosong.');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="icon-btn delete" title="Hapus pelanggan">
                                        🗑
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $canManagePelanggan ? 6 : 5 }}" class="empty-row">
                            <div class="empty-state">
                                <div class="empty-state-icon">👤</div>
                                <strong>Belum ada pelanggan</strong>
                                <span>Tambahkan pelanggan dan hubungkan ke Port OUT ODP yang tersedia.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

@if($canManagePelanggan)
<div class="modal-backdrop" id="pelangganModal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <div class="modal-title">Edit Pelanggan</div>
                <div class="modal-subtitle">
                    Ubah data pelanggan atau pindahkan ke Port OUT ODP lain yang masih tersedia.
                </div>
            </div>

            <button type="button" class="modal-close" onclick="closePelangganModal()">✕</button>
        </div>

        <div class="modal-body">
            <form class="pelanggan-form" method="POST" id="editPelangganForm">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="field">
                        <label>ODP</label>
                        <select name="odp_id" id="edit_odp_id" required>
                            <option value="">Pilih ODP</option>
                            @foreach($listOdp as $odp)
                            <option value="{{ $odp->id }}">{{ $odp->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Port OUT ODP</label>
                        <select name="port_id" id="edit_port_id" required>
                            <option value="">Pilih ODP dulu</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>Nama Pelanggan</label>
                        <input type="text" name="nama" id="edit_nama" required>
                    </div>

                    <div class="field">
                        <label>No. Layanan</label>
                        <input type="text" name="no_layanan" id="edit_no_layanan">
                    </div>

                    <div class="field">
                        <label>Latitude</label>
                        <input type="number" step="any" name="lat" id="edit_lat">
                    </div>

                    <div class="field">
                        <label>Longitude</label>
                        <input type="number" step="any" name="lng" id="edit_lng">
                    </div>

                    <div class="field full">
                        <label>Alamat</label>
                        <textarea name="alamat" id="edit_alamat"></textarea>
                    </div>

                    <div class="field full">
                        <label>Keterangan</label>
                        <textarea name="keterangan" id="edit_keterangan"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-modern-secondary" onclick="closePelangganModal()">
                        Batal
                    </button>

                    <button type="submit" class="btn-modern-primary">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
    const availableOdpPorts = @json($availableOdpPorts);
    const pelangganBaseUrl = "{{ url('/pelanggan') }}";
    const oldOdpId = @json($oldOdpId);
    const oldPortId = @json($oldPortId);

    function populatePortSelect(odpId, portSelect, selectedPortId = null, currentPort = null) {
        portSelect.innerHTML = '<option value="">Pilih Port OUT</option>';

        if (!odpId) {
            portSelect.innerHTML = '<option value="">Pilih ODP dulu</option>';
            return;
        }

        let ports = availableOdpPorts.filter(function(port) {
            return String(port.odp_id) === String(odpId);
        });

        if (currentPort && String(currentPort.odp_id) === String(odpId)) {
            const alreadyExists = ports.some(function(port) {
                return String(port.id) === String(currentPort.id);
            });

            if (!alreadyExists) {
                ports.unshift(currentPort);
            }
        }

        if (ports.length === 0) {
            portSelect.innerHTML = '<option value="">Tidak ada port kosong</option>';
            return;
        }

        ports.forEach(function(port) {
            const option = document.createElement('option');
            option.value = port.id;
            option.textContent = port.label;

            if (selectedPortId && String(selectedPortId) === String(port.id)) {
                option.selected = true;
            }

            portSelect.appendChild(option);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const formWrap = document.getElementById('pelangganFormWrap');
        const btnToggle = document.getElementById('btnTogglePelangganForm');
        const btnCancel = document.getElementById('btnCancelPelangganForm');

        if (btnToggle && formWrap) {
            btnToggle.addEventListener('click', function() {
                formWrap.classList.toggle('open');
            });
        }

        if (btnCancel && formWrap) {
            btnCancel.addEventListener('click', function() {
                formWrap.classList.remove('open');
            });
        }

        const odpSelect = document.getElementById('odp_id');
        const portSelect = document.getElementById('port_id');

        if (odpSelect && portSelect) {
            odpSelect.addEventListener('change', function() {
                populatePortSelect(odpSelect.value, portSelect);
            });

            if (oldOdpId) {
                populatePortSelect(oldOdpId, portSelect, oldPortId);
            }
        }

        const editOdpSelect = document.getElementById('edit_odp_id');
        const editPortSelect = document.getElementById('edit_port_id');

        if (editOdpSelect && editPortSelect) {
            editOdpSelect.addEventListener('change', function() {
                const currentPortRaw = editPortSelect.getAttribute('data-current-port');
                const currentPort = currentPortRaw ? JSON.parse(currentPortRaw) : null;

                populatePortSelect(editOdpSelect.value, editPortSelect, null, currentPort);
            });
        }
    });

    function openEditPelangganModal(button) {
        const raw = button.getAttribute('data-pelanggan');

        if (!raw) {
            return;
        }

        const data = JSON.parse(raw);

        const modal = document.getElementById('pelangganModal');
        const form = document.getElementById('editPelangganForm');

        const odpSelect = document.getElementById('edit_odp_id');
        const portSelect = document.getElementById('edit_port_id');

        form.action = `${pelangganBaseUrl}/${data.id}`;

        document.getElementById('edit_nama').value = data.nama ?? '';
        document.getElementById('edit_no_layanan').value = data.no_layanan ?? '';
        document.getElementById('edit_alamat').value = data.alamat ?? '';
        document.getElementById('edit_lat').value = data.lat ?? '';
        document.getElementById('edit_lng').value = data.lng ?? '';
        document.getElementById('edit_keterangan').value = data.keterangan ?? '';

        odpSelect.value = data.odp_id ?? '';

        if (data.current_port) {
            portSelect.setAttribute('data-current-port', JSON.stringify(data.current_port));
        } else {
            portSelect.removeAttribute('data-current-port');
        }

        populatePortSelect(data.odp_id, portSelect, data.port_id, data.current_port);

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closePelangganModal() {
        const modal = document.getElementById('pelangganModal');

        if (!modal) {
            return;
        }

        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePelangganModal();
        }
    });

    document.addEventListener('click', function(event) {
        const modal = document.getElementById('pelangganModal');

        if (!modal || !modal.classList.contains('open')) {
            return;
        }

        if (event.target === modal) {
            closePelangganModal();
        }
    });
</script>

@endsection