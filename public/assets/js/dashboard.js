document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const toggleSidebar = document.getElementById('toggleSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (!sidebar || !toggleSidebar) return;

    const storageKey = 'webftth_sidebar_collapsed';
    const mobileQuery = window.matchMedia('(max-width: 768px)');

    function refreshMap(delay = 300) {
        setTimeout(function () {
            if (window.dashboardMap) {
                window.dashboardMap.invalidateSize();
            }
        }, delay);
    }

    function isMobile() {
        return mobileQuery.matches;
    }

    function applySidebarState() {
        document.body.classList.remove('sidebar-open');

        if (isMobile()) {
            sidebar.classList.remove('collapsed');
            refreshMap(350);
            return;
        }

        const savedState = localStorage.getItem(storageKey);

        if (savedState === 'true') {
            sidebar.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
        }

        refreshMap(350);
    }

    applySidebarState();

    toggleSidebar.addEventListener('click', function () {
        if (isMobile()) {
            sidebar.classList.remove('collapsed');
            document.body.classList.toggle('sidebar-open');
            refreshMap(300);
            return;
        }

        sidebar.classList.toggle('collapsed');
        localStorage.setItem(storageKey, sidebar.classList.contains('collapsed'));
        refreshMap(300);
    });

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            document.body.classList.remove('sidebar-open');
            refreshMap(300);
        });
    }

    document.querySelectorAll('.sidebar .menu-item').forEach(function (item) {
        item.addEventListener('click', function () {
            if (isMobile()) {
                document.body.classList.remove('sidebar-open');
            }
        });
    });

    window.addEventListener('resize', function () {
        applySidebarState();
    });
});