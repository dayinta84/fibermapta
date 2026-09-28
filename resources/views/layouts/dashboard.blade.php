<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard') - WebGIS FTTH</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}?v={{ time() }}">
    @stack('styles')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css">
</head>

<body>
    @php
    $user = auth()->user();
    $role = $user->role ?? null;

    $isSuperadmin = $role === 'superadmin';
    $isAdmin = $role === 'admin';
    $isTeknisi = $role === 'teknisi';

    $canManageNetwork = in_array($role, ['superadmin', 'admin']);
    $canManageUser = $isSuperadmin;

    $roleLabel = match ($role) {
    'superadmin' => 'Superadmin',
    'admin' => 'Admin',
    'teknisi' => 'Teknisi',
    default => 'User',
    };

    $roleDescription = match ($role) {
    'superadmin' => 'Akses penuh sistem',
    'admin' => 'Pengelola data jaringan',
    'teknisi' => 'Akses teknis lapangan',
    default => 'Pengguna sistem',
    };
    @endphp

    <div class="app-shell">

        <aside id="sidebar" class="sidebar">
            <div class="sidebar-top">
                <div class="sidebar-brand">
                    <div class="brand-icon">
                        <img src="{{ asset('assets/siport_icon.svg') }}" alt="Logo SI PORT">
                    </div>

                    <div class="brand-text">
                        <div class="brand-title">WebGIS FTTH</div>
                        <div class="brand-subtitle">Monitoring Jaringan & PLB</div>
                    </div>
                </div>

                <nav class="sidebar-menu">

                    <a href="{{ route('dashboard') }}"
                        class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="menu-icon">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4.5 10.8L12 4.5L19.5 10.8V19A1.5 1.5 0 0 1 18 20.5H6A1.5 1.5 0 0 1 4.5 19V10.8Z" />
                                <path d="M9.5 20.5V14H14.5V20.5" />
                            </svg>
                        </span>
                        <span class="menu-text">Dashboard WebGIS</span>
                    </a>

                    <a href="{{ route('titik.index') }}"
                        class="menu-item {{ request()->routeIs('titik.*') ? 'active' : '' }}">
                        <span class="menu-icon">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 5.5V18.5" />
                                <path d="M6.5 9H17.5" />
                                <path d="M6.5 15H17.5" />
                                <circle cx="6.5" cy="9" r="2" />
                                <circle cx="17.5" cy="9" r="2" />
                                <circle cx="6.5" cy="15" r="2" />
                                <circle cx="17.5" cy="15" r="2" />
                            </svg>
                        </span>
                        <span class="menu-text">
                            {{ $canManageNetwork ? 'Kelola Jaringan & Port' : 'Data Jaringan & Port' }}
                        </span>
                    </a>

                    <a href="{{ route('pelanggan.index') }}"
                        class="menu-item {{ request()->routeIs('pelanggan.*') ? 'active' : '' }}">
                        <span class="menu-icon">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="8.2" r="3.2" />
                                <path d="M5.5 19.5C6.3 15.9 8.6 14 12 14C15.4 14 17.7 15.9 18.5 19.5" />
                            </svg>
                        </span>
                        <span class="menu-text">
                            {{ $canManageNetwork ? 'Kelola Pelanggan' : 'Data Pelanggan' }}
                        </span>
                    </a>

                    <a href="{{ route('simulasi-topologi-plb.index') }}"
                        class="menu-item {{ request()->routeIs('simulasi-topologi-plb.*') ? 'active' : '' }}">
                        <span class="menu-icon">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M5 18.5H19" />
                                <path d="M6.5 16L10 11.5L13.2 14L17.8 7.5" />
                                <path d="M5.5 5.5H18.5A1.5 1.5 0 0 1 20 7V17.5H4V7A1.5 1.5 0 0 1 5.5 5.5Z" />
                            </svg>
                        </span>
                        <span class="menu-text">
                            {{ $canManageNetwork ? 'Simulasi PLB Topologi' : 'Hasil Simulasi PLB' }}
                        </span>
                    </a>

                    @if($canManageUser)
                    <a href="{{ route('users.index') }}"
                        class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <span class="menu-icon">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="9" cy="8.5" r="3" />
                                <path d="M3.8 19C4.5 15.8 6.4 14.2 9 14.2C11.6 14.2 13.5 15.8 14.2 19" />
                                <circle cx="16.5" cy="9.5" r="2.4" />
                                <path d="M15.3 14.5C17.9 14.7 19.6 16.1 20.2 19" />
                            </svg>
                        </span>
                        <span class="menu-text">Kelola User</span>
                    </a>
                    @endif

                </nav>
            </div>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>

                    <div class="sidebar-user-info">
                        <div class="sidebar-user-name">{{ $user->name ?? '-' }}</div>
                        <div class="sidebar-user-role">
                            {{ $roleLabel }} · {{ $roleDescription }}
                        </div>
                    </div>
                </div>

                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logoutForm').submit();"
                    class="menu-item logout-item">
                    <span class="menu-icon">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M10.5 6.5V5.5A1.5 1.5 0 0 1 12 4H18A1.5 1.5 0 0 1 19.5 5.5V18.5A1.5 1.5 0 0 1 18 20H12A1.5 1.5 0 0 1 10.5 18.5V17.5" />
                            <path d="M4.5 12H14" />
                            <path d="M7.8 8.7L4.5 12L7.8 15.3" />
                        </svg>
                    </span>
                    <span class="menu-text">Logout</span>
                </a>

                <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">
                    @csrf
                </form>
            </div>
        </aside>

        <div id="sidebarOverlay" class="sidebar-overlay"></div>

        <div class="main-area">
            <header class="topbar">
                <div class="topbar-left">
                    <button id="toggleSidebar" class="hamburger" type="button" aria-label="Toggle sidebar">
                        ☰
                    </button>

                    <div>
                        <div class="topbar-page-title">
                            @yield('topbar_title', 'Dashboard')
                        </div>

                        <div class="topbar-page-subtitle">
                            @yield('topbar_subtitle', 'Sistem Informasi Geografis FTTH')
                        </div>
                    </div>
                </div>

                <div class="topbar-right">
                    <div class="topbar-user">
                        <div class="topbar-user-name">{{ $user->name ?? '-' }}</div>
                        <div class="topbar-user-role">
                            {{ $roleLabel }}
                        </div>
                    </div>

                    <div class="topbar-avatar">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                </div>
            </header>

            <main class="content-area">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ time() }}"></script>

    @stack('scripts')
</body>

</html>