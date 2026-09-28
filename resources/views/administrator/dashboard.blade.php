@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('topbar_title', 'Dashboard WebGIS')
@section('topbar_subtitle', auth()->user()->role === 'teknisi'
? 'Monitoring titik jaringan dan pelanggan FTTH'
: 'Monitoring dan pengelolaan titik jaringan FTTH')

@section('content')

@php
$role = auth()->user()->role ?? null;
$canManageNetwork = in_array($role, ['superadmin', 'admin']);
@endphp

<style>
    .dash-page {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .dash-stats {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
    }

    .dash-stat {
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 18px;
        padding: 16px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
    }

    .dash-stat-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .dash-stat-value {
        font-size: 26px;
        font-weight: 800;
        color: #111827;
        line-height: 1;
    }

    .dash-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .dash-card {
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .08);
        border-radius: 18px;
        padding: 16px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
    }

    .dash-card-title {
        margin: 0 0 6px 0;
        font-size: 18px;
        font-weight: 800;
        color: #111827;
    }

    .dash-card-subtitle {
        margin: 0 0 14px 0;
        font-size: 13px;
        color: #6b7280;
    }

    .dash-map-box {
        overflow: hidden;
        border-radius: 16px;
        border: 1px solid rgba(0, 0, 0, .08);
        min-height: 560px;
        background: #f3f4f6;
    }

    #dashboardMap {
        width: 100%;
        height: 560px;
    }

    .dash-legend {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .dash-legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #374151;
    }

    .dash-legend-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 999px;
        color: #fff;
        font-size: 11px;
        font-weight: 800;
    }

    .dash-empty {
        padding: 24px;
        text-align: center;
        color: #6b7280;
        font-size: 14px;
    }

    @media (max-width: 1100px) {
        .dash-stats {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 900px) {
        .dash-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        #dashboardMap {
            height: 460px;
        }

        .dash-map-box {
            min-height: 460px;
        }
    }
</style>

<div class="dash-page">

    <div class="dash-stats">
        <div class="dash-stat">
            <div class="dash-stat-label">Total Titik</div>
            <div class="dash-stat-value" id="statTotal">0</div>
        </div>

        <div class="dash-stat">
            <div class="dash-stat-label">OLT</div>
            <div class="dash-stat-value" id="statOlt">0</div>
        </div>

        <div class="dash-stat">
            <div class="dash-stat-label">ODC</div>
            <div class="dash-stat-value" id="statOdc">0</div>
        </div>

        <div class="dash-stat">
            <div class="dash-stat-label">ODP</div>
            <div class="dash-stat-value" id="statOdp">0</div>
        </div>

        <div class="dash-stat">
            <div class="dash-stat-label">Pelanggan</div>
            <div class="dash-stat-value" id="statPelanggan">0</div>
        </div>
    </div>

    <div class="dash-grid">
        <div class="dash-card">
            <h3 class="dash-card-title">Peta Jaringan FTTH</h3>
            <p class="dash-card-subtitle">
                @if($canManageNetwork)
                Marker OLT, ODC, ODP, dan pelanggan ditampilkan untuk membantu pengelolaan jaringan FTTH.
                @else
                Marker OLT, ODC, ODP, dan pelanggan ditampilkan untuk membantu teknisi melihat kondisi jaringan di lapangan.
                @endif
            </p>

            <div class="dash-legend">
                <div class="dash-legend-item">
                    <span class="dash-legend-badge" style="background:#7c3aed;">OLT</span>
                    Titik OLT
                </div>

                <div class="dash-legend-item">
                    <span class="dash-legend-badge" style="background:#f59e0b;">ODC</span>
                    Titik ODC
                </div>

                <div class="dash-legend-item">
                    <span class="dash-legend-badge" style="background:#10b981;">ODP</span>
                    Titik ODP
                </div>

                <div class="dash-legend-item">
                    <span class="dash-legend-badge" style="background:#2563eb;">PLG</span>
                    Pelanggan
                </div>
            </div>

            <div class="dash-map-box">
                <div id="dashboardMap"></div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    window.dashboardMap = L.map('dashboardMap').setView([-7.6318794, 111.5239502], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(window.dashboardMap);

    const dashboardRoutes = {
        pelangganIndex: @json(route('pelanggan.index'))
    };

    function getPinColor(jenis) {
        if (jenis === 'OLT') return '#7c3aed';
        if (jenis === 'ODC') return '#f59e0b';
        if (jenis === 'ODP') return '#10b981';
        if (jenis === 'PELANGGAN') return '#2563eb';

        return '#64748b';
    }

    function getPinLabel(jenis) {
        if (jenis === 'OLT') return 'OLT';
        if (jenis === 'ODC') return 'ODC';
        if (jenis === 'ODP') return 'ODP';
        if (jenis === 'PELANGGAN') return 'PLG';

        return '-';
    }

    function getLineColor(jenisJalur, jenisTitik) {
        if (jenisTitik === 'PELANGGAN') {
            return '#2563eb';
        }

        if (jenisJalur === 'Feeder' || jenisTitik === 'ODC') {
            return '#f59e0b';
        }

        if (jenisJalur === 'Distribusi' || jenisTitik === 'ODP') {
            return '#10b981';
        }

        return '#2563eb';
    }

    function getDisplayLatLng(t) {
        let lat = parseFloat(t.lat);
        let lng = parseFloat(t.lng);

        if (t.jenis === 'OLT') {
            lat += 0.00000;
            lng += 0.00000;
        } else if (t.jenis === 'ODC') {
            lat += 0.00008;
            lng += 0.00008;
        } else if (t.jenis === 'ODP') {
            lat -= 0.00008;
            lng -= 0.00008;
        }

        return [lat, lng];
    }

    function getDisplayPelangganLatLng(p) {
        return [parseFloat(p.lat), parseFloat(p.lng)];
    }

    function formatMeter(meter) {
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

    function buildPinIcon(jenis) {
        const color = getPinColor(jenis);
        const label = getPinLabel(jenis);

        return L.divIcon({
            className: '',
            html: `
                <div style="display:flex;flex-direction:column;align-items:center;">
                    <div style="
                        min-width:42px;
                        height:28px;
                        padding:0 8px;
                        border-radius:999px;
                        background:${color};
                        color:#fff;
                        font-size:11px;
                        font-weight:800;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        box-shadow:0 4px 12px rgba(0,0,0,.18);
                        border:2px solid #fff;
                    ">
                        ${label}
                    </div>
                    <div style="
                        width:0;
                        height:0;
                        border-left:7px solid transparent;
                        border-right:7px solid transparent;
                        border-top:12px solid ${color};
                        margin-top:-2px;
                        filter:drop-shadow(0 3px 3px rgba(0,0,0,.18));
                    "></div>
                </div>
            `,
            iconSize: [42, 42],
            iconAnchor: [21, 40],
            popupAnchor: [0, -34]
        });
    }

    function buildPopupContent(t) {
        let popup = `
            <div style="min-width:230px;">
                <div style="font-weight:800;font-size:14px;margin-bottom:8px;">
                    ${escapeHtml(t.nama)}
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Jenis:</b> ${escapeHtml(t.jenis)}
                </div>
        `;

        if (t.parent) {
            popup += `
                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Parent:</b> ${escapeHtml(t.parent.nama)}
                </div>
            `;
        }

        if (t.lat && t.lng) {
            popup += `
                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Koordinat:</b> ${escapeHtml(t.lat)}, ${escapeHtml(t.lng)}
                </div>
            `;
        }

        if (t.jenis === 'OLT') {
            popup += `
                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Tx Power:</b> ${t.tx_power !== null && t.tx_power !== undefined ? escapeHtml(t.tx_power + ' dBm') : '-'}
                </div>

                <div style="font-size:13px;margin-bottom:8px;">
                    <b>Fungsi:</b> Sumber utama jaringan
                </div>
            `;
        }

        if (t.jenis === 'ODC') {
            popup += `
                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Splitter:</b> ${escapeHtml(t.rasio_splitter || '-')}
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Jenis Jalur:</b> ${escapeHtml(t.jenis_jalur || '-')}
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Panjang Kabel:</b> ${escapeHtml(formatMeter(t.panjang_kabel_meter))}
                </div>

                <div style="font-size:13px;margin-bottom:8px;">
                    <b>Fungsi:</b> Distribusi ke ODP
                </div>

                <a href="/titik-jaringan/${t.id}/detail-odc" style="
                    display:inline-block;
                    padding:7px 10px;
                    background:#f59e0b;
                    color:#fff;
                    text-decoration:none;
                    border-radius:8px;
                    font-size:12px;
                    font-weight:700;
                ">Lihat Detail ODC</a>
            `;
        }

        if (t.jenis === 'ODP') {
            popup += `
                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Splitter:</b> ${escapeHtml(t.rasio_splitter || '-')}
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Kapasitas Port:</b> ${escapeHtml(t.kapasitas_port || '-')}
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Jenis Jalur:</b> ${escapeHtml(t.jenis_jalur || '-')}
                </div>

                <div style="font-size:13px;margin-bottom:8px;">
                    <b>Panjang Kabel:</b> ${escapeHtml(formatMeter(t.panjang_kabel_meter))}
                </div>

                <a href="/titik-jaringan/${t.id}/detail-odp" style="
                    display:inline-block;
                    padding:7px 10px;
                    background:#10b981;
                    color:#fff;
                    text-decoration:none;
                    border-radius:8px;
                    font-size:12px;
                    font-weight:700;
                ">Lihat Detail ODP</a>
            `;
        }

        popup += `</div>`;

        return popup;
    }

    function buildPelangganPopup(p) {
        const odpName = p.odp ? p.odp.nama : '-';
        const portNumber = p.port ? 'OUT ' + p.port.nomor_port : '-';

        return `
            <div style="min-width:240px;">
                <div style="font-weight:800;font-size:14px;margin-bottom:8px;">
                    ${escapeHtml(p.nama)}
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Jenis:</b> Pelanggan
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>No. Layanan:</b> ${escapeHtml(p.no_layanan || '-')}
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Terhubung ke ODP:</b> ${escapeHtml(odpName)}
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Port:</b> ${escapeHtml(portNumber)}
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Alamat:</b> ${escapeHtml(p.alamat || '-')}
                </div>

                <div style="font-size:13px;margin-bottom:8px;">
                    <b>Koordinat:</b> ${escapeHtml(p.lat)}, ${escapeHtml(p.lng)}
                </div>

                <a href="${dashboardRoutes.pelangganIndex}" style="
                    display:inline-block;
                    padding:7px 10px;
                    background:#2563eb;
                    color:#fff;
                    text-decoration:none;
                    border-radius:8px;
                    font-size:12px;
                    font-weight:700;
                ">Lihat Data Pelanggan</a>
            </div>
        `;
    }

    function buildRoutePopup(t) {
        return `
            <div style="min-width:230px;">
                <div style="font-weight:800;font-size:14px;margin-bottom:8px;">
                    ${escapeHtml(t.parent?.nama || '-')} → ${escapeHtml(t.nama)}
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Jenis Jalur:</b> ${escapeHtml(t.jenis_jalur || '-')}
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Jarak Udara:</b> ${escapeHtml(formatMeter(t.jarak_udara_meter))}
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Panjang Kabel:</b> ${escapeHtml(formatMeter(t.panjang_kabel_meter))}
                </div>
            </div>
        `;
    }

    function buildPelangganRoutePopup(p) {
        return `
            <div style="min-width:230px;">
                <div style="font-weight:800;font-size:14px;margin-bottom:8px;">
                    ${escapeHtml(p.odp?.nama || '-')} → ${escapeHtml(p.nama)}
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Jenis Jalur:</b> Drop Core / Akses Pelanggan
                </div>

                <div style="font-size:13px;margin-bottom:4px;">
                    <b>Port ODP:</b> ${p.port ? escapeHtml('OUT ' + p.port.nomor_port) : '-'}
                </div>
            </div>
        `;
    }

    fetch("{{ route('map.data') }}")
        .then(response => response.json())
        .then(data => {
            const titik = data.titik || [];
            const pelanggan = data.pelanggan || [];

            document.getElementById('statTotal').textContent = titik.length;
            document.getElementById('statOlt').textContent = titik.filter(t => t.jenis === 'OLT').length;
            document.getElementById('statOdc').textContent = titik.filter(t => t.jenis === 'ODC').length;
            document.getElementById('statOdp').textContent = titik.filter(t => t.jenis === 'ODP').length;
            document.getElementById('statPelanggan').textContent = pelanggan.length;

            const bounds = [];

            titik.forEach(t => {
                if (!t.lat || !t.lng || !t.parent || !t.parent.lat || !t.parent.lng) {
                    return;
                }

                const from = getDisplayLatLng(t.parent);
                const to = getDisplayLatLng(t);

                bounds.push(from);
                bounds.push(to);

                const lineColor = getLineColor(t.jenis_jalur, t.jenis);

                const routeLine = L.polyline([from, to], {
                    color: lineColor,
                    weight: 4,
                    opacity: 0.72,
                    dashArray: t.jenis === 'ODP' ? '8, 8' : null
                }).addTo(window.dashboardMap);

                routeLine.bindPopup(buildRoutePopup(t));
            });

            pelanggan.forEach(p => {
                if (!p.lat || !p.lng || !p.odp || !p.odp.lat || !p.odp.lng) {
                    return;
                }

                const from = getDisplayLatLng({
                    jenis: 'ODP',
                    lat: p.odp.lat,
                    lng: p.odp.lng
                });

                const to = getDisplayPelangganLatLng(p);

                bounds.push(from);
                bounds.push(to);

                const line = L.polyline([from, to], {
                    color: getLineColor(null, 'PELANGGAN'),
                    weight: 3,
                    opacity: 0.58,
                    dashArray: '4, 8'
                }).addTo(window.dashboardMap);

                line.bindPopup(buildPelangganRoutePopup(p));
            });

            titik.forEach(t => {
                if (!t.lat || !t.lng) return;

                const position = getDisplayLatLng(t);

                bounds.push(position);

                L.marker(position, {
                    icon: buildPinIcon(t.jenis)
                }).addTo(window.dashboardMap).bindPopup(buildPopupContent(t));
            });

            pelanggan.forEach(p => {
                if (!p.lat || !p.lng) return;

                const position = getDisplayPelangganLatLng(p);

                bounds.push(position);

                L.marker(position, {
                    icon: buildPinIcon('PELANGGAN')
                }).addTo(window.dashboardMap).bindPopup(buildPelangganPopup(p));
            });

            if (bounds.length > 0) {
                window.dashboardMap.fitBounds(bounds, {
                    padding: [30, 30]
                });

                setTimeout(function() {
                    window.dashboardMap.invalidateSize();
                    window.dashboardMap.fitBounds(bounds, {
                        padding: [30, 30]
                    });
                }, 300);
            }
        })
        .catch(() => {
            console.log('Gagal memuat data peta dashboard.');
        });
</script>
@endpush