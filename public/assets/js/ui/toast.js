(() => {
    'use strict';

    const allowedTypes = ['success', 'error', 'warning', 'info'];
    const allowedPositions = [
        'top-right', 'top-left', 'top-center',
        'bottom-right', 'bottom-left', 'bottom-center', 'center',
    ];
    const icons = { success: '✓', error: '!', warning: '!', info: 'i' };

    function stack(position) {
        const safePosition = allowedPositions.includes(position) ? position : 'top-right';
        const id = `toast-stack-${safePosition}`;
        let region = document.getElementById(id);
        if (region) return region;

        region = document.createElement('div');
        region.id = id;
        region.className = `toast-stack toast-stack--${safePosition}`;
        region.setAttribute('aria-label', document.body.dataset.toastRegion || 'Notifications');
        document.body.appendChild(region);
        return region;
    }

    function show(message, options = {}) {
        const type = allowedTypes.includes(options.type) ? options.type : 'info';
        const position = options.position || 'top-right';
        const item = document.createElement('div');
        item.className = `toast toast--${type}`;
        item.setAttribute('role', type === 'error' || type === 'warning' ? 'alert' : 'status');

        const symbol = document.createElement('span');
        symbol.className = 'toast-icon';
        symbol.setAttribute('aria-hidden', 'true');
        symbol.textContent = icons[type];

        const copy = document.createElement('div');
        copy.className = 'toast-copy';
        if (options.title) {
            const title = document.createElement('strong');
            title.textContent = String(options.title);
            copy.appendChild(title);
        }
        const description = document.createElement('span');
        description.textContent = String(message);
        copy.appendChild(description);

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'toast-close';
        close.setAttribute('aria-label', document.body.dataset.toastClose || 'Close notification');
        close.textContent = '×';
        close.addEventListener('click', () => item.remove());

        item.append(symbol, copy, close);
        stack(position).appendChild(item);
        const timeout = Number(options.timeout ?? 5000);
        if (Number.isFinite(timeout) && timeout > 0) {
            window.setTimeout(() => item.remove(), timeout);
        }
        return item;
    }

    window.toast = {
        show,
        success: (message, options = {}) => show(message, { ...options, type: 'success' }),
        error: (message, options = {}) => show(message, { ...options, type: 'error' }),
        warning: (message, options = {}) => show(message, { ...options, type: 'warning' }),
        info: (message, options = {}) => show(message, { ...options, type: 'info' }),
    };

    const embedded = document.getElementById('server-toasts');
    if (embedded) {
        try {
            const messages = JSON.parse(embedded.textContent || '[]');
            if (Array.isArray(messages)) {
                messages.forEach((entry) => {
                    if (entry && typeof entry === 'object') {
                        show(entry.message || '', {
                            type: entry.type,
                            title: entry.title,
                            position: entry.position,
                            timeout: 7000,
                        });
                    }
                });
            }
        } catch (_) {
            // A malformed optional payload must not break the page.
        }
    }

    if (Array.isArray(window.__toastQueue)) {
        window.__toastQueue.forEach((entry) => {
            if (entry && typeof entry === 'object') show(entry.message || '', entry);
        });
        window.__toastQueue = [];
    }
})();
