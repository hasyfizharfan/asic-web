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

// Cookie-based SPA API client shared by all real portal pages.
/**
 * Format nominal sesuai mata uang (IDR tanpa desimal, USD dua desimal) dan bahasa halaman.
 * Nominal lintas mata uang tidak pernah dijumlahkan di klien.
 */
window.asicMoney = function (value, currency = 'IDR') {
    const code = currency === 'USD' ? 'USD' : 'IDR';
    const locale = document.documentElement.lang === 'en' ? 'en-US' : 'id-ID';
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: code,
        minimumFractionDigits: code === 'USD' ? 2 : 0,
        maximumFractionDigits: code === 'USD' ? 2 : 0,
    }).format(Number(value || 0));
};

window.asicApi = {
    xsrfCookie() {
        const cookie = document.cookie.split('; ').find((item) => item.startsWith('XSRF-TOKEN='));
        return cookie ? decodeURIComponent(cookie.substring('XSRF-TOKEN='.length)) : '';
    },

    async primeCsrf(force = false) {
        if (!force && this.xsrfCookie()) return;

        await fetch('/sanctum/csrf-cookie', {
            method: 'GET',
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        });
    },

    showToast(message, type = 'error') {
        if (window.Alpine?.store('toast')) window.Alpine.store('toast').show(message, type);
    },

    async request(endpoint, options = {}, retried = false) {
        const url = endpoint instanceof URL
            ? endpoint.toString()
            : (endpoint.startsWith('http') ? endpoint : (endpoint.startsWith('/') ? endpoint : `/${endpoint}`));
        const method = (options.method || 'GET').toUpperCase();
        const csrfRequired = !['GET', 'HEAD', 'OPTIONS'].includes(method);

        if (csrfRequired) await this.primeCsrf();

        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {}),
        };
        const xsrf = this.xsrfCookie();
        if (csrfRequired && xsrf) headers['X-XSRF-TOKEN'] = xsrf;

        let body = options.body;
        if (body && !(body instanceof FormData) && typeof body !== 'string' && !(body instanceof Blob)) {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify(body);
        }

        try {
            const response = await fetch(url, {
                ...options,
                method,
                body,
                credentials: 'same-origin',
                headers,
            });

            const contentType = response.headers.get('content-type') || '';
            const data = contentType.includes('application/json') ? await response.json() : await response.text();

            if (response.status === 419 && csrfRequired && !retried) {
                await this.primeCsrf(true);
                return this.request(endpoint, options, true);
            }

            if (response.status === 401 && !window.location.pathname.endsWith('/login')) {
                window.location.assign(`/login?redirect=${encodeURIComponent(window.location.pathname)}`);
            }

            if (response.status === 403) this.showToast(data?.message || 'Anda tidak memiliki akses ke halaman ini.');
            if (response.status === 419) this.showToast('Sesi keamanan kedaluwarsa. Silakan muat ulang halaman.');

            return {
                ok: response.ok,
                status: response.status,
                data,
                errors: response.status === 422 ? (data?.errors || {}) : {},
                error: response.ok ? null : (data?.message || `HTTP ${response.status}: Request failed`),
            };
        } catch (error) {
            return { ok: false, status: 0, data: null, errors: {}, error: error.message || 'Network error' };
        }
    },

    csrfToken() {
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        return tokenMeta ? tokenMeta.getAttribute('content') : '';
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

    patch(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PATCH',
            body: data
        });
    },

    delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    },

    upload(endpoint, formData) {
        return this.request(endpoint, { method: 'POST', body: formData });
    },

    postForm(endpoint, formData) {
        return this.upload(endpoint, formData);
    },
};

function clearFormErrors(form) {
    form.querySelectorAll('[data-asic-field-error]').forEach((node) => node.remove());
    form.querySelectorAll('[name]').forEach((input) => input.classList.remove('border-error'));
    const summary = form.querySelector('[data-form-error]');
    if (summary) {
        summary.textContent = '';
        summary.classList.add('hidden');
    }
}

function showFormErrors(form, result) {
    const errors = result.errors || {};
    Object.entries(errors).forEach(([field, messages]) => {
        const input = form.querySelector(`[name="${CSS.escape(field)}"]`);
        const wrapper = input?.closest('.space-y-1\\.5') || input?.parentElement?.parentElement;
        if (!wrapper) return;

        input.classList.add('border-error');
        const message = document.createElement('p');
        message.dataset.asicFieldError = 'true';
        message.className = 'text-[11px] text-error font-medium';
        message.textContent = Array.isArray(messages) ? messages[0] : String(messages);
        wrapper.append(message);
    });

    const summary = form.querySelector('[data-form-error]');
    if (summary && result.error) {
        summary.textContent = result.error;
        summary.classList.remove('hidden');
    } else if (result.error) {
        window.asicApi.showToast(result.error);
    }
}

document.querySelectorAll('form[data-asic-login], form[data-asic-api-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors(form);

        const submit = form.querySelector('[type="submit"]');
        if (submit) submit.disabled = true;

        const payload = Object.fromEntries(new FormData(form).entries());
        const result = await window.asicApi.post(form.action, payload);

        if (submit) submit.disabled = false;
        if (!result.ok) {
            showFormErrors(form, result);
            return;
        }

        window.asicApi.showToast(result.data?.message || 'Berhasil.', 'success');
        const redirect = result.data?.data?.redirect_url || form.dataset.successRedirect || '/login';
        window.location.assign(redirect);
    });
});

document.querySelectorAll('form[data-asic-logout]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const result = await window.asicApi.post(form.action, {});
        if (result.ok) window.location.assign('/login');
    });
});

// Start Alpine safely only if not already started
if (!window._alpineStarted && window.Alpine) {
    window._alpineStarted = true;
    window.Alpine.start();
}
