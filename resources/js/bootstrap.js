import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;

// Sanctum's SPA flow uses the encrypted XSRF-TOKEN cookie, never a bearer token.
// Prime the cookie before each API request (including login and registration).
let csrfCookieRequest;
window.axios.interceptors.request.use(async config => {
    const url = new URL(config.url ?? '', config.baseURL ?? window.location.href);
    const isApiRequest = url.pathname.startsWith('/api/');
    const isCsrfRequest = url.pathname === '/sanctum/csrf-cookie';

    if (isApiRequest) {
        config.withCredentials = true;
        config.withXSRFToken = true;

        if (!isCsrfRequest) {
            csrfCookieRequest ??= window.axios.get('/sanctum/csrf-cookie')
                .finally(() => { csrfCookieRequest = undefined; });
            await csrfCookieRequest;
        }
    }

    return config;
});
