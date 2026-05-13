/* ═══════════════════════════════════════════════════════════════
   main.js — Global JavaScript utilities
   ═══════════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    // ── Sidebar toggle ────────────────────────────────────────────
    const toggleBtn = document.getElementById('toggleSideNav');
    const sideNav   = document.getElementById('sideNav');

    if (toggleBtn && sideNav) {
        toggleBtn.addEventListener('click', () => {
            sideNav.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (
                sideNav.classList.contains('show') &&
                !sideNav.contains(e.target) &&
                !toggleBtn.contains(e.target)
            ) {
                sideNav.classList.remove('show');
            }
        });
    }

    // ── Active nav link highlighting ──────────────────────────────
    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-btn').forEach(link => {
        const href = link.getAttribute('href') || '';
        if (href && currentPath.includes(href.split('/').pop())) {
            link.classList.add('active');
        }
    });

    // ── Auto-dismiss flash alerts ─────────────────────────────────
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });

})();
