/* ═══════════════════════════════════════════════════════════════
   reservation.js — New reservation form validation & AJAX submit
   ═══════════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    const form        = document.getElementById('reservation-form');
    const submitBtn   = document.getElementById('submit-btn');
    const depDateEl   = document.getElementById('departure_date');
    const retDateEl   = document.getElementById('return_date');

    if (!form) return;

    // ── Set minimum departure date to today ───────────────────────
    const today = new Date().toISOString().split('T')[0];
    if (depDateEl) depDateEl.min = today;
    if (retDateEl) retDateEl.min = today;

    // ── Ensure return date >= departure date ──────────────────────
    if (depDateEl && retDateEl) {
        depDateEl.addEventListener('change', () => {
            retDateEl.min = depDateEl.value;
            if (retDateEl.value && retDateEl.value < depDateEl.value) {
                retDateEl.value = depDateEl.value;
            }
        });
    }

    // ── AJAX form submission ─────────────────────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Client-side required field check
        const required = form.querySelectorAll('[required]');
        let valid = true;

        required.forEach(field => {
            field.style.borderColor = '';
            if (!field.value.trim()) {
                field.style.borderColor = 'var(--danger)';
                valid = false;
            }
        });

        if (!valid) {
            VRS.notify.warning('Please fill in all required fields.');
            return;
        }

        // Date validation
        if (depDateEl && depDateEl.value < today) {
            VRS.notify.warning('Departure date cannot be in the past.');
            depDateEl.style.borderColor = 'var(--danger)';
            return;
        }

        if (depDateEl && retDateEl && retDateEl.value < depDateEl.value) {
            VRS.notify.warning('Return date must be on or after departure date.');
            retDateEl.style.borderColor = 'var(--danger)';
            return;
        }

        await VRS.ajax.submitForm(form, {
            submitBtn: submitBtn,
            onSuccess: (data) => {
                VRS.notify.success(data.message || 'Reservation submitted successfully!');
                if (data.redirect) {
                    setTimeout(() => { window.location.href = data.redirect; }, 1000);
                }
            },
        });
    });

})();
