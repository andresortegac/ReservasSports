// resources/js/app.js

import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// --- Sidebar móvil ---
document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.querySelector(".sidebar");
    const overlay = document.querySelector(".overlay");
    const btnToggle = document.querySelector("#sidebarToggle");

    if (btnToggle) {
        btnToggle.addEventListener("click", () => {
            sidebar.classList.toggle("open");
            overlay.classList.toggle("show");
        });
    }

    if (overlay) {
        overlay.addEventListener("click", () => {
            sidebar.classList.remove("open");
            overlay.classList.remove("show");
        });
    }
});
