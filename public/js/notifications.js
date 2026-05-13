/* ═══════════════════════════════════════════════════════════════
   notifications.js — Toast notification system for USeP VRS
   Usage:
     VRS.notify.success('Reservation submitted!');
     VRS.notify.error('Something went wrong.');
     VRS.notify.warning('Please check your input.');
     VRS.notify.info('Your session will expire soon.');
   ═══════════════════════════════════════════════════════════════ */

window.VRS = window.VRS || {};

VRS.notify = (function () {
    'use strict';

    let container = null;

    const ICONS = {
        success: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="#16a34a"/><path d="M6 10l3 3 5-6" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        error:   '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="#dc2626"/><path d="M7 7l6 6M13 7l-6 6" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>',
        warning: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="#d97706"/><path d="M10 6v5M10 13v1" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>',
        info:    '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="10" fill="#2563eb"/><path d="M10 9v5M10 6v1" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>',
    };

    function ensureContainer() {
        if (container && document.body.contains(container)) return;
        container = document.createElement('div');
        container.id = 'vrs-notifications';
        container.className = 'vrs-toast-container';
        document.body.appendChild(container);
    }

    /**
     * Show a toast notification.
     * @param {string} type - success | error | warning | info
     * @param {string} message - The notification message
     * @param {number} duration - Auto-dismiss time in ms (default 4000)
     */
    function show(type, message, duration = 4000) {
        ensureContainer();

        const toast = document.createElement('div');
        toast.className = `vrs-toast vrs-toast--${type}`;

        toast.innerHTML = `
            <div class="vrs-toast__icon">${ICONS[type] || ICONS.info}</div>
            <div class="vrs-toast__body">
                <span class="vrs-toast__title">${capitalize(type)}</span>
                <span class="vrs-toast__message">${escapeHtml(message)}</span>
            </div>
            <button class="vrs-toast__close" aria-label="Close">&times;</button>
            <div class="vrs-toast__progress"></div>
        `;

        // Close button
        toast.querySelector('.vrs-toast__close').addEventListener('click', () => dismiss(toast));

        container.appendChild(toast);

        // Trigger entrance animation
        requestAnimationFrame(() => {
            toast.classList.add('vrs-toast--visible');
        });

        // Start progress bar
        const progressBar = toast.querySelector('.vrs-toast__progress');
        progressBar.style.animationDuration = duration + 'ms';
        progressBar.classList.add('vrs-toast__progress--running');

        // Auto-dismiss
        const timer = setTimeout(() => dismiss(toast), duration);
        toast._timer = timer;

        return toast;
    }

    function dismiss(toast) {
        if (toast._timer) clearTimeout(toast._timer);
        toast.classList.remove('vrs-toast--visible');
        toast.classList.add('vrs-toast--exit');
        toast.addEventListener('animationend', () => {
            toast.remove();
        }, { once: true });
        // Fallback removal if animationend doesn't fire
        setTimeout(() => {
            if (toast.parentNode) toast.remove();
        }, 400);
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    return {
        success: (msg, dur) => show('success', msg, dur),
        error:   (msg, dur) => show('error',   msg, dur),
        warning: (msg, dur) => show('warning', msg, dur),
        info:    (msg, dur) => show('info',    msg, dur),
        show:    show,
        dismiss: dismiss,
    };
})();
