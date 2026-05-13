/* ═══════════════════════════════════════════════════════════════
   dashboard.js — Live clock + UI helpers
   ═══════════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    // ── Live Clock ────────────────────────────────────────────────
    const clockEl = document.getElementById('clock');
    if (clockEl) {
        function updateClock() {
            const now = new Date();
            clockEl.textContent = now.toLocaleTimeString('en-PH', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
        }
        updateClock();
        setInterval(updateClock, 1000);
    }

    // ── Confirm delete / cancel dialogs ────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });

    // ── Stat number counter animation ─────────────────────────────
    document.querySelectorAll('.stat .num').forEach(el => {
        const target = parseInt(el.textContent, 10);
        if (isNaN(target)) return;
        let current  = 0;
        const step   = Math.ceil(target / 40);
        const timer  = setInterval(() => {
            current += step;
            if (current >= target) { current = target; clearInterval(timer); }
            el.textContent = current;
        }, 25);
    });

})();
