import './bootstrap';
import Alpine from 'alpinejs';

if (!window.Alpine) {
    window.Alpine = Alpine;
}

// Global Toast Store for FE
if (window.Alpine && !window.Alpine.store('toast')) {
    window.Alpine.store('toast', {
        items: [],
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

// Start Alpine safely only if not already started
if (!window._alpineStarted && window.Alpine) {
    window._alpineStarted = true;
    window.Alpine.start();
}

