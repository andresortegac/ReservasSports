document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('appSidebar');
    const toggle = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('appOverlay');

    if (!sidebar || !toggle || !overlay) {
        return;
    }

    const closeSidebar = function () {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
    };

    toggle.addEventListener('click', function () {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
    });

    overlay.addEventListener('click', closeSidebar);
});
