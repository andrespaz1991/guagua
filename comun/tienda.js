(() => {
    'use strict';
    const $ = selector => document.querySelector(selector);
    const base = document.body.dataset.base;
    const endpoint = `${base}/comun/tienda_api.php`;
    const csrf = $('meta[name="csrf-token"]').content;
    const content = $('#store-content');
    const dialog = $('#app-dialog');
    const detail = $('#dialog-content');
    const state = { apps: [], view: new URLSearchParams(location.search).get('vista') === 'menu' ? 'menu' : 'explorar', category: 'Todas', search: '', sort: 'name', busy: false, zip: true, manage: false };
    const favoriteCategory = 'Mis favoritas';
    const titles = { explorar: 'Aplicaciones', instaladas: 'Mis aplicaciones', disponibles: 'Disponibles para instalar', biblioteca: 'Mi biblioteca', menu: 'Gestionar menú' };
    const colors = ['#3e4b79', '#34626c', '#68505d', '#4b576b', '#5c507b', '#4b665f', '#6d5948'];
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
    const normalize = text => String(text).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es');
    const bytes = amount => {
        if (amount == null) return 'Calculando…';
        if (amount < 1024) return `${amount} B`;
        const unit = Math.min(Math.floor(Math.log(amount) / Math.log(1024)), 3);
        return `${(amount / (1024 ** unit)).toLocaleString('es', { maximumFractionDigits: 1 })} ${['B', 'KB', 'MB', 'GB'][unit]}`;
    };
    const icon = app => `<span class="app-icon" style="--app-bg:${colors[parseInt(app.id.slice(0, 2), 16) % colors.length]}" aria-hidden="true">${escape(app.icono)}</span>`;
    let toastTimer;
    let modalRequest = 0;

    async function request(action, id = '', mutation = false, payload = {}) {
        const response = await fetch(mutation ? endpoint : `${endpoint}?${new URLSearchParams({ action, id })}`, mutation ? {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
            body: JSON.stringify({ ...payload, action, id: String(id) }), credentials: 'same-origin', cache: 'no-store'
        } : { credentials: 'same-origin', cache: 'no-store' });
        const type = response.headers.get('content-type') || '';
        if (action === 'backup' && response.ok && type.includes('application/zip')) {
            const blob = await response.blob();
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = response.headers.get('content-disposition')?.match(/filename="([^"]+)"/)?.[1] || `guagua-${id}.zip`;
            document.body.append(link); link.click(); link.remove();
            setTimeout(() => URL.revokeObjectURL(url), 60000);
            return {};
        }
        const data = type.includes('application/json') ? await response.json() : {};
        if (!response.ok || data.status !== 'success') throw new Error(data.message || 'No se pudo conectar con la tienda. Actualiza la página e inténtalo de nuevo.');
        return data;
    }

    function toast(message) {
        clearTimeout(toastTimer);
        $('#store-toast').textContent = message;
        $('#store-toast').hidden = false;
        toastTimer = setTimeout(() => { $('#store-toast').hidden = true; }, 9000);
    }

    function card(app) {
        const canEditMenu = state.manage && (app.estado === 'instalada' || app.menu_id);
        const menuAction = canEditMenu ? `<button type="button" class="app-action edit-menu" data-configure-menu="${app.id}" aria-label="Configurar acceso de ${escape(app.nombre)}"><span aria-hidden="true">✎</span> Editar menú</button>` : '';
        const appIcon = canEditMenu ? `<button type="button" class="app-icon-trigger" data-configure-menu="${app.id}" aria-label="Editar menú de ${escape(app.nombre)}" title="Editar menú de ${escape(app.nombre)}">${icon(app)}<span class="app-icon-edit-mark" aria-hidden="true">✎</span></button>` : icon(app);
        let actions;
        if (app.estado === 'disponible') {
            actions = state.manage
                ? `<button type="button" class="app-action install" data-install="${app.id}" aria-label="Instalar ${escape(app.nombre)}" ${!state.zip || app.reinstalable === false ? 'disabled' : ''}>Instalar</button>`
                : `<button type="button" class="app-action" data-app="${app.id}">Ver detalles</button>`;
            actions += menuAction;
        } else {
            actions = `<a class="app-action app-open" href="${escape(base)}/${escape(app.ruta)}" aria-label="Abrir ${escape(app.nombre)}">Abrir</a>` + menuAction;
            if (app.protegida) actions += `<button type="button" class="app-action protected" data-app="${app.id}" aria-label="Por qué está protegida ${escape(app.nombre)}">Protegida</button>`;
            else if (state.manage) actions += `<button type="button" class="app-action uninstall" data-uninstall="${app.id}" aria-label="Desinstalar ${escape(app.nombre)}" ${!state.zip ? 'disabled' : ''}>Desinstalar</button>`;
        }
        return `<article class="app-card">${appIcon}<div class="app-info"><button class="app-title" data-app="${app.id}" type="button">${escape(app.nombre)}</button><p class="app-category">${escape(app.categoria)}</p><div class="app-actions">${actions}</div></div></article>`;
    }

    function empty(title, description) {
        return `<div class="empty-state"><span class="empty-icon" aria-hidden="true">▦</span><h2>${escape(title)}</h2><p>${escape(description)}</p><button type="button" class="plain-button" data-reset>Explorar aplicaciones</button></div>`;
    }

    function render() {
        const menuView = state.view === 'menu';
        $('#store-manage-menu').hidden = menuView;
        $('#menu-workspace').hidden = !menuView;
        $('.store-toolbar').hidden = menuView;
        content.hidden = menuView;
        $('#store-search').placeholder = menuView ? 'Buscar accesos, categorías o enlaces del menú' : 'Buscar aplicaciones para tu Guagua';
        $('#store-search').setAttribute('aria-label', menuView ? 'Buscar accesos del menú' : 'Buscar aplicaciones');
        $('#view-title').textContent = titles[state.view];
        $('#view-help').hidden = !['instaladas', 'disponibles'].includes(state.view);
        $('#view-help').textContent = state.view === 'instaladas'
            ? 'Pulsa Desinstalar en la app que quieras retirar. Podrás descargar una copia de seguridad antes.'
            : 'Pulsa Instalar para recuperar una aplicación y sus datos locales.';
        const discovery = state.view === 'explorar' && !state.search && state.category === 'Todas';
        $('#store-hero').hidden = !discovery;
        $('#library-summary').hidden = state.view !== 'biblioteca';
        const installed = state.apps.filter(app => app.estado === 'instalada');
        const available = state.apps.filter(app => app.estado === 'disponible');
        const saved = available.reduce((total, app) => total + (app.limpieza_pendiente ? 0 : Math.max(0, (app.bytes || 0) - (app.paquete_bytes || 0))), 0);
        $('#library-summary').innerHTML = `<div class="summary-card"><strong>${installed.length}</strong><span>Apps instaladas</span></div><div class="summary-card"><strong>${available.length}</strong><span>Listas para reinstalar</span></div><div class="summary-card"><strong>${bytes(saved)}</strong><span>Ahorro por compresión estimado</span></div>`;
        document.querySelectorAll('.store-sidebar [data-view]').forEach(button => {
            button.classList.toggle('active', button.dataset.view === state.view);
            if (button.dataset.view === state.view) button.setAttribute('aria-current', 'page');
            else button.removeAttribute('aria-current');
        });
        if (menuView) { window.guaguaStoreMenu?.show(state.search); return; }
        const categories = ['Todas', favoriteCategory, ...new Set(state.apps.map(app => app.categoria).filter(category => category !== favoriteCategory).sort((a, b) => a.localeCompare(b, 'es')))];
        $('#category-filters').innerHTML = categories.map(category => `<button type="button" data-category="${escape(category)}" class="${category === state.category ? 'active' : ''}" aria-pressed="${category === state.category}">${category === favoriteCategory ? '<span class="favorite-category-star" aria-hidden="true">★</span> ' : ''}${escape(category)}</button>`).join('');
        const apps = state.apps.filter(app => {
            if (state.view === 'instaladas' && app.estado !== 'instalada') return false;
            if (state.view === 'disponibles' && app.estado !== 'disponible') return false;
            if (state.category === favoriteCategory) {
                if (!app.favorita) return false;
            } else if (state.category !== 'Todas' && app.categoria !== state.category) return false;
            return normalize(`${app.nombre} ${app.descripcion} ${app.categoria}`).includes(normalize(state.search));
        }).sort((a, b) => {
            if (state.sort === 'category') return a.categoria.localeCompare(b.categoria, 'es') || a.nombre.localeCompare(b.nombre, 'es');
            if (state.sort === 'state') return a.estado.localeCompare(b.estado) || a.nombre.localeCompare(b.nombre, 'es');
            return a.nombre.localeCompare(b.nombre, 'es');
        });
        if (!apps.length) {
            content.innerHTML = state.category === favoriteCategory
                ? empty('No hay favoritas en esta vista', state.search ? 'No hay aplicaciones favoritas que coincidan con tu búsqueda.' : 'Las aplicaciones marcadas como favoritas en el menú aparecerán aquí cuando coincidan con esta vista.')
                : state.view === 'disponibles' && !state.search && state.category === 'Todas'
                ? empty('Todo está en tu Guagua', 'Cuando desinstales una app, aparecerá aquí para que puedas recuperarla en el futuro.')
                : empty('No encontramos aplicaciones', 'Prueba otro nombre o explora todas las categorías.');
        } else if (discovery) {
            const learning = apps.filter(app => ['Educación', 'Juegos', 'Gestión escolar'].includes(app.categoria)).slice(0, 6);
            const tools = apps.filter(app => !['Educación', 'Juegos', 'Gestión escolar'].includes(app.categoria)).slice(0, 6);
            const collection = (title, items) => `<section class="collection"><header class="section-heading"><h2>${title}</h2><span>${items.length} aplicaciones</span></header><div class="collection-list">${items.map(card).join('')}</div></section>`;
            content.innerHTML = `<div class="collections">${collection('Herramientas para tu día a día', tools)}${collection('Aprender y enseñar', learning)}</div><header class="results-heading"><h2>Explora todas las aplicaciones</h2><span>${apps.length} en tu catálogo</span></header><div class="app-grid">${apps.map(card).join('')}</div>`;
        } else {
            content.innerHTML = `<header class="results-heading"><h2>${state.search ? `Resultados para “${escape(state.search)}”` : escape(state.category === 'Todas' ? titles[state.view] : state.category)}</h2><span>${apps.length} ${apps.length === 1 ? 'aplicación' : 'aplicaciones'}</span></header><div class="app-grid">${apps.map(card).join('')}</div>`;
        }
        content.setAttribute('aria-busy', 'false');
    }

    async function load() {
        const data = await request('catalog');
        state.apps = data.apps; state.zip = data.zip; state.manage = data.manage;
        $('#store-status').textContent = !data.manage ? 'Tu perfil puede consultar el catálogo, pero no administrar las aplicaciones.' : data.zip ? '' : 'La administración de aplicaciones requiere activar la extensión ZIP de PHP en el servidor.';
        render();
    }

    function modalError(message) {
        let error = $('#dialog-error');
        if (!error) { error = document.createElement('p'); error.id = 'dialog-error'; error.className = 'dialog-error'; error.setAttribute('role', 'alert'); detail.append(error); }
        error.textContent = message;
    }

    function busy(value, message = '') {
        state.busy = value;
        dialog.querySelectorAll('button, input').forEach(element => { element.disabled = value; });
        let progress = $('#dialog-progress');
        if (!progress) { progress = document.createElement('p'); progress.id = 'dialog-progress'; progress.className = 'dialog-progress'; progress.setAttribute('role', 'status'); detail.append(progress); }
        progress.textContent = message;
    }

    async function showApp(id) {
        const app = state.apps.find(item => item.id === id);
        if (!app || state.busy) return;
        const current = ++modalRequest;
        detail.innerHTML = `<header class="detail-heading">${icon(app)}<div><h2 id="dialog-title">${escape(app.nombre)}</h2><p>${escape(app.categoria)}</p></div></header><p class="detail-description">${escape(app.descripcion)}</p><div class="detail-facts"><div><strong>${app.estado === 'instalada' ? 'Instalada' : 'Disponible'}</strong><small>Estado</small></div><div><strong id="detail-size">${bytes(app.bytes)}</strong><small>${app.estado === 'instalada' ? 'Archivos en disco' : 'Tamaño original'}</small></div>${app.estado === 'disponible' ? `<div><strong>${bytes(app.paquete_bytes)}</strong><small>Paquete comprimido</small></div>` : ''}</div>
            ${app.protegida ? `<div class="detail-note"><strong>Componente protegido</strong>${escape(app.protegida)}</div>` : app.estado === 'disponible' ? '<div class="detail-note"><strong>Lista para volver</strong>La instalación recupera los archivos y datos locales guardados en el paquete. Las bases compartidas permanecen en Guagua.</div>' : '<div class="detail-note"><strong>Tú decides qué conservar instalado</strong>Al desinstalar, la app sale del menú y su carpeta se guarda comprimida para reinstalarla después.</div>'}
            ${!state.manage ? '<div class="detail-note">Tu perfil no dispone de permisos para administrar la instalación de esta app.</div>' : ''}${state.manage ? `<button type="button" class="app-menu-link" data-configure-menu="${id}">⚙ Configurar acceso en el menú</button>` : ''}<div class="dialog-actions">${app.estado === 'instalada' ? `${!app.protegida && state.manage ? `<button type="button" class="secondary-button" data-uninstall="${id}" ${!state.zip ? 'disabled' : ''}>Desinstalar</button>` : ''}<a class="primary-button" href="${escape(base)}/${escape(app.ruta)}">Abrir aplicación</a>` : state.manage ? `<button type="button" class="primary-button" data-install="${id}" ${!state.zip || app.reinstalable === false ? 'disabled' : ''}>Instalar aplicación</button>` : ''}</div>${app.reinstalable === false ? '<p class="dialog-error">El paquete no está disponible en el servidor. Revisa el almacenamiento de la tienda.</p>' : ''}`;
        if (!dialog.open) dialog.showModal();
        if (app.estado === 'instalada') {
            try {
                const data = await request('details', id);
                if (modalRequest === current && dialog.open && $('#detail-size')) $('#detail-size').textContent = bytes(data.app.bytes);
            } catch (error) { if (modalRequest === current && dialog.open) modalError(error.message); }
        }
    }

    function confirmUninstall(id) {
        const app = state.apps.find(item => item.id === id);
        if (!app || app.estado !== 'instalada' || app.protegida || state.busy || !state.manage || !state.zip) return;
        ++modalRequest;
        detail.innerHTML = `<header class="detail-heading">${icon(app)}<div><h2 id="dialog-title">Desinstalar ${escape(app.nombre)}</h2><p>Se retirará para todos los usuarios de esta instalación.</p></div></header><p class="detail-description">¿Quieres descargar una copia antes de desinstalar?</p><label class="backup-choice"><input type="checkbox" id="download-backup" checked><span>Descargar una copia de seguridad<small>ZIP con los archivos de esta app y una copia de la base Guagua completa${app.carpeta.toLowerCase() === 'edutask' ? ' y de EduTask' : ''}. Puede incluir datos de otros módulos. La copia se prepara antes de retirar la app.</small></span></label><div class="detail-note"><strong>Tus datos quedan a salvo en Guagua</strong>Podrás reinstalar la app y recuperar sus archivos locales desde la tienda. No se borran tablas ni bases compartidas. No se incluyen archivos externos a su carpeta ni datos guardados solo en el navegador.</div><div class="dialog-actions"><button type="button" class="secondary-button" data-close>Cancelar</button><button type="button" class="danger-button" id="confirm-uninstall" data-confirm="${id}">Descargar copia y desinstalar</button></div>`;
        if (!dialog.open) dialog.showModal();
    }

    async function mutate(action, id) {
        if (state.busy || !state.manage) return;
        $('#dialog-error')?.remove();
        const backup = action === 'uninstall' && $('#download-backup')?.checked;
        busy(true, backup ? 'Preparando la copia de seguridad. La app sigue instalada…' : action === 'install' ? 'Verificando y restaurando los archivos…' : 'Comprimiendo y verificando los archivos…');
        let completed = false;
        try {
            if (backup) {
                await request('backup', id, true);
                $('#dialog-progress').textContent = 'Copia preparada y descarga solicitada. Retirando la aplicación…';
            }
            const result = await request(action, id, true);
            completed = true;
            busy(false); dialog.close(); ++modalRequest;
            toast(result.message);
            await load();
        } catch (error) {
            if (completed) {
                $('#store-status').textContent = `La operación terminó, pero el catálogo no se pudo actualizar: ${error.message}`;
            } else {
                modalError(`${error.message}${backup ? ' Si falló la copia, la aplicación sigue instalada.' : ''}`);
            }
        } finally { busy(false); }
    }

    document.addEventListener('click', event => {
        const button = event.target.closest('[data-view],[data-category],[data-app],[data-reset],[data-uninstall],[data-install],[data-confirm],[data-close],[data-configure-menu]');
        if (!button || state.busy || window.guaguaStoreMenu?.busy()) return;
        if (button.dataset.view) {
            setView(button.dataset.view);
        } else if (button.hasAttribute('data-reset')) {
            state.view = 'explorar'; state.category = 'Todas'; state.search = ''; $('#store-search').value = ''; render();
        } else if (button.dataset.category) {
            state.category = button.dataset.category; render();
            [...$('#category-filters').children].find(item => item.dataset.category === state.category)?.scrollIntoView({ block: 'nearest', inline: 'nearest' });
        } else if (button.dataset.app) showApp(button.dataset.app);
        else if (button.dataset.uninstall) confirmUninstall(button.dataset.uninstall);
        else if (button.dataset.install) {
            const app = state.apps.find(item => item.id === button.dataset.install);
            if (!app || !state.manage || !state.zip || app.reinstalable === false) return;
            if (!dialog.open) showApp(app.id);
            mutate('install', app.id);
        }
        else if (button.dataset.confirm) mutate('uninstall', button.dataset.confirm);
        else if (button.hasAttribute('data-close')) dialog.close();
        else if (button.dataset.configureMenu) {
            const app = state.apps.find(item => item.id === button.dataset.configureMenu);
            if (app && state.manage) { if (dialog.open) dialog.close(); window.guaguaStoreMenu?.editApp(app); }
        }
    });
    $('#store-search').addEventListener('input', event => { state.search = event.target.value.trim(); render(); });
    $('#store-sort').addEventListener('change', event => { state.sort = event.target.value; render(); });
    dialog.addEventListener('change', event => { if (event.target.id === 'download-backup') $('#confirm-uninstall').textContent = event.target.checked ? 'Descargar copia y desinstalar' : 'Desinstalar sin descargar copia'; });
    dialog.addEventListener('cancel', event => { if (state.busy) event.preventDefault(); else ++modalRequest; });
    dialog.addEventListener('close', () => { ++modalRequest; });
    $('.dialog-close').addEventListener('click', () => { if (!state.busy) dialog.close(); });
    document.addEventListener('keydown', event => { if (event.key === '/' && !dialog.open && !$('#menu-dialog').open && !['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName)) { event.preventDefault(); $('#store-search').focus(); } });
    function setView(view) {
        if (!titles[view]) return;
        state.view = view; state.category = 'Todas'; state.search = ''; $('#store-search').value = '';
        const url = new URL(location.href);
        if (view === 'menu') url.searchParams.set('vista', 'menu');
        else url.searchParams.delete('vista');
        url.searchParams.delete('editar'); history.replaceState(null, '', url);
        render(); window.scrollTo({ top: 0 });
    }
    window.guaguaStore = { request, toast, reload: load, apps: () => state.apps, setView, canManage: () => state.manage };
    load().catch(error => {
        content.setAttribute('aria-busy', 'false');
        content.innerHTML = `<div class="empty-state"><h2>No se pudo cargar la tienda</h2><p>${escape(error.message)}</p><button type="button" class="plain-button" id="retry-load">Volver a intentar</button></div>`;
        $('#retry-load').addEventListener('click', () => location.reload());
    });
})();
