@extends('layouts.dashboard')

@section('title', 'Detail ODP')
@section('topbar_title', 'Detail ODP')
@section('topbar_subtitle', 'Port distribusi akhir ODP ke pelanggan')

@section('content')

<link rel="stylesheet" href="{{ asset('assets/css/detail-odp.css') }}?v={{ time() }}">

@php
$role = auth()->user()->role ?? null;

$canManage = in_array($role, ['superadmin', 'admin']);
$canUpdateRedaman = in_array($role, ['superadmin', 'admin', 'teknisi']);

$formatMeter = function ($meter) {
if ($meter === null || $meter === '') {
return '-';
}

if ($meter >= 1000) {
return number_format($meter / 1000, 2) . ' km';
}

return number_format($meter, 2) . ' m';
};
@endphp

<div class="odp-page">

    @if(session('success'))
    <div class="odp-alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="odp-alert error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="odp-alert error">
        <strong>Periksa kembali input berikut:</strong>
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <section class="odp-card odp-hero">
        <div class="odp-hero-main">
            <div>
                <div class="odp-kicker">ODP / Optical Distribution Point</div>

                <h1 class="odp-title">{{ $titik->nama }}</h1>

                <p class="odp-subtitle">
                    ODP menerima koneksi dari ODC melalui Port IN, lalu membagi layanan ke pelanggan melalui Port OUT.
                </p>
            </div>

            <div class="odp-actions">
                <a href="{{ route('titik.index') }}" class="btn-modern-secondary">
                    ← Kembali
                </a>
            </div>
        </div>

        <div class="odp-summary">
            <div class="odp-summary-card">
                <span>Parent / ODC</span>
                <strong>{{ $titik->parent?->nama ?? '-' }}</strong>
            </div>

            <div class="odp-summary-card">
                <span>Port Sumber ODC</span>
                <strong>
                    @if($sourcePort)
                    OUT {{ $sourcePort->nomor_port }}
                    @else
                    -
                    @endif
                </strong>
            </div>

            <div class="odp-summary-card">
                <span>Rasio Splitter</span>
                <strong>{{ $titik->rasio_splitter ?? '-' }}</strong>
            </div>

            <div class="odp-summary-card">
                <span>Port Digunakan</span>
                <strong>{{ $jumlahTerpakai }}</strong>
            </div>

            <div class="odp-summary-card">
                <span>Port Tersedia</span>
                <strong>{{ $jumlahKosong }}</strong>
            </div>
        </div>

        @if($titik->parent)
        <div class="route-mini">
            <div class="route-mini-head">
                <div class="route-mini-icon">↳</div>

                <div class="route-mini-title">
                    <h2>Jalur Masuk</h2>
                    <p>ODC menuju ODP</p>
                </div>
            </div>

            <div class="route-mini-data">
                <div class="route-mini-item">
                    <span>Dari</span>
                    <strong>{{ $titik->parent?->nama ?? '-' }}</strong>
                </div>

                <div class="route-mini-item">
                    <span>Ke</span>
                    <strong>{{ $titik->nama }}</strong>
                </div>

                <div class="route-mini-item">
                    <span>Panjang Kabel</span>
                    <strong>{{ $formatMeter($titik->panjang_kabel_meter) }}</strong>
                </div>

                <span class="route-badge">
                    {{ $titik->jenis_jalur ?? 'Distribusi' }}
                </span>
            </div>
        </div>
        @endif
    </section>

    <section class="odp-card odp-section">
        <div class="section-title-row">
            <div>
                <h2>Port ODP</h2>
                <p class="section-subtitle">
                    Port IN sebagai jalur masuk dari ODC, sedangkan Port OUT digunakan sebagai jalur layanan ke pelanggan.
                </p>
            </div>
        </div>

        <div class="port-odp-grid">

            @if($portIn)
            <article class="port-card port-in">
                <div class="port-card-head">
                    <div class="port-icon in"></div>

                    <div class="port-title">
                        <h3>Port IN</h3>
                        <p>Jalur masuk</p>
                    </div>

                    <span class="status-pill status-in">
                        {{ ucfirst($portIn->status) }}
                    </span>
                </div>

                <div class="port-info-list">
                    <div class="port-info-box">
                        <span>Sumber</span>
                        <strong>{{ $portIn->sumber_port ?? $titik->parent?->nama ?? '-' }}</strong>
                    </div>

                    <div class="port-info-box">
                        <span>Redaman Manual</span>
                        <strong>
                            {{ $portIn->redaman_manual !== null ? number_format($portIn->redaman_manual, 2) . ' dB' : '-' }}
                        </strong>
                    </div>

                    <div class="port-info-box">
                        <span>Keterangan</span>
                        <strong>{{ $portIn->keterangan_redaman ?: 'Port masuk dari ODC' }}</strong>
                    </div>
                </div>

                @if($canUpdateRedaman)
                <div class="redaman-wrapper">
                    <button
                        type="button"
                        class="btn-redaman-icon"
                        title="Atur redaman"
                        onclick="toggleRedamanPopover('redaman-odp-in-{{ $portIn->id }}')">
                        ⚙
                    </button>

                    <div id="redaman-odp-in-{{ $portIn->id }}" class="redaman-popover">
                        <form method="POST" action="{{ route('ports.update_redaman', $portIn->id) }}">
                            @csrf
                            @method('PUT')

                            <h3 class="redaman-form-title">Atur Redaman Port IN</h3>

                            <div class="redaman-field">
                                <label>Redaman Manual (dB)</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="redaman_manual"
                                    value="{{ old('redaman_manual', $portIn->redaman_manual) }}"
                                    placeholder="Contoh: 0.20">
                            </div>

                            <div class="redaman-field">
                                <label>Sumber Port</label>

                                @if($canManage)
                                <input
                                    type="text"
                                    name="sumber_port"
                                    value="{{ old('sumber_port', $portIn->sumber_port) }}"
                                    placeholder="Contoh: {{ $titik->parent?->nama ?? 'ODC' }}">
                                @else
                                <input
                                    type="text"
                                    value="{{ $portIn->sumber_port ?? $titik->parent?->nama ?? '-' }}"
                                    disabled>
                                @endif
                            </div>

                            <div class="redaman-field">
                                <label>Keterangan</label>
                                <textarea
                                    name="keterangan_redaman"
                                    placeholder="Catatan redaman port">{{ old('keterangan_redaman', $portIn->keterangan_redaman) }}</textarea>
                            </div>

                            <button type="submit" class="btn-redaman-save">
                                Simpan
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </article>
            @else
            <article class="port-card port-in">
                <div class="port-card-head">
                    <div class="port-icon in"></div>

                    <div class="port-title">
                        <h3>Port IN</h3>
                        <p>Belum dibuat</p>
                    </div>

                    <span class="status-pill status-in">Kosong</span>
                </div>

                <div class="port-empty-note">
                    Port IN belum tersedia. Port akan dibuat otomatis saat data ODP disimpan atau rasio splitter diperbarui oleh admin.
                </div>
            </article>
            @endif

            @if($portOut->count())
            @foreach($portOut as $port)
            @php
            $pelanggan = $port->pelanggan;
            $isUsed = $port->status === 'terpakai' || $pelanggan !== null;

            $pelangganPayload = $pelanggan ? [
            'id' => $pelanggan->id,
            'nama' => $pelanggan->nama,
            'no_layanan' => $pelanggan->no_layanan,
            'alamat' => $pelanggan->alamat,
            'lat' => $pelanggan->lat,
            'lng' => $pelanggan->lng,
            'keterangan' => $pelanggan->keterangan,
            'odp_id' => $titik->id,
            'port_id' => $port->id,
            ] : null;

            $portPayload = [
            'odp_id' => $titik->id,
            'odp_nama' => $titik->nama,
            'port_id' => $port->id,
            'nomor_port' => $port->nomor_port,
            ];
            @endphp

            <article class="port-card {{ $isUsed ? 'used' : 'empty' }}">
                <div class="port-card-head">
                    <div class="port-icon {{ $isUsed ? 'used' : 'empty' }}"></div>

                    <div class="port-title">
                        <h3>OUT {{ $port->nomor_port }}</h3>
                        <p>Jalur pelanggan</p>
                    </div>

                    @if($isUsed)
                    <span class="status-pill status-used">Terpakai</span>
                    @else
                    <span class="status-pill status-empty">Kosong</span>
                    @endif
                </div>

                @if($pelanggan)
                <div class="customer-box">
                    <h4>{{ $pelanggan->nama }}</h4>

                    <p>
                        <strong>No. Layanan:</strong>
                        {{ $pelanggan->no_layanan ?: '-' }}
                    </p>

                    <p>
                        <strong>Alamat:</strong>
                        {{ $pelanggan->alamat ?: '-' }}
                    </p>

                    <p>
                        <strong>Koordinat:</strong>
                        @if($pelanggan->lat !== null && $pelanggan->lng !== null)
                        {{ $pelanggan->lat }}, {{ $pelanggan->lng }}
                        @else
                        -
                        @endif
                    </p>

                    @if($pelanggan->keterangan)
                    <p>
                        <strong>Keterangan:</strong>
                        {{ $pelanggan->keterangan }}
                    </p>
                    @endif
                </div>

                <div class="port-info-list">
                    <div class="port-info-box">
                        <span>Redaman Manual</span>
                        <strong>
                            {{ $port->redaman_manual !== null ? number_format($port->redaman_manual, 2) . ' dB' : '-' }}
                        </strong>
                    </div>

                    @if($port->keterangan_redaman)
                    <div class="port-info-box">
                        <span>Keterangan Port</span>
                        <strong>{{ $port->keterangan_redaman }}</strong>
                    </div>
                    @endif
                </div>

                @if($canManage || $canUpdateRedaman)
                <div class="port-actions icon-actions center">

                    @if($canManage)
                    <button
                        type="button"
                        class="icon-btn edit"
                        title="Edit pelanggan"
                        data-pelanggan='@json($pelangganPayload)'
                        onclick="openEditPelangganModal(this)">
                        ✎
                    </button>

                    <form
                        method="POST"
                        action="{{ route('pelanggan.destroy', $pelanggan->id) }}"
                        onsubmit="return confirm('Yakin ingin menghapus pelanggan dari port ini?')">
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="icon-btn delete"
                            title="Hapus pelanggan">
                            🗑
                        </button>
                    </form>
                    @endif

                    @if($canUpdateRedaman)
                    <div class="redaman-wrapper">
                        <button
                            type="button"
                            class="btn-redaman-icon"
                            title="Atur redaman"
                            onclick="toggleRedamanPopover('redaman-odp-out-used-{{ $port->id }}')">
                            ⚙
                        </button>

                        <div id="redaman-odp-out-used-{{ $port->id }}" class="redaman-popover redaman-popover-right">
                            <form method="POST" action="{{ route('ports.update_redaman', $port->id) }}">
                                @csrf
                                @method('PUT')

                                <h3 class="redaman-form-title">Atur Redaman OUT {{ $port->nomor_port }}</h3>

                                <div class="redaman-field">
                                    <label>Redaman Manual (dB)</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        name="redaman_manual"
                                        value="{{ old('redaman_manual', $port->redaman_manual) }}"
                                        placeholder="Contoh: 0.20">
                                </div>

                                <div class="redaman-field">
                                    <label>Sumber Port</label>

                                    @if($canManage)
                                    <input
                                        type="text"
                                        name="sumber_port"
                                        value="{{ old('sumber_port', $port->sumber_port) }}"
                                        placeholder="Contoh: OUT {{ $port->nomor_port }}">
                                    @else
                                    <input
                                        type="text"
                                        value="{{ $port->sumber_port ?? '-' }}"
                                        disabled>
                                    @endif
                                </div>

                                <div class="redaman-field">
                                    <label>Keterangan</label>
                                    <textarea
                                        name="keterangan_redaman"
                                        placeholder="Catatan redaman port">{{ old('keterangan_redaman', $port->keterangan_redaman) }}</textarea>
                                </div>

                                <button type="submit" class="btn-redaman-save">
                                    Simpan
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
                @else
                <div class="empty-port-content">
                    <div class="empty-port-icon">+</div>

                    <p>
                        Port ini masih kosong dan bisa digunakan untuk pelanggan baru.
                    </p>

                    <div class="empty-port-redaman">
                        <span>Redaman Manual</span>
                        <strong>
                            {{ $port->redaman_manual !== null ? number_format($port->redaman_manual, 2) . ' dB' : '-' }}
                        </strong>
                    </div>

                    @if($port->keterangan_redaman)
                    <div class="empty-port-redaman">
                        <span>Keterangan Port</span>
                        <strong>{{ $port->keterangan_redaman }}</strong>
                    </div>
                    @endif

                    @if($canManage || $canUpdateRedaman)
                    <div class="port-actions icon-actions center">

                        @if($canManage)
                        <button
                            type="button"
                            class="icon-btn add"
                            title="Tambah pelanggan"
                            data-port='@json($portPayload)'
                            onclick="openTambahPelangganModal(this)">
                            +
                        </button>
                        @endif

                        @if($canUpdateRedaman)
                        <div class="redaman-wrapper">
                            <button
                                type="button"
                                class="btn-redaman-icon"
                                title="Atur redaman"
                                onclick="toggleRedamanPopover('redaman-odp-out-empty-{{ $port->id }}')">
                                ⚙
                            </button>

                            <div id="redaman-odp-out-empty-{{ $port->id }}" class="redaman-popover redaman-popover-right">
                                <form method="POST" action="{{ route('ports.update_redaman', $port->id) }}">
                                    @csrf
                                    @method('PUT')

                                    <h3 class="redaman-form-title">Atur Redaman OUT {{ $port->nomor_port }}</h3>

                                    <div class="redaman-field">
                                        <label>Redaman Manual (dB)</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="redaman_manual"
                                            value="{{ old('redaman_manual', $port->redaman_manual) }}"
                                            placeholder="Contoh: 0.20">
                                    </div>

                                    <div class="redaman-field">
                                        <label>Sumber Port</label>

                                        @if($canManage)
                                        <input
                                            type="text"
                                            name="sumber_port"
                                            value="{{ old('sumber_port', $port->sumber_port) }}"
                                            placeholder="Contoh: OUT {{ $port->nomor_port }}">
                                        @else
                                        <input
                                            type="text"
                                            value="{{ $port->sumber_port ?? '-' }}"
                                            disabled>
                                        @endif
                                    </div>

                                    <div class="redaman-field">
                                        <label>Keterangan</label>
                                        <textarea
                                            name="keterangan_redaman"
                                            placeholder="Catatan redaman port">{{ old('keterangan_redaman', $port->keterangan_redaman) }}</textarea>
                                    </div>

                                    <button type="submit" class="btn-redaman-save">
                                        Simpan
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
            </article>
            @endforeach
            @endif
        </div>

        @if(!$portIn && !$portOut->count())
        <p class="empty-note">
            Belum ada port pada ODP ini. Port akan dibuat otomatis saat data ODP disimpan atau rasio splitter diperbarui oleh admin.
        </p>
        @endif
    </section>
</div>

@if($canManage)
<div class="modal-backdrop" id="pelangganModal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <div class="modal-title" id="pelangganModalTitle">Tambah Pelanggan</div>
                <div class="modal-subtitle" id="pelangganModalSubtitle">
                    Tambahkan pelanggan ke Port OUT ODP.
                </div>
            </div>

            <button type="button" class="modal-close" onclick="closePelangganModal()">✕</button>
        </div>

        <div class="modal-body">
            <form
                method="POST"
                id="pelangganForm"
                class="pelanggan-form"
                action="{{ route('pelanggan.store') }}"
                data-store-url="{{ route('pelanggan.store') }}"
                data-update-base-url="{{ url('/pelanggan') }}">
                @csrf

                <input type="hidden" name="_method" id="pelangganFormMethod" value="POST">
                <input type="hidden" name="odp_id" id="pelanggan_odp_id" value="{{ $titik->id }}">
                <input type="hidden" name="port_id" id="pelanggan_port_id">

                <div class="locked-port-info">
                    <span>Port Tujuan</span>
                    <strong id="pelanggan_port_label">-</strong>
                </div>

                <div class="form-grid-2">
                    <div class="form-field">
                        <label>Nama Pelanggan</label>
                        <input type="text" name="nama" id="pelanggan_nama" required>
                    </div>

                    <div class="form-field">
                        <label>No. Layanan</label>
                        <input type="text" name="no_layanan" id="pelanggan_no_layanan">
                    </div>
                </div>

                <div class="form-field">
                    <label>Alamat</label>
                    <textarea name="alamat" id="pelanggan_alamat" placeholder="Alamat pelanggan"></textarea>
                </div>

                <div class="form-grid-2">
                    <div class="form-field">
                        <label>Latitude</label>
                        <input type="number" step="any" name="lat" id="pelanggan_lat">
                    </div>

                    <div class="form-field">
                        <label>Longitude</label>
                        <input type="number" step="any" name="lng" id="pelanggan_lng">
                    </div>
                </div>

                <div class="form-field">
                    <label>Keterangan</label>
                    <textarea name="keterangan" id="pelanggan_keterangan" placeholder="Keterangan opsional"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-modern-secondary" onclick="closePelangganModal()">
                        Batal
                    </button>

                    <button type="submit" class="btn-modern-primary">
                        Simpan Pelanggan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script src="{{ asset('assets/js/detail-odp.js') }}?v={{ time() }}"></script>

@endsection