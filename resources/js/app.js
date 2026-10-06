import Alpine from 'alpinejs';
import * as lucide from 'lucide';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;
window.lucide = lucide;

export function initLucideIcons() {
    createIcons({
        icons,
        attrs: {
            'stroke-width': 2,
        },
    });
}

window.initLucideIcons = initLucideIcons;

// Theme handling (Light / Dark mode)
export function initTheme() {
    const storedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (storedTheme === 'dark' || (!storedTheme && prefersDark)) {
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.setAttribute('data-theme', 'light');
    }
}

export function toggleTheme() {
    const isDark = document.documentElement.classList.contains('dark');
    if (isDark) {
        document.documentElement.classList.remove('dark');
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
    } else {
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
    }
    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: isDark ? 'light' : 'dark' } }));
}

window.initTheme = initTheme;
window.toggleTheme = toggleTheme;

document.addEventListener('DOMContentLoaded', () => {
    initLucideIcons();
    initTheme();
});

document.addEventListener('livewire:navigated', () => {
    initLucideIcons();
    initTheme();
});

Alpine.start();

