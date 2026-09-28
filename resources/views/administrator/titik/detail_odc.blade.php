@extends('layouts.dashboard')

@section('title', 'Detail ODC')
@section('topbar_title', 'Detail ODC')
@section('topbar_subtitle', 'Port distribusi ODC ke ODP atau cabang jaringan')

@section('content')

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

<style>
    .odc-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
        color: #0f172a;
    }

    .odc-alert {
        padding: 13px 15px;
        border-radius: 16px;
        font-size: 14px;
        border: 1px solid transparent;
    }

    .odc-alert.success {
        color: #166534;
        background: #dcfce7;
        border-color: #bbf7d0;
    }

    .odc-alert.error {
        color: #991b1b;
        background: #fee2e2;
        border-color: #fecaca;
    }

    .odc-alert ul {
        margin: 8px 0 0 18px;
    }

    .odc-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
    }

    .odc-hero {
        padding: 22px 24px;
    }

    .odc-section {
        padding: 22px 24px 26px;
    }

    .odc-hero-main {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 20px;
        align-items: flex-start;
    }

    .odc-kicker {
        display: inline-flex;
        align-items: center;
        margin-bottom: 9px;
        padding: 6px 12px;
        border-radius: 999px;
        color: #b45309;
        background: #fef3c7;
        font-size: 12px;
        font-weight: 900;
    }

    .odc-title {
        margin: 0;
        color: #020617;
        font-size: 32px;
        line-height: 1.05;
        font-weight: 950;
        letter-spacing: -0.7px;
    }

    .odc-subtitle,
    .section-subtitle {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.55;
    }

    .odc-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-modern-primary,
    .btn-modern-secondary {
        min-height: 40px;
        border-radius: 14px;
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        transition: 0.16s ease;
        white-space: nowrap;
    }

    .btn-modern-primary {
        border: 0;
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb, #10b981);
        box-shadow: 0 14px 28px rgba(37, 99, 235, 0.22);
    }

    .btn-modern-secondary {
        color: #334155;
        background: #ffffff;
        border: 1px solid #e5e7eb;
    }

    .btn-modern-primary:hover,
    .btn-modern-secondary:hover {
        transform: translateY(-1px);
    }

    .odc-summary {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 11px;
        margin-top: 20px;
    }

    .odc-summary-card {
        padding: 12px 13px;
        border-radius: 17px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
    }

    .odc-summary-card span {
        display: block;
        margin-bottom: 5px;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
    }

    .odc-summary-card strong {
        display: block;
        color: #020617;
        font-size: 20px;
        line-height: 1.1;
        font-weight: 950;
        word-break: break-word;
    }

    .route-mini {
        margin-top: 16px;
        padding: 13px 14px;
        border-radius: 18px;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border: 1px solid #e5e7eb;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        align-items: center;
        gap: 14px;
    }

    .route-mini-head {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 190px;
    }

    .route-mini-icon {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 900;
    }

    .route-mini-title h2 {
        margin: 0;
        color: #020617;
        font-size: 15px;
        font-weight: 950;
    }

    .route-mini-title p {
        margin: 2px 0 0;
        color: #64748b;
        font-size: 12px;
    }

    .route-mini-data {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto;
        gap: 9px;
        align-items: center;
    }

    .route-mini-item {
        padding: 9px 10px;
        border-radius: 14px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        min-width: 0;
    }

    .route-mini-item span {
        display: block;
        margin-bottom: 2px;
        color: #64748b;
        font-size: 11px;
        font-weight: 900;
    }

    .route-mini-item strong {
        display: block;
        color: #020617;
        font-size: 14px;
        font-weight: 900;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .route-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 12px;
        border-radius: 999px;
        color: #1d4ed8;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .section-title-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .section-title-row h2 {
        margin: 0;
        color: #020617;
        font-size: 22px;
        font-weight: 950;
    }

    .port-odc-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        align-items: start;
    }

    .port-card {
        position: relative;
        padding: 15px;
        border-radius: 21px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
        overflow: visible;
        z-index: 1;
    }

    .port-card:has(.redaman-popover.open) {
        z-index: 80;
    }

    .port-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 4px;
        border-radius: 21px 21px 0 0;
    }

    .port-card.port-in::before {
        background: linear-gradient(90deg, #0f172a, #64748b);
    }

    .port-card.used::before {
        background: linear-gradient(90deg, #2563eb, #60a5fa);
    }

    .port-card.empty::before {
        background: linear-gradient(90deg, #16a34a, #86efac);
    }

    .port-card-head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 13px;
    }

    .port-icon {
        width: 42px;
        height: 42px;
        border-radius: 15px;
        position: relative;
        flex: 0 0 auto;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }

    .port-icon::before {
        content: "";
        position: absolute;
        left: 8px;
        top: 8px;
        width: 25px;
        height: 21px;
        border-radius: 6px 6px 3px 3px;
        background: #334155;
    }

    .port-icon::after {
        content: "";
        position: absolute;
        left: 12px;
        top: 28px;
        width: 18px;
        height: 5px;
        background:
            linear-gradient(90deg,
                #0f172a 0 2px,
                transparent 2px 4px,
                #0f172a 4px 6px,
                transparent 6px 8px,
                #0f172a 8px 10px,
                transparent 10px 12px,
                #0f172a 12px 14px,
                transparent 14px 16px,
                #0f172a 16px 18px);
    }

    .port-icon.in {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .port-icon.in::before {
        background: #0f172a;
    }

    .port-icon.used {
        background: #dbeafe;
        border-color: #93c5fd;
    }

    .port-icon.used::before {
        background: #2563eb;
    }

    .port-icon.empty {
        background: #dcfce7;
        border-color: #86efac;
    }

    .port-icon.empty::before {
        background: #16a34a;
    }

    .port-title {
        min-width: 0;
        flex: 1;
    }

    .port-title h3 {
        margin: 0;
        color: #020617;
        font-size: 17px;
        font-weight: 950;
        line-height: 1.2;
    }

    .port-title p {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 9px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .status-in {
        color: #334155;
        background: #e2e8f0;
    }

    .status-used {
        color: #1d4ed8;
        background: #dbeafe;
    }

    .status-empty {
        color: #166534;
        background: #dcfce7;
    }

    .port-info-list {
        display: grid;
        gap: 8px;
    }

    .port-info-box {
        padding: 10px 11px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
    }

    .port-info-box span {
        display: block;
        margin-bottom: 4px;
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
    }

    .port-info-box strong {
        display: block;
        color: #020617;
        font-size: 14px;
        line-height: 1.25;
        font-weight: 900;
        word-break: break-word;
    }

    .port-empty-note {
        min-height: 124px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px;
        border-radius: 16px;
        color: #64748b;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        font-size: 13px;
        line-height: 1.45;
        text-align: center;
    }

    .icon-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
    }

    .icon-actions.center {
        justify-content: center;
    }

    .icon-btn,
    .btn-redaman-icon {
        width: 38px;
        height: 38px;
        border-radius: 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        font-weight: 900;
        line-height: 1;
        transition: 0.16s ease;
        text-decoration: none;
        border: 1px solid transparent;
    }

    .icon-btn:hover,
    .btn-redaman-icon:hover {
        transform: translateY(-1px);
    }

    .icon-btn.add {
        color: #ffffff;
        background: #2563eb;
        border-color: #2563eb;
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

    .btn-redaman-icon {
        color: #1d4ed8;
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .redaman-wrapper {
        position: relative;
        display: inline-block;
        margin-top: 12px;
    }

    .redaman-popover {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        z-index: 999;
        display: none;
        width: 320px;
        padding: 15px;
        border-radius: 20px;
        background: #f8fafc;
        border: 1px solid #dbe3ef;
        box-shadow: 0 22px 55px rgba(15, 23, 42, 0.22);
    }

    .redaman-popover.open {
        display: block;
    }

    .redaman-popover-right {
        left: auto;
        right: 0;
    }

    .redaman-form-title {
        margin: 0 0 12px;
        color: #0f172a;
        font-size: 14px;
        font-weight: 950;
        text-align: center;
    }

    .redaman-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        margin-bottom: 12px;
    }

    .redaman-field label {
        color: #0f172a;
        font-size: 13px;
        font-weight: 900;
    }

    .redaman-field input,
    .redaman-field textarea {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 13px;
        padding: 11px 12px;
        background: #ffffff;
        color: #0f172a;
        font-size: 14px;
        outline: none;
    }

    .redaman-field input:focus,
    .redaman-field textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .redaman-field input:disabled {
        background: #f1f5f9;
        color: #64748b;
        cursor: not-allowed;
    }

    .redaman-field textarea {
        min-height: 82px;
        resize: vertical;
    }

    .btn-redaman-save {
        width: 100%;
        border: 0;
        border-radius: 15px;
        padding: 12px 14px;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #ffffff;
        font-size: 15px;
        font-weight: 950;
        cursor: pointer;
        transition: 0.16s ease;
    }

    .btn-redaman-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
    }

    .empty-note {
        margin-top: 14px;
        color: #64748b;
        font-size: 14px;
        line-height: 1.5;
    }

    @media (max-width: 1250px) {
        .port-odc-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .route-mini {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 1100px) {
        .odc-hero-main {
            grid-template-columns: 1fr;
        }

        .odc-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .route-mini-data {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .route-badge {
            grid-column: 1 / -1;
            justify-self: flex-start;
        }
    }

    @media (max-width: 900px) {
        .port-odc-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .odc-section,
        .odc-hero {
            padding: 18px;
        }
    }

    @media (max-width: 650px) {

        .odc-summary,
        .port-odc-grid,
        .route-mini-data {
            grid-template-columns: 1fr;
        }

        .odc-actions,
        .odc-actions .btn-modern-secondary {
            width: 100%;
        }

        .odc-title {
            font-size: 30px;
        }
    }

    @media (max-width: 700px) {

        .redaman-popover,
        .redaman-popover-right {
            position: fixed;
            left: 16px;
            right: 16px;
            top: 50%;
            width: auto;
            transform: translateY(-50%);
        }
    }
</style>

<div class="odc-page">

    @if(session('success'))
    <div class="odc-alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="odc-alert error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="odc-alert error">
        <strong>Periksa kembali input berikut:</strong>
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <section class="odc-card odc-hero">
        <div class="odc-hero-main">
            <div>
                <div class="odc-kicker">ODC / Optical Distribution Cabinet</div>

                <h1 class="odc-title">{{ $titik->nama }}</h1>

                <p class="odc-subtitle">
                    ODC menerima koneksi dari OLT melalui Port IN, kemudian membagi jaringan ke ODP atau cabang jaringan melalui Port OUT.
                </p>
            </div>

            <div class="odc-actions">
                <a href="{{ route('titik.index') }}" class="btn-modern-secondary">
                    ← Kembali
                </a>
            </div>
        </div>

        <div class="odc-summary">
            <div class="odc-summary-card">
                <span>Parent / Sumber</span>
                <strong>{{ $titik->parent?->nama ?? '-' }}</strong>
            </div>

            <div class="odc-summary-card">
                <span>Rasio Splitter</span>
                <strong>{{ $titik->rasio_splitter ?? '-' }}</strong>
            </div>

            <div class="odc-summary-card">
                <span>Kapasitas Port</span>
                <strong>{{ $kapasitasPort ?? '-' }}</strong>
            </div>

            <div class="odc-summary-card">
                <span>Port Digunakan</span>
                <strong>{{ $jumlahTerpakai }}</strong>
            </div>

            <div class="odc-summary-card">
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
                    <p>Parent menuju ODC</p>
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
                    {{ $titik->jenis_jalur ?? 'Feeder' }}
                </span>
            </div>
        </div>
        @endif
    </section>

    <section class="odc-card odc-section">
        <div class="section-title-row">
            <div>
                <h2>Port ODC</h2>
                <p class="section-subtitle">
                    Port IN digunakan sebagai jalur masuk dari OLT, sedangkan Port OUT digunakan sebagai jalur distribusi menuju ODP atau cabang jaringan.
                </p>
            </div>
        </div>

        <div class="port-odc-grid">

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
                        <strong>{{ $portIn->keterangan_redaman ?: 'Port masuk dari OLT' }}</strong>
                    </div>
                </div>

                @if($canUpdateRedaman)
                <div class="redaman-wrapper">
                    <button
                        type="button"
                        class="btn-redaman-icon"
                        title="Atur redaman"
                        onclick="toggleRedamanPopover('redaman-odc-in-{{ $portIn->id }}')">
                        ⚙
                    </button>

                    <div id="redaman-odc-in-{{ $portIn->id }}" class="redaman-popover">
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
                                    placeholder="Contoh: {{ $titik->parent?->nama ?? 'OLT' }}">
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
                    Port IN belum tersedia. Port akan dibuat otomatis saat data ODC disimpan atau rasio splitter diperbarui oleh admin.
                </div>
            </article>
            @endif

            @if($portOut->count())
            @foreach($portOut as $port)
            @php
            $connectedTitik = $port->connectedTitik;
            $isUsed = $port->status === 'terpakai' || $connectedTitik !== null;
            @endphp

            <article class="port-card {{ $isUsed ? 'used' : 'empty' }}">
                <div class="port-card-head">
                    <div class="port-icon {{ $isUsed ? 'used' : 'empty' }}"></div>

                    <div class="port-title">
                        <h3>Port {{ $port->nomor_port }}</h3>
                        <p>Jalur distribusi</p>
                    </div>

                    @if($isUsed)
                    <span class="status-pill status-used">Terpakai</span>
                    @else
                    <span class="status-pill status-empty">Kosong</span>
                    @endif
                </div>

                @if($connectedTitik)
                <div class="port-info-list">
                    <div class="port-info-box">
                        <span>Terhubung ke</span>
                        <strong>{{ $connectedTitik->nama }}</strong>
                    </div>

                    <div class="port-info-box">
                        <span>Jenis Tujuan</span>
                        <strong>{{ strtoupper($connectedTitik->jenis) }}</strong>
                    </div>

                    <div class="port-info-box">
                        <span>Redaman Manual</span>
                        <strong>
                            {{ $port->redaman_manual !== null ? number_format($port->redaman_manual, 2) . ' dB' : '-' }}
                        </strong>
                    </div>

                    @if($port->keterangan_redaman)
                    <div class="port-info-box">
                        <span>Keterangan</span>
                        <strong>{{ $port->keterangan_redaman }}</strong>
                    </div>
                    @endif
                </div>
                @else
                <div class="port-empty-note">
                    Port ini masih kosong dan bisa dipilih ketika admin membuat ODP baru.
                </div>
                @endif

                @if($canUpdateRedaman)
                <div class="redaman-wrapper">
                    <button
                        type="button"
                        class="btn-redaman-icon"
                        title="Atur redaman"
                        onclick="toggleRedamanPopover('redaman-odc-out-{{ $port->id }}')">
                        ⚙
                    </button>

                    <div id="redaman-odc-out-{{ $port->id }}" class="redaman-popover redaman-popover-right">
                        <form method="POST" action="{{ route('ports.update_redaman', $port->id) }}">
                            @csrf
                            @method('PUT')

                            <h3 class="redaman-form-title">Atur Redaman Port {{ $port->nomor_port }}</h3>

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
            </article>
            @endforeach
            @endif
        </div>

        @if(!$portIn && !$portOut->count())
        <p class="empty-note">
            Belum ada port pada ODC ini. Port akan dibuat otomatis saat data ODC disimpan atau rasio splitter diperbarui oleh admin.
        </p>
        @endif
    </section>
</div>

<script>
    function toggleRedamanPopover(id) {
        const target = document.getElementById(id);

        if (!target) {
            return;
        }

        document.querySelectorAll('.redaman-popover.open').forEach(function(popover) {
            if (popover.id !== id) {
                popover.classList.remove('open');
            }
        });

        target.classList.toggle('open');
    }

    function closeRedamanPopovers() {
        document.querySelectorAll('.redaman-popover.open').forEach(function(popover) {
            popover.classList.remove('open');
        });
    }

    document.addEventListener('click', function(event) {
        const isButton = event.target.closest('.btn-redaman-icon');
        const isPopover = event.target.closest('.redaman-popover');

        if (!isButton && !isPopover) {
            closeRedamanPopovers();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeRedamanPopovers();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const redamanInputs = document.querySelectorAll('input[name="redaman_manual"]');

        redamanInputs.forEach(function(input) {
            input.addEventListener('input', function() {
                const value = Number(input.value);

                if (input.value === '') {
                    input.setCustomValidity('');
                    return;
                }

                if (Number.isNaN(value)) {
                    input.setCustomValidity('Redaman manual harus berupa angka.');
                    return;
                }

                if (value < 0) {
                    input.setCustomValidity('Redaman manual tidak boleh kurang dari 0.');
                    return;
                }

                if (value > 100) {
                    input.setCustomValidity('Redaman manual terlalu besar. Maksimal 100 dB.');
                    return;
                }

                input.setCustomValidity('');
            });
        });
    });
</script>

@endsection