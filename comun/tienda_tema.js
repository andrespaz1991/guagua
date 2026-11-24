(() => {
    'use strict';
    const key = 'guagua.apps.theme';
    const root = document.documentElement;
    const valid = value => value === 'light' || value === 'dark';

    function apply(theme) {
        theme = valid(theme) ? theme : 'dark';
        root.dataset.storeTheme = theme;
        root.style.colorScheme = theme;
        document.querySelector('meta[name="color-scheme"]')?.setAttribute('content', theme);
        document.querySelectorAll('[data-store-theme-option]').forEach(button => {
            button.setAttribute('aria-pressed', String(button.dataset.storeThemeOption === theme));
        });
    }

    let saved;
    try { saved = localStorage.getItem(key); } catch (error) { /* La elección sigue funcionando sin almacenamiento. */ }
    apply(saved);
    document.addEventListener('DOMContentLoaded', () => apply(root.dataset.storeTheme));
    document.addEventListener('click', event => {
        const button = event.target.closest('[data-store-theme-option]');
        if (!button || !valid(button.dataset.storeThemeOption)) return;
        const theme = button.dataset.storeThemeOption;
        apply(theme);
        try { localStorage.setItem(key, theme); } catch (error) { /* No bloquear el cambio de apariencia. */ }
    });
    window.addEventListener('storage', event => {
        if (event.key === key) apply(event.newValue);
    });
})();
