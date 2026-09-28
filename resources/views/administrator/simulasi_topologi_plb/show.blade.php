@extends('layouts.dashboard')

@section('title', 'Whiteboard Simulasi Topologi PLB')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/simulasi-topologi-plb.css') }}">
@endpush

@section('content')
@php
$role = auth()->user()->role ?? null;
$canManagePlb = in_array($role, ['superadmin', 'admin']);
@endphp

<div class="topology-workspace">
    <div class="workspace-header">
        <div>
            <a href="{{ route('simulasi-topologi-plb.index') }}" class="back-link">← Kembali</a>
            <h1>{{ $simulasi->nama }}</h1>
            <p>{{ $simulasi->catatan ?: 'Tidak ada catatan simulasi' }}</p>
        </div>

        @if($canManagePlb)
        <div class="header-actions">
            <button class="btn-secondary" onclick="openSettingsModal()">⚙ Parameter</button>
            <button class="btn-secondary" onclick="saveTopology()">Simpan</button>
            <button class="btn-red" onclick="calculateTopology()">Hitung</button>
        </div>
        @else
        <div class="header-actions">
            <span class="badge-muted">Mode Teknisi: Lihat Hasil</span>
        </div>
        @endif
    </div>

    <div class="workspace-body">
        <aside class="component-sidebar">
            @if($canManagePlb)
            <h3>Komponen</h3>

            <button onclick="addNode('OLT')">+ OLT</button>
            <button onclick="addNode('ODC')">+ ODC</button>
            <button onclick="addNode('ODP')">+ ODP</button>
            <button onclick="addNode('Splitter')">+ Splitter</button>
            <button onclick="addNode('Splice')">+ Splice</button>
            <button onclick="addNode('ONU')">+ Pelanggan</button>

            <hr>

            <div class="hint-box">
                <strong>Cara pakai:</strong>
                <p>1. Tambahkan komponen.</p>
                <p>2. Klik node sumber.</p>
                <p>3. Klik “Mulai Koneksi”.</p>
                <p>4. Klik node tujuan.</p>
                <p>5. Isi panjang kabel pada garis.</p>
                <p>6. Klik Simpan atau Hitung.</p>
            </div>
            @else
            <h3>Mode Teknisi</h3>
            <div class="hint-box">
                <strong>Akses lihat hasil</strong>
                <p>Teknisi dapat melihat topologi, detail komponen, dan hasil simulasi PLB.</p>
                <p>Teknisi tidak dapat menambah, mengubah, menghapus, menyimpan, atau menghitung ulang topologi.</p>
            </div>
            @endif
        </aside>

        <main class="canvas-wrapper">
            <div id="canvas" class="topology-canvas">
                <svg id="edgeLayer"></svg>
                <div id="nodeLayer"></div>
            </div>
        </main>

        <aside class="property-panel">
            <h3>{{ $canManagePlb ? 'Properti' : 'Detail Komponen' }}</h3>
            <div id="propertyContent">
                @if($canManagePlb)
                <p class="muted">Klik node atau garis untuk mengatur parameter.</p>
                @else
                <p class="muted">Klik node atau garis untuk melihat detail komponen. Teknisi tidak dapat mengubah topologi simulasi.</p>
                @endif
            </div>
        </aside>
    </div>

    <div class="result-panel">
        <h3>Hasil Perhitungan Endpoint</h3>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Endpoint</th>
                        <th>Jalur</th>
                        <th>Total Panjang</th>
                        <th>Total Loss</th>
                        <th>Safety Margin</th>
                        <th>Prx</th>
                        <th>Margin</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="resultTable">
                    @forelse(($simulasi->hasil ?? []) as $row)
                    <tr>
                        <td>{{ $row['endpoint'] }}</td>
                        <td>{{ $row['jalur'] }}</td>
                        <td>{{ $row['total_panjang_km'] }} km</td>
                        <td>{{ $row['total_loss'] }} dB</td>
                        <td>{{ $row['safety_margin'] ?? $simulasi->safety_margin }} dB</td>
                        <td>{{ $row['prx'] }} dBm</td>
                        <td>{{ $row['margin'] }} dB</td>
                        <td>
                            <span class="{{ $row['status'] === 'layak' ? 'badge-ok' : 'badge-bad' }}">
                                {{ $row['status'] === 'layak' ? 'Layak' : 'Tidak Layak' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="muted">
                            {{ $canManagePlb ? 'Belum ada hasil. Klik Hitung PLB.' : 'Belum ada hasil simulasi yang dapat dilihat.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($canManagePlb)
<div id="settingsModal" class="modal-backdrop" style="display:none;">
    <div class="modal-box large">
        <h2>Pengaturan Simulasi</h2>

        <form action="{{ route('simulasi-topologi-plb.update-settings', $simulasi->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-grid-3">
                <div class="form-group">
                    <label>OLT Power Default (dBm)</label>
                    <input type="number" step="0.001" name="ptx" value="{{ $simulasi->ptx }}">
                </div>

                <div class="form-group">
                    <label>Redaman Serat Default (dB/km)</label>
                    <input type="number" step="0.001" name="alpha_serat" value="{{ $simulasi->alpha_serat }}">
                </div>

                <div class="form-group">
                    <label>Loss Konektor Default (dB)</label>
                    <input type="number" step="0.001" name="loss_konektor" value="{{ $simulasi->loss_konektor }}">
                </div>

                <div class="form-group">
                    <label>Loss Splice Default (dB)</label>
                    <input type="number" step="0.001" name="loss_splice" value="{{ $simulasi->loss_splice }}">
                </div>

                <div class="form-group">
                    <label>Safety Margin (dB)</label>
                    <input type="number" step="0.001" name="safety_margin" value="{{ $simulasi->safety_margin }}">
                </div>

                <div class="form-group">
                    <label>Threshold Rx (dBm)</label>
                    <input type="number" step="0.001" name="threshold_rx" value="{{ $simulasi->threshold_rx }}">
                </div>
            </div>

            <h3>Loss Splitter Default</h3>
            <p class="muted">Rasio diseragamkan dengan data jaringan utama: 1:4, 1:8, dan 1:16.</p>

            @php
            $losses = $simulasi->splitter_losses ?? [];
            $splitterRatios = ['1:4' => 7.3, '1:8' => 10.5, '1:16' => 13.8];
            @endphp

            <div class="form-grid-3">
                @foreach($splitterRatios as $ratio => $defaultLoss)
                <div class="form-group">
                    <label>Splitter {{ $ratio }}</label>
                    <input type="number"
                        step="0.001"
                        name="splitter_losses[{{ $ratio }}]"
                        value="{{ $losses[$ratio] ?? $defaultLoss }}">
                </div>
                @endforeach
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeSettingsModal()">Batal</button>
                <button type="submit" class="btn-red">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    window.TOPOLOGY_CONFIG = {
        csrf: "{{ csrf_token() }}",
        canManage: @json($canManagePlb),
        saveUrl: @json($canManagePlb ? route('simulasi-topologi-plb.update-topology', $simulasi->id) : null),
        hitungUrl: @json($canManagePlb ? route('simulasi-topologi-plb.hitung', $simulasi->id) : null),
        nodes: @json($simulasi->nodes ?? []),
        edges: @json($simulasi->edges ?? []),
        defaults: {
            ptx: {{$simulasi->ptx}},
            alpha_serat: {{$simulasi->alpha_serat}},
            loss_konektor: {{$simulasi->loss_konektor}},
            loss_splice: {{$simulasi->loss_splice}},
            safety_margin: {{$simulasi->safety_margin}},
            threshold_rx: {{$simulasi->threshold_rx}},
            splitter_losses: @json($simulasi->splitter_losses ?? ['1:4' => 7.3, '1:8' => 10.5, '1:16' => 13.8])
        },
        allowedSplitterRatios: ['1:4', '1:8', '1:16']
    };

    function openSettingsModal() {
        const modal = document.getElementById('settingsModal');
        if (modal) modal.style.display = 'flex';
    }

    function closeSettingsModal() {
        const modal = document.getElementById('settingsModal');
        if (modal) modal.style.display = 'none';
    }
</script>

@push('scripts')
<script src="{{ asset('assets/js/simulasi-topologi-plb.js') }}"></script>
@endpush

@endsection