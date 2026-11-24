(() => {
    'use strict';
    const $ = selector => document.querySelector(selector);
    const store = window.guaguaStore;
    const base = document.body.dataset.base;
    const dialog = $('#menu-dialog');
    const body = $('#menu-dialog-content');
    const list = $('#menu-list');
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' })[char]);
    const normalize = value => String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es');
    const state = { rows: null, icons: [], loading: null, query: '', page: 1, busy: false, deepLink: false };
    let disabled = new Map();

    function icon(value) {
        value = String(value || '📁');
        if (/^icon-sga-[a-zA-Z0-9_-]+$/.test(value)) {
            const file = state.icons.find(item => item.valor === value)?.imagen || `${value.slice(9)}.png`;
            return `<img src="${escape(base)}/comun/img/png/${encodeURIComponent(file)}" alt="">`;
        }
        if (/^(?:fa[srlbd]? |fa-|bx[ -])/.test(value) && /^[a-zA-Z0-9_ -]+$/.test(value)) return `<i class="${escape(value)}" aria-hidden="true"></i>`;
        return escape(value);
    }

    function categoryValues() {
        const values = new Map();
        state.rows.forEach(row => { if (row.categoria && !values.has(normalize(row.categoria))) values.set(normalize(row.categoria), row.categoria); });
        return [...values.values()].sort((a, b) => a.localeCompare(b, 'es'));
    }

    function categories() {
        const selected = $('#menu-category').value;
        const values = categoryValues();
        $('#menu-category').innerHTML = '<option value="">Todas las categorías</option>' + values.map(value => `<option value="${escape(value)}">${escape(value)}</option>`).join('');
        if (values.includes(selected)) $('#menu-category').value = selected;
    }

    async function ensureLoaded() {
        if (state.rows !== null) return;
        if (!state.loading) {
            state.loading = store.request('menu_list').then(data => {
                state.rows = data.items; state.icons = data.icons; categories();
            }).finally(() => { state.loading = null; });
        }
        await state.loading;
    }

    function render() {
        if (!state.rows) return;
        const category = $('#menu-category').value;
        const filter = $('#menu-filter').value;
        const term = normalize(state.query);
        const rows = state.rows.filter(row => (!category || normalize(row.categoria) === normalize(category))
            && (filter !== 'favorites' || row.fav) && (filter !== 'header' || row.is_cabecera)
            && (filter !== 'other' || (!row.fav && !row.is_cabecera))
            && normalize(`${row.menu_item_name} ${row.menu_description} ${row.categoria} ${row.menu_url}`).includes(term))
            .sort((a, b) => a.menu_item_name.localeCompare(b.menu_item_name, 'es'));
        const pages = Math.max(1, Math.ceil(rows.length / 18));
        state.page = Math.max(1, Math.min(state.page, pages));
        $('#menu-count').textContent = `${rows.length} ${rows.length === 1 ? 'acceso' : 'accesos'}`;
        list.setAttribute('aria-busy', 'false');
        if (!rows.length) {
            list.innerHTML = '<div class="empty-state"><span class="empty-icon" aria-hidden="true">☷</span><h3>No hay accesos que coincidan</h3><p>Prueba otro nombre o añade un nuevo acceso al menú.</p></div>';
        } else {
            list.innerHTML = rows.slice((state.page - 1) * 18, state.page * 18).map(row => `<article class="menu-entry">
                <span class="menu-entry-icon" aria-hidden="true">${icon(row.icono)}</span>
                <div class="menu-entry-info"><button type="button" class="menu-entry-name" data-menu-edit="${row.menu_item_id}">${escape(row.menu_item_name)}</button><p>${escape(row.menu_description || row.categoria || 'Acceso de Guagua')}</p><span class="menu-entry-url" title="${escape(row.menu_url)}">${escape(row.menu_url)}</span></div>
                <span class="menu-entry-category">${escape(row.categoria || 'Sin categoría')}</span>
                <div class="menu-entry-actions"><button type="button" class="menu-flag ${row.fav ? 'selected' : ''}" data-menu-flag="fav" data-menu-id="${row.menu_item_id}" aria-pressed="${row.fav}" aria-label="${row.fav ? 'Quitar de favoritas' : 'Marcar favorita'}: ${escape(row.menu_item_name)}" title="Favorita"><span aria-hidden="true">${row.fav ? '★' : '☆'}</span></button><button type="button" class="menu-flag menu-header-flag ${row.is_cabecera ? 'selected' : ''}" data-menu-flag="is_cabecera" data-menu-id="${row.menu_item_id}" aria-pressed="${row.is_cabecera}" aria-label="${row.is_cabecera ? 'Quitar de cabecera' : 'Anclar a cabecera'}: ${escape(row.menu_item_name)}">⌖ <span>Cabecera</span></button><button type="button" class="menu-edit-button" data-menu-edit="${row.menu_item_id}" aria-label="Editar acceso: ${escape(row.menu_item_name)}">Editar</button><a class="menu-open-button" href="${escape(base)}/comun/modulo.php?id=${row.menu_item_id}" target="${escape(row.url_target)}" rel="noopener" aria-label="Abrir acceso: ${escape(row.menu_item_name)}">Abrir</a></div>
            </article>`).join('');
        }
        $('#menu-pagination').innerHTML = pages > 1 ? `<button type="button" class="secondary-button" data-menu-page="${state.page - 1}" ${state.page === 1 ? 'disabled' : ''}>Anterior</button><span>Página ${state.page} de ${pages}</span><button type="button" class="secondary-button" data-menu-page="${state.page + 1}" ${state.page === pages ? 'disabled' : ''}>Siguiente</button>` : '';
    }

    async function show(query = '') {
        if (query !== state.query) state.page = 1;
        state.query = query;
        try {
            await ensureLoaded(); render();
            if (!state.deepLink) {
                state.deepLink = true;
                const id = new URLSearchParams(location.search).get('editar');
                if (id && /^\d+$/.test(id)) {
                    const row = state.rows.find(row => row.menu_item_id === Number(id));
                    if (row) editor(row);
                }
            }
        } catch (error) {
            list.setAttribute('aria-busy', 'false');
            list.innerHTML = `<div class="empty-state"><h3>No se pudo cargar el menú</h3><p>${escape(error.message)}</p><button type="button" class="secondary-button" data-menu-retry>Volver a intentar</button></div>`;
        }
    }

    function error(message) {
        let element = $('#menu-dialog-error');
        if (!element) { element = document.createElement('p'); element.id = 'menu-dialog-error'; element.className = 'dialog-error'; element.setAttribute('role', 'alert'); body.append(element); }
        element.textContent = message;
    }

    function busy(value) {
        state.busy = value;
        if (value) {
            disabled = new Map();
            document.querySelectorAll('#menu-dialog button,#menu-dialog input,#menu-dialog select,#menu-dialog textarea,#menu-workspace button').forEach(element => { disabled.set(element, element.disabled); element.disabled = true; });
        } else { disabled.forEach((wasDisabled, element) => { element.disabled = wasDisabled; }); disabled.clear(); }
    }

    async function mutate(action, id, payload = {}) {
        if (state.busy) return;
        $('#menu-dialog-error')?.remove();
        busy(true);
        let saved = false;
        try {
            const result = await store.request(action, id, true, payload);
            saved = true;
            state.rows = result.items; categories(); render();
            dialog.close(); store.toast(result.message);
            await store.reload();
        } catch (cause) {
            if (saved) $('#store-status').textContent = 'El menú se guardó. Recarga la página para actualizar el catálogo de aplicaciones.';
            else if (dialog.open) error(cause.message);
            else store.toast(cause.message);
        } finally { busy(false); }
    }

    function editor(row = {}) {
        if (state.busy) return;
        const id = row.menu_item_id || '';
        const defaults = ['📁','📚','📝','📊','🗓️','👥','🎓','💡','🧩','🎨','🛒','⚙️','🏠','✅','🔔','💬'];
        const icons = [...new Set([...defaults, ...state.rows.map(item => item.icono), ...state.icons.map(item => item.valor)])].filter(value => value && [...value].length <= 25);
        const cat = categoryValues();
        body.innerHTML = `<h2 id="menu-dialog-title">${id ? 'Editar acceso' : 'Nuevo acceso'}</h2><p class="menu-dialog-intro">Elige cómo aparecerá en el menú y en tus favoritas.</p>
            <form id="menu-editor-form" data-menu-id="${id}"><div class="menu-form-grid">
                <label class="menu-field full">Nombre del acceso<input name="menu_item_name" required maxlength="255" value="${escape(row.menu_item_name)}" autocomplete="off"></label>
                <label class="menu-field full">Enlace<input name="menu_url" required maxlength="255" placeholder="apps/mi-app/index.php" value="${escape(row.menu_url)}" autocomplete="off"><small>Ruta de Guagua o enlace que empiece por https://.</small></label>
                <label class="menu-field full">Descripción<textarea name="menu_description" maxlength="10000" rows="2">${escape(row.menu_description)}</textarea></label>
                <label class="menu-field">Categoría del menú<input name="categoria" list="menu-category-options" maxlength="255" value="${escape(row.categoria)}" autocomplete="off"><datalist id="menu-category-options">${cat.map(value => `<option value="${escape(value)}"></option>`).join('')}</datalist></label>
                <label class="menu-field">Abrir en<select name="url_target"><option value="_self" ${row.url_target !== '_blank' ? 'selected' : ''}>La misma pestaña</option><option value="_blank" ${row.url_target === '_blank' ? 'selected' : ''}>Una pestaña nueva</option></select></label>
                <div class="menu-field full"><label for="menu-icon-input">Icono</label><div class="menu-icon-input"><span id="menu-icon-preview" class="menu-entry-icon" aria-hidden="true">${icon(row.icono)}</span><input id="menu-icon-input" name="icono" maxlength="25" value="${escape(row.icono || '📁')}" autocomplete="off"></div><details class="menu-icon-catalog"><summary>Elegir un icono</summary><div class="menu-icon-options">${icons.map(value => `<button type="button" data-menu-icon="${escape(value)}" aria-label="Usar icono ${escape(state.icons.find(item => item.valor === value)?.nombre || value)}" title="${escape(value)}">${icon(value)}</button>`).join('')}</div></details></div>
                <label class="menu-check full"><input name="fav" type="checkbox" ${row.fav ? 'checked' : ''}><span>Mostrar en Mis favoritas</span></label>
                <label class="menu-check full"><input name="is_cabecera" type="checkbox" ${row.is_cabecera ? 'checked' : ''}><span>Anclar a la cabecera de Guagua<small>También se marcará como favorita para que aparezca en la barra.</small></span></label>
                <details class="menu-advanced full"><summary>Más opciones</summary><label class="menu-field">Acceso superior<select name="menu_parent_id"><option value="">Sin acceso superior</option>${state.rows.filter(item => item.menu_item_id !== id).map(item => `<option value="${item.menu_item_id}" ${item.menu_item_id === row.menu_parent_id ? 'selected' : ''}>${escape(item.menu_item_name)}</option>`).join('')}</select></label></details>
            </div><div class="dialog-actions menu-editor-actions">${id ? `<button type="button" class="menu-remove-button" data-menu-remove="${id}">Quitar acceso</button>` : ''}<button type="button" class="secondary-button" data-menu-close>Cancelar</button><button type="submit" class="primary-button">Guardar acceso</button></div></form>`;
        if (!dialog.open) dialog.showModal();
    }

    async function editApp(app) {
        try {
            await ensureLoaded();
            const row = state.rows.find(item => item.menu_item_id === app.menu_id);
            if (!row && app.estado !== 'instalada') { store.toast('Reinstala esta app antes de añadir un acceso nuevo.'); return; }
            editor(row || { menu_item_name: app.nombre, menu_description: app.descripcion, menu_url: app.ruta, categoria: app.categoria, icono: app.icono, fav: false });
        } catch (cause) { store.toast(cause.message); }
    }

    function confirmDelete(id) {
        const row = state.rows.find(item => item.menu_item_id === Number(id));
        if (!row) return;
        body.innerHTML = `<h2 id="menu-dialog-title">Quitar acceso</h2><p class="detail-description">¿Quieres quitar «${escape(row.menu_item_name)}» del menú?</p><div class="detail-note">La aplicación y sus datos seguirán instalados. Solo se quitará este enlace del menú y de las favoritas.</div><div class="dialog-actions"><button type="button" class="secondary-button" data-menu-edit="${row.menu_item_id}">Cancelar</button><button type="button" class="danger-button" data-menu-delete="${row.menu_item_id}">Quitar acceso</button></div>`;
    }

    function syncDialog() {
        const apps = store.apps().filter(app => app.estado === 'instalada' && !app.menu_id);
        body.innerHTML = `<h2 id="menu-dialog-title">Añadir apps al menú</h2><p class="menu-dialog-intro">Elige las apps instaladas que aún no tienen un acceso. No se duplicarán las existentes.</p>${apps.length ? `<form id="menu-sync-form"><label class="menu-check menu-sync-all"><input type="checkbox" id="menu-sync-all">Seleccionar todas</label><div class="menu-sync-options">${apps.map(app => `<label class="menu-check"><input name="apps" type="checkbox" value="${app.id}"><span>${escape(app.icono)} ${escape(app.nombre)}<small>${escape(app.categoria)}</small></span></label>`).join('')}</div><p class="menu-dialog-intro">Se añadirán sin marcar como favoritas. Podrás elegir sus favoritos y accesos de cabecera después.</p><div class="dialog-actions"><button type="button" class="secondary-button" data-menu-close>Cancelar</button><button type="submit" class="primary-button">Añadir seleccionadas</button></div></form>` : '<div class="detail-note">Todas tus apps instaladas ya tienen un acceso en el menú.</div><div class="dialog-actions"><button type="button" class="secondary-button" data-menu-close>Cerrar</button></div>'}`;
        if (!dialog.open) dialog.showModal();
    }

    document.addEventListener('click', async event => {
        const button = event.target.closest('[data-menu-edit],[data-menu-flag],[data-menu-remove],[data-menu-delete],[data-menu-close],[data-menu-page],[data-menu-icon],[data-menu-retry]');
        if (!button || state.busy) return;
        if (button.hasAttribute('data-menu-edit')) {
            const row = state.rows.find(item => item.menu_item_id === Number(button.dataset.menuEdit));
            if (row) editor(row);
        } else if (button.dataset.menuFlag) {
            const row = state.rows.find(item => item.menu_item_id === Number(button.dataset.menuId));
            if (row) await mutate('menu_flag', row.menu_item_id, { field: button.dataset.menuFlag, value: !row[button.dataset.menuFlag] });
        } else if (button.dataset.menuRemove) confirmDelete(button.dataset.menuRemove);
        else if (button.dataset.menuDelete) await mutate('menu_delete', button.dataset.menuDelete);
        else if (button.hasAttribute('data-menu-close')) dialog.close();
        else if (button.hasAttribute('data-menu-page')) { state.page = Number(button.dataset.menuPage); render(); $('#menu-workspace').scrollIntoView({ block:'start' }); }
        else if (button.dataset.menuIcon) { $('#menu-icon-input').value = button.dataset.menuIcon; $('#menu-icon-preview').innerHTML = icon(button.dataset.menuIcon); }
        else if (button.hasAttribute('data-menu-retry')) await show(state.query);
    });
    document.addEventListener('submit', async event => {
        if (event.target.id === 'menu-editor-form') {
            event.preventDefault();
            const form = event.target;
            const input = Object.fromEntries(new FormData(form));
            input.fav = form.elements.fav.checked; input.is_cabecera = form.elements.is_cabecera.checked;
            await mutate('menu_save', form.dataset.menuId, { item: input });
        } else if (event.target.id === 'menu-sync-form') {
            event.preventDefault();
            const ids = new FormData(event.target).getAll('apps');
            if (!ids.length) { error('Selecciona al menos una aplicación.'); return; }
            await mutate('menu_sync', '', { ids });
        }
    });
    document.addEventListener('input', event => { if (event.target.id === 'menu-icon-input') $('#menu-icon-preview').innerHTML = icon(event.target.value); });
    document.addEventListener('change', event => {
        if (event.target.id === 'menu-sync-all') dialog.querySelectorAll('input[name="apps"]').forEach(box => { box.checked = event.target.checked; });
        if (event.target.name === 'is_cabecera' && event.target.checked && $('#menu-editor-form')) $('#menu-editor-form').elements.fav.checked = true;
        if (event.target.name === 'fav' && !event.target.checked && $('#menu-editor-form')) $('#menu-editor-form').elements.is_cabecera.checked = false;
    });
    $('#menu-category').addEventListener('change', () => { state.page = 1; render(); });
    $('#menu-filter').addEventListener('change', () => { state.page = 1; render(); });
    $('#menu-new').addEventListener('click', async () => { try { await ensureLoaded(); editor(); } catch (cause) { store.toast(cause.message); } });
    $('#menu-sync').addEventListener('click', syncDialog);
    $('#menu-dialog-close').addEventListener('click', () => { if (!state.busy) dialog.close(); });
    dialog.addEventListener('cancel', event => { if (state.busy) event.preventDefault(); });
    window.guaguaStoreMenu = { show, editApp, busy: () => state.busy };
})();
