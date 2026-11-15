import './bootstrap';
import Alpine from 'alpinejs';

if (!window.Alpine) {
    window.Alpine = Alpine;
}

// Global Toast Store for FE
if (window.Alpine && !window.Alpine.store('toast')) {
    window.Alpine.store('toast', {
        items: [],
        show(message, type = 'success', duration = 3500) {
            this.add(message, type, duration);
        },
        add(message, type = 'success', duration = 3500) {
            const id = Date.now() + Math.random().toString(36).substring(2, 7);
            this.items.push({ id, message, type });
            setTimeout(() => {
                this.remove(id);
            }, duration);
        },
        remove(id) {
            this.items = this.items.filter(item => item.id !== id);
        }
    });
}

// ASIC Frontend API Client Layer (Connecting Blade to Backend APIs)
window.asicApi = {
    csrfToken() {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        return tokenMeta ? tokenMeta.getAttribute('content') : '';
    },

    async request(endpoint, options = {}) {
        const url = endpoint.startsWith('http') ? endpoint : (endpoint.startsWith('/') ? endpoint : '/' + endpoint);
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.csrfToken(),
            ...(options.headers || {})
        };

        if (options.body && !(options.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(options.body);
        }

        console.info(`[ASIC API Request] ${options.method || 'GET'} ${url}`, options.body ? options.body : '');

        try {
            const response = await fetch(url, {
                ...options,
                headers
            });

            let data = null;
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                data = await response.text();
            }

            console.info(`[ASIC API Response] ${response.status} from ${url}:`, data);

            return {
                ok: response.ok,
                status: response.status,
                data: data,
                error: response.ok ? null : (data?.message || `HTTP ${response.status}: Request failed`)
            };
        } catch (err) {
            console.warn(`[ASIC API Network/CORS] ${url}:`, err);
            return {
                ok: false,
                status: 0,
                data: null,
                error: err.message || 'Network error / endpoint unavailable'
            };
        }
    },

    get(endpoint, params = {}) {
        const query = new URLSearchParams(params).toString();
        const url = query ? `${endpoint}?${query}` : endpoint;
        return this.request(url, { method: 'GET' });
    },

    post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: data
        });
    },

    put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: data
        });
    },

    delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
};

// Start Alpine safely only if not already started
if (!window._alpineStarted && window.Alpine) {
    window._alpineStarted = true;
    window.Alpine.start();
}
