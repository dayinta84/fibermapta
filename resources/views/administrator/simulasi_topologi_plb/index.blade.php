@extends('layouts.dashboard')

@section('title', 'Simulasi Topologi PLB')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/simulasi-topologi-plb.css') }}">
@endpush

@section('content')
@php
$role = auth()->user()->role ?? null;
$canManagePlb = in_array($role, ['superadmin', 'admin']);
@endphp

<div class="topology-page">
    <div class="topology-header">
        <div>
            <h1>Simulasi Topologi PLB</h1>
            <p>
                @if($canManagePlb)
                Rancang topologi jaringan FTTH secara visual dan hitung Power Link Budget semua endpoint.
                @else
                Lihat hasil simulasi Power Link Budget jaringan FTTH yang telah dibuat oleh admin.
                @endif
            </p>
        </div>

        @if($canManagePlb)
        <button class="btn-red" onclick="openCreateModal()">
            + Buat Simulasi
        </button>
        @endif
    </div>

    @if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="network-grid">
        @forelse($simulasi as $item)
        @php
        $nodes = $item->nodes ?? [];
        $hasil = $item->hasil ?? [];
        $totalNode = count($nodes);
        $totalEndpoint = collect($nodes)->where('type', 'ONU')->count();
        $layak = collect($hasil)->where('status', 'layak')->count();
        $tidakLayak = collect($hasil)->where('status', 'tidak_layak')->count();
        @endphp

        <div class="network-card">
            <div class="network-card-top">
                <div>
                    <h3>{{ $item->nama }}</h3>
                    <p>{{ $item->catatan ?: 'Tidak ada catatan' }}</p>
                </div>

                @if($canManagePlb)
                <form action="{{ route('simulasi-topologi-plb.destroy', $item->id) }}"
                    method="POST"
                    onsubmit="return confirm('Hapus simulasi ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn-icon-danger" type="submit" title="Hapus simulasi">×</button>
                </form>
                @endif
            </div>

            <div class="network-meta">
                <div><strong>{{ $totalNode }}</strong> Komponen</div>
                <div><strong>{{ $totalEndpoint }}</strong> Endpoint</div>
                <div>OLT Power: <strong>{{ $item->ptx }} dBm</strong></div>
                <div>Safety Margin: <strong>{{ $item->safety_margin }} dB</strong></div>
                <div>Updated: {{ $item->updated_at->format('d M Y H:i') }}</div>
            </div>

            @if(count($hasil) > 0)
            <div class="network-status">
                <span class="badge-ok">{{ $layak }} Layak</span>
                <span class="badge-bad">{{ $tidakLayak }} Tidak Layak</span>
            </div>
            @else
            <div class="network-status">
                <span class="badge-muted">Belum dihitung</span>
            </div>
            @endif

            <a href="{{ route('simulasi-topologi-plb.show', $item->id) }}" class="btn-open">
                {{ $canManagePlb ? 'Buka Whiteboard' : 'Lihat Simulasi' }}
            </a>
        </div>
        @empty
        <div class="empty-state">
            <h3>Belum ada simulasi topologi</h3>
            @if($canManagePlb)
            <p>Klik tombol “Buat Simulasi” untuk mulai membuat rancangan jaringan.</p>
            @else
            <p>Belum ada simulasi yang dapat dilihat. Hubungi admin untuk membuat simulasi terlebih dahulu.</p>
            @endif
        </div>
        @endforelse
    </div>
</div>

@if($canManagePlb)
<div id="createModal" class="modal-backdrop" style="display:none;">
    <div class="modal-box">
        <h2>Simulasi Baru</h2>

        <form action="{{ route('simulasi-topologi-plb.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Nama Simulasi</label>
                <input type="text" name="nama" required placeholder="Contoh: Area Perumahan A">
            </div>

            <div class="form-group">
                <label>Catatan</label>
                <textarea name="catatan" placeholder="Contoh: Skenario jaringan FTTH area perumahan"></textarea>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>OLT Power Default (dBm)</label>
                    <input type="number" step="0.01" name="ptx" value="9" required>
                </div>

                <div class="form-group">
                    <label>Threshold Rx (dBm)</label>
                    <input type="number" step="0.01" name="threshold_rx" value="-28" required>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeCreateModal()">Batal</button>
                <button type="submit" class="btn-red">Buat</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createModal').style.display = 'flex';
    }

    function closeCreateModal() {
        document.getElementById('createModal').style.display = 'none';
    }
</script>
@endif

@endsection