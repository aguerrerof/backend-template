import './bootstrap';
import './challenge.js';
import Alpine from 'alpinejs';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

function resolveInitialTheme() {
    const stored = localStorage.getItem('theme');
    if (stored === 'dark' || stored === 'light') return stored;

    const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)')?.matches;
    return prefersDark ? 'dark' : 'light';
}

function applyTheme(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme } }));
}

window.__setTheme = (theme) => {
    if (theme !== 'dark' && theme !== 'light') return;
    localStorage.setItem('theme', theme);
    applyTheme(theme);
};

window.__toggleTheme = () => {
    const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
    window.__setTheme(next);
};

window.__getTheme = () => (document.documentElement.classList.contains('dark') ? 'dark' : 'light');

window.Alpine = Alpine;

Alpine.start();

try {
    applyTheme(resolveInitialTheme());
} catch {
    // ignore
}
