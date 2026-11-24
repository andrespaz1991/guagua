(() => {
    'use strict';
    const viewer = document.querySelector('.red-viewer');
    if (!viewer) return;
    const stage = viewer.querySelector('#red-stage');
    const layout = viewer.querySelector('.visor-container');
    const status = viewer.querySelector('[role="status"]');
    if (!stage || !layout || !status) return;
    const announce = (message) => { status.textContent = message; };
    const fullscreen = viewer.querySelector('[data-action="fullscreen"]');
    fullscreen.hidden = !document.fullscreenEnabled || !stage.requestFullscreen;
    viewer.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;
        switch (button.dataset.action) {
            case 'wide': {
                const wide = layout.classList.toggle('viewer-wide');
                button.setAttribute('aria-pressed', String(wide));
                button.textContent = wide ? 'Mostrar detalles' : 'Ampliar vista';
                break;
            }
            case 'exit-fullscreen':
                try { await document.exitFullscreen(); } catch (error) { announce('Usa Escape para salir de pantalla completa.'); }
                break;
            case 'fullscreen':
                try {
                    await stage.requestFullscreen();
                } catch (error) {
                    announce('No se pudo activar la pantalla completa. Puedes ampliar la vista.');
                }
                break;
            case 'copy': {
                const url = new URL(window.location.href);
                const resourceId = url.searchParams.get('red');
                url.search = '';
                url.hash = '';
                url.searchParams.set('red', resourceId);
                try {
                    await navigator.clipboard.writeText(url.href);
                    announce('Enlace del recurso copiado.');
                } catch (error) {
                    // También funciona en instalaciones HTTP sin acceso al portapapeles.
                    status.textContent = 'Copia el enlace del recurso: ';
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.readOnly = true;
                    input.value = url.href;
                    input.setAttribute('aria-label', 'Enlace del recurso para copiar');
                    status.appendChild(input);
                    input.focus();
                    input.select();
                }
                break;
            }
        }
    });
    viewer.querySelectorAll('[data-media]').forEach((media) => {
        media.addEventListener('error', () => announce('No se pudo cargar el archivo. Intenta abrir el recurso o descargarlo.'));
    });
    // Mantener la integración con favoritos y añadir operación por teclado.
    viewer.querySelectorAll('.fav_visor [onclick]').forEach((favorite) => {
        favorite.tabIndex = 0;
        favorite.setAttribute('role', 'button');
        favorite.setAttribute('aria-label', 'Cambiar mi estrella para este recurso');
        favorite.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                favorite.click();
            }
        });
    });
})();
