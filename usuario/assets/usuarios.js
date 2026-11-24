(() => {
    'use strict';
    const form = document.querySelector('#users-search');
    const results = document.querySelector('#users-results');
    const status = document.querySelector('#users-search-status');
    const editor = document.querySelector('#usuario-form');
    if (!form || !results || !status) return;
    let controller;
    let timer;
    let sequence = 0;
    const searchUrl = (page) => {
        const url = new URL(form.action, location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        url.searchParams.set('page', page);
        return url;
    };
    const invalidate = () => {
        clearTimeout(timer);
        sequence++;
        if (controller) controller.abort();
    };
    const load = async (page) => {
        invalidate();
        const requestSequence = sequence;
        controller = new AbortController();
        const url = searchUrl(page);
        const request = new URL(url);
        request.searchParams.set('ajax_search', '1');
        results.setAttribute('aria-busy', 'true');
        status.textContent = 'Buscando perfiles…';
        try {
            const response = await fetch(request, { signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (response.redirected || response.headers.get('X-Guagua-Fragment') !== 'usuarios') {
                throw new Error('La sesión puede haber caducado. Recargue la página para continuar.');
            }
            if (!response.ok) throw new Error('No se pudo consultar el directorio. Inténtelo de nuevo.');
            const html = await response.text();
            if (requestSequence !== sequence) return;
            results.innerHTML = html;
            const summary = results.querySelector('[data-total]');
            if (summary) url.searchParams.set('page', summary.dataset.page);
            history.replaceState(null, '', url);
            status.textContent = summary ? `${summary.dataset.total} ${summary.dataset.total === '1' ? 'perfil encontrado' : 'perfiles encontrados'}.` : 'Directorio actualizado.';
        } catch (error) {
            if (error.name !== 'AbortError' && requestSequence === sequence) status.textContent = error.message + ' Se mantienen los resultados anteriores.';
        } finally {
            if (requestSequence === sequence) results.setAttribute('aria-busy', 'false');
        }
    };
    form.addEventListener('submit', (event) => { event.preventDefault(); load(1); });
    form.querySelector('[name="buscar"]').addEventListener('input', () => {
        invalidate();
        timer = setTimeout(() => load(1), 350);
    });
    form.querySelector('select').addEventListener('change', () => load(1));
    results.addEventListener('click', (event) => {
        const link = event.target.closest('[data-page]');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        load(link.dataset.page);
    });
    document.addEventListener('submit', (event) => {
        const deleteForm = event.target.closest('[data-delete-form]');
        if (deleteForm && !window.confirm(`¿Eliminar permanentemente el perfil de ${deleteForm.dataset.name}? Esta operación no se puede deshacer.`)) event.preventDefault();
        const stateForm = event.target.closest('[data-state-form]');
        if (!stateForm) return;
        const inactive = stateForm.elements.estado.value === 'inactivo';
        const message = inactive ? `¿Desactivar el acceso de ${stateForm.dataset.name}? Sus datos y vínculos se conservarán.` : `¿Activar el acceso de ${stateForm.dataset.name}?`;
        if (!window.confirm(message)) event.preventDefault();
    });
    document.querySelectorAll('[data-open-form]').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (!editor) return;
            if (document.querySelector('[name="id_usuario_original"]')) {
                // Crear siempre abre un formulario limpio, incluso desde una edición.
                const url = searchUrl(1);
                url.searchParams.set('nuevo', '1');
                url.hash = 'usuario-form';
                link.href = url.href;
                return;
            }
            event.preventDefault();
            editor.open = true;
            editor.scrollIntoView({ block: 'start' });
            editor.querySelector('input:not([type="hidden"])').focus({ preventScroll: true });
        });
    });
    // Completar la identificación de acceso sin sobrescribir un nombre elegido.
    const documentInput = document.querySelector('#users-id_usuario');
    const username = document.querySelector('#users-usuario');
    if (!document.querySelector('[name="id_usuario_original"]') && documentInput && username) {
        documentInput.addEventListener('blur', () => { if (!username.value.trim()) username.value = documentInput.value.trim(); });
    }
    document.querySelector('#users-edit-form')?.addEventListener('submit', (event) => {
        const button = event.submitter;
        if (button) { button.disabled = true; button.textContent = 'Guardando…'; }
    });
})();
