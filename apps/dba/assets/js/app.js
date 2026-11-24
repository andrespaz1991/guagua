document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    // Theme toggle
    const themeToggleBtn = document.getElementById('theme-toggle');
    const htmlEl = document.documentElement;
    const icon = themeToggleBtn.querySelector('i');
    
    let currentTheme = localStorage.getItem('dba_theme') || 'light';
    setTheme(currentTheme);

    themeToggleBtn.addEventListener('click', () => {
        currentTheme = currentTheme === 'light' ? 'dark' : 'light';
        setTheme(currentTheme);
    });

    function setTheme(theme) {
        htmlEl.setAttribute('data-theme', theme);
        localStorage.setItem('dba_theme', theme);
        if(theme === 'dark') {
            icon.className = 'fa-solid fa-sun';
        } else {
            icon.className = 'fa-solid fa-moon';
        }
    }

    // Data State
    let dbData = { grados: [], materias: [], estandares: [], dbas: [], ejes_tematicos: [] };
    let currentState = {
        gradoId: null,
        gradoName: '',
        materiaId: null,
        materiaName: ''
    };

    // Modal state
    let activeModalParams = {}; // { action: 'create'|'edit', type: 'estandar', period: 1, estandarId: 2, dbaId: null, name: '...' }

    const selectGrado = document.getElementById('select-grado');
    const selectMateria = document.getElementById('select-materia');
    const periodsContainer = document.getElementById('periods-container');
    const accordionToolbar = document.getElementById('accordion-toolbar');
    const viewTitle = document.getElementById('view-title');
    const viewSubtitle = document.getElementById('view-subtitle');
    const operationStatus = document.getElementById('operation-status');
    const editorView = document.getElementById('editor-view');
    const pendientesView = document.getElementById('pendientes-view');
    const importarView = document.getElementById('importar-view');
    const tabEditor = document.getElementById('tab-editor');
    const tabPendientes = document.getElementById('tab-pendientes');
    const tabImportar = document.getElementById('tab-importar');
    const reporteGrado = document.getElementById('reporte-grado');
    const reporteMateria = document.getElementById('reporte-materia');
    const reportePeriodo = document.getElementById('reporte-periodo');
    const reporteMostrarCompletos = document.getElementById('reporte-mostrar-completos');
    const auditSummary = document.getElementById('audit-summary');
    const auditTableBody = document.getElementById('audit-table-body');
    const importFile = document.getElementById('import-file');
    const importFileName = document.getElementById('import-file-name');
    const importButton = document.getElementById('btn-importar');
    const importResult = document.getElementById('import-result');
    let activeView = 'editor';
    const accordionState = new Map();

    function accordionKey(type, id) {
        return `${currentState.gradoId}:${currentState.materiaId}:${dbData.periodo_actual || 0}:${type}:${id}`;
    }

    function accordionAttributes(type, id) {
        const key = accordionKey(type, id);
        return `data-accordion-key="${escapeHtml(key)}"${accordionState.get(key) ? ' open' : ''}`;
    }

    function accordionHeading(kind, title, titleClass = '') {
        return `<span class="accordion-heading"><i class="fa-solid fa-chevron-right accordion-chevron" aria-hidden="true"></i><span class="accordion-label ${titleClass}"><span class="item-kind">${escapeHtml(kind)}</span><span class="accordion-text" title="${escapeHtml(title)}">${escapeHtml(title)}</span></span></span>`;
    }

    editorView.addEventListener('click', event => {
        const button = event.target.closest('[data-accordion-action]');
        if (!button) return;
        const expanded = button.dataset.accordionAction === 'expand';
        const period = button.closest('.period-block');
        const scope = period || periodsContainer;
        // Al contraer un periodo, su cabecera y controles continúan disponibles.
        if (period) {
            period.open = true;
            accordionState.set(period.dataset.accordionKey, true);
        }
        scope.querySelectorAll('details[data-accordion-key]').forEach(details => {
            details.open = expanded;
            accordionState.set(details.dataset.accordionKey, expanded);
        });
    });

    // Init
    fetchData();

    async function fetchData() {
        try {
            const res = await fetch('api/get_data.php');
            const result = await res.json();
            if (!res.ok || result.status !== 'success') throw new Error(result.message || 'No fue posible cargar los datos.');

            dbData = result.data;
            initSelects();
            document.dispatchEvent(new CustomEvent('dba:data'));
            if (activeView === 'pendientes') {
                renderAuditReport();
            } else if (activeView === 'editor') {
                renderPeriods();
            }
            return true;
        } catch (e) {
            console.error(e);
            showOperation(`Error al cargar los datos: ${e.message}`, 'error');
            return false;
        }
    }

    function initSelects() {
        selectGrado.innerHTML = '<option value="">Seleccione un grado...</option>';
        dbData.grados.forEach(g => {
            const opt = document.createElement('option');
            opt.value = g.id_grado;
            opt.textContent = g.nombre;
            selectGrado.appendChild(opt);
        });

        selectMateria.innerHTML = '<option value="">Seleccione una materia...</option>';
        dbData.materias.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id_materia;
            opt.textContent = m.nombre_materia;
            selectMateria.appendChild(opt);
        });

        if (currentState.gradoId) selectGrado.value = currentState.gradoId;
        if (currentState.materiaId) selectMateria.value = currentState.materiaId;

        initAuditFilters();
    }

    function initAuditFilters() {
        const gradoSeleccionado = reporteGrado.value;
        const materiaSeleccionada = reporteMateria.value;

        reporteGrado.innerHTML = '<option value="">Todos los grados</option>';
        dbData.grados.forEach(grado => {
            const option = document.createElement('option');
            option.value = grado.id_grado;
            option.textContent = `Grado ${grado.nombre}`;
            reporteGrado.appendChild(option);
        });
        reporteGrado.value = gradoSeleccionado;

        reporteMateria.innerHTML = '<option value="">Todas las materias</option>';
        dbData.materias.forEach(materia => {
            const option = document.createElement('option');
            option.value = materia.id_materia;
            option.textContent = materia.nombre_materia;
            reporteMateria.appendChild(option);
        });
        reporteMateria.value = materiaSeleccionada;
    }

    selectGrado.addEventListener('change', (e) => {
        currentState.gradoId = parseInt(e.target.value);
        currentState.gradoName = e.target.options[e.target.selectedIndex].text;
        if (activeView === 'editor') renderPeriods();
        document.dispatchEvent(new CustomEvent('dba:filters'));
    });

    selectMateria.addEventListener('change', (e) => {
        currentState.materiaId = parseInt(e.target.value);
        currentState.materiaName = e.target.options[e.target.selectedIndex].text;
        if (activeView === 'editor') renderPeriods();
        document.dispatchEvent(new CustomEvent('dba:filters'));
    });

    // Helper to safely escape HTML attributes
    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function renderDescription(text) {
        const description = String(text || '').trim();
        return description ? `<div class="item-description">${escapeHtml(description)}</div>` : '';
    }

    function findRecord(type, id) {
        const collections = {
            estandar: dbData.estandares,
            dba: dbData.dbas,
            eje: dbData.ejes_tematicos
        };
        const idFields = {
            estandar: 'id_estandar',
            dba: 'id_dba',
            eje: 'id_eje_tematico'
        };
        return (collections[type] || []).find(item => String(item[idFields[type]]) === String(id)) || null;
    }

    function showOperation(message, type = '') {
        document.dispatchEvent(new CustomEvent('dba:message', { detail: { message, type } }));
        const modalBody = document.querySelector('.modal-overlay:not(.hidden) .modal-body');
        if (modalBody) {
            let note = modalBody.querySelector('.modal-status');
            if (!note) { note = document.createElement('p'); note.className = 'modal-status'; note.setAttribute('role', 'status'); modalBody.appendChild(note); }
            note.textContent = message;
        }
        if (!operationStatus) return;
        operationStatus.textContent = message;
        operationStatus.className = `operation-status ${type}`.trim();
    }

    function renderPeriods() {
        periodsContainer.querySelectorAll('details[data-accordion-key]').forEach(details => {
            accordionState.set(details.dataset.accordionKey, details.open);
        });
        periodsContainer.innerHTML = '';
        if (accordionToolbar) accordionToolbar.classList.toggle('hidden', !currentState.gradoId || !currentState.materiaId);
        if (!currentState.gradoId || !currentState.materiaId) {
            periodsContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fa-solid fa-layer-group"></i>
                    <p>Seleccione un Grado y Materia</p>
                </div>
            `;
            viewTitle.textContent = 'Configuración Curricular';
            viewSubtitle.textContent = 'Seleccione Grado y Materia en el panel lateral.';
            return;
        }

        viewTitle.textContent = `Grado ${currentState.gradoName} - ${currentState.materiaName}`;
        const activePeriod = Number(dbData.periodo_actual) || null;
        viewSubtitle.textContent = activePeriod
            ? `Periodo ${activePeriod} vigente según las fechas configuradas. Despliegue los elementos para consultar o editar.`
            : 'No hay un periodo vigente según las fechas configuradas. Despliegue el periodo que desea consultar.';

        // Filtrar estándares de este grado y materia
        const estandaresList = dbData.estandares.filter(e => e.grado == currentState.gradoId && e.id_materia_oficial == currentState.materiaId);

        for (let periodNum = 1; periodNum <= 4; periodNum++) {
            const periodEstandares = estandaresList.filter(e => e.id_periodo == periodNum);
            
            const block = document.createElement('details');
            block.className = 'period-block';
            block.dataset.period = periodNum;
            block.dataset.accordionKey = accordionKey('periodo', periodNum);
            block.open = accordionState.has(block.dataset.accordionKey)
                ? accordionState.get(block.dataset.accordionKey) : periodNum === activePeriod;
            if (periodNum === activePeriod) block.classList.add('current-period');
            
            let estandaresHtml = '';
            if (periodEstandares.length === 0) {
                estandaresHtml = `<p class="empty-text">No hay estándares configurados para el Periodo ${periodNum}.</p>`;
            } else {
                periodEstandares.forEach(est => {
                    const dbasList = dbData.dbas.filter(d => d.id_estandar == est.id_estandar);
                    let dbasHtml = '';
                    
                    if (dbasList.length === 0) {
                        dbasHtml = `<p class="empty-text">Sin DBAs asociados.</p>`;
                    } else {
                        dbasList.forEach(dba => {
                            const ejesList = dbData.ejes_tematicos.filter(ej => ej.id_dba == dba.id_dba);
                            let ejesHtml = '';
                            if (ejesList.length === 0) {
                                ejesHtml = `<p class="empty-text">Sin Ejes Temáticos asociados.</p>`;
                            } else {
                                ejesList.forEach(eje => {
                                    ejesHtml += `
                                    <details class="eje-item" ${accordionAttributes('eje', eje.id_eje_tematico)}>
                                        <summary class="eje-header">
                                            ${accordionHeading('Eje temático', eje.nombre_eje_tematico, 'eje-title')}
                                        </summary>
                                        <div class="eje-body">
                                            <span class="actions-group">
                                                <button class="btn-icon btn-edit-eje" data-id="${eje.id_eje_tematico}" data-name="${escapeHtml(eje.nombre_eje_tematico)}" title="Editar Eje"><i class="fa-solid fa-pen"></i></button>
                                                <button class="btn-icon delete btn-del-eje" data-id="${eje.id_eje_tematico}" title="Borrar Eje"><i class="fa-solid fa-trash-can"></i></button>
                                            </span>
                                            ${renderDescription(eje.descripcion_eje_tematico) || '<p class="empty-text">Sin descripción adicional.</p>'}
                                        </div>
                                    </details>
                                    `;
                                });
                            }

                            const evidencias = (dbData.evidencias || []).filter(e => e.id_dba == dba.id_dba && String(e.descripcion_evidencia || '').trim());
                            const evidenciasHtml = evidencias.map(e => {
                                const text = String(e.descripcion_evidencia).trim();
                                const preview = Array.from(text).length > 140 ? Array.from(text).slice(0, 140).join('') + '…' : text;
                                return `<details class="ev-inline-item" ${accordionAttributes('evidencia', e.id_evidencia_aprendizaje)}>
                                    <summary class="evidence-header">${accordionHeading('Evidencia', preview)}</summary>
                                    <div class="evidence-body"><p>${escapeHtml(text)}</p><span class="ev-badge">${e.origen === 'ia_pendiente' ? 'IA · pendiente de revisión' : e.origen === 'ia_revisada' ? 'IA · revisada' : 'Registrada'}</span><button type="button" class="btn-icon" data-ev-edit="${e.id_evidencia_aprendizaje}" title="Editar evidencia"><i class="fa-solid fa-pen"></i></button></div>
                                </details>`;
                            }).join('');
                            dbasHtml += `
                                <details class="dba-card" ${accordionAttributes('dba', dba.id_dba)}>
                                    <summary class="dba-header">
                                        ${accordionHeading('DBA', dba.nombre_dba, 'dba-title')}
                                    </summary>
                                    <div class="dba-body">
                                        <span class="actions-group">
                                            <button class="btn-icon btn-edit-dba" data-id="${dba.id_dba}" data-name="${escapeHtml(dba.nombre_dba)}" title="Editar DBA"><i class="fa-solid fa-pen"></i></button>
                                            <button class="btn-icon btn-add-eje" data-dba="${dba.id_dba}" title="Añadir Eje Temático"><i class="fa-solid fa-plus"></i></button>
                                            <button class="btn-icon delete btn-del-dba" data-id="${dba.id_dba}" title="Borrar DBA (Cascada)"><i class="fa-solid fa-trash-can"></i></button>
                                        </span>
                                    ${renderDescription(dba.descripcion_dba)}
                                    <details class="curriculum-group" ${accordionAttributes('ejes', dba.id_dba)}>
                                        <summary class="group-header">${accordionHeading('Ejes temáticos', `${ejesList.length} ${ejesList.length === 1 ? 'registrado' : 'registrados'}`)}</summary>
                                        <div class="ejes-list">${ejesHtml}</div>
                                    </details>
                                    <div class="dba-learning">
                                        <div class="ev-tags">${String(dba.palabras_clave || '').split(',').filter(s => s.trim()).map(s => `<span>${escapeHtml(s.trim())}</span>`).join('')}</div>
                                        <details class="curriculum-group" ${accordionAttributes('evidencias', dba.id_dba)}>
                                            <summary class="group-header">${accordionHeading('Evidencias de aprendizaje', `${evidencias.length} ${evidencias.length === 1 ? 'registrada' : 'registradas'}`)}</summary>
                                            <div class="evidence-list"><div class="accordion-actions"><button type="button" class="btn btn-secondary" data-ev-add="${dba.id_dba}">Añadir evidencia</button></div>${evidenciasHtml || '<p class="empty-text">Sin evidencia. Añada una o genérela en la pestaña Evidencias de aprendizaje.</p>'}</div>
                                        </details>
                                    </div>
                                    </div>
                                </details>
                            `;
                        });
                    }

                    estandaresHtml += `
                        <details class="estandar-card" ${accordionAttributes('estandar', est.id_estandar)}>
                            <summary class="estandar-header">
                                ${accordionHeading('Estándar', est.nombre_estandar, 'estandar-title')}
                            </summary>
                            <div class="estandar-body">
                                <span class="actions-group">
                                    <button class="btn-icon btn-edit-est" data-id="${est.id_estandar}" data-name="${escapeHtml(est.nombre_estandar)}" title="Editar Estándar"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn-icon btn-add-dba" data-est="${est.id_estandar}" title="Añadir DBA"><i class="fa-solid fa-plus"></i></button>
                                    <button class="btn-icon delete btn-del-est" data-id="${est.id_estandar}" title="Borrar Estándar (Cascada)"><i class="fa-solid fa-trash-can"></i></button>
                                </span>
                                ${renderDescription(est.descripcion_estandar)}
                                ${dbasHtml}
                            </div>
                        </details>
                    `;
                });
            }

            const dates = (dbData.periodos || []).find(periodo => Number(periodo.numero_periodo ?? periodo.id_periodo) === periodNum);
            const dateLabel = dates ? `${dates.fecha_inicio.split('-').reverse().join('/')} – ${dates.fecha_fin.split('-').reverse().join('/')}` : '';
            block.innerHTML = `
                <summary class="period-header">
                    <span class="accordion-heading"><i class="fa-solid fa-chevron-right accordion-chevron" aria-hidden="true"></i><span class="accordion-label"><span class="period-label">Periodo ${periodNum}${periodNum === activePeriod ? '<span class="period-current-badge">Vigente</span>' : ''}</span><span class="period-meta">${periodEstandares.length} ${periodEstandares.length === 1 ? 'estándar' : 'estándares'}${dateLabel ? ' · ' + escapeHtml(dateLabel) : ''}</span></span></span>
                </summary>
                <div class="period-body" id="period-body-${periodNum}">
                    <div class="period-toolbar">
                        <div class="accordion-quick-actions" role="group" aria-label="Controles del periodo ${periodNum}">
                            <button type="button" class="btn btn-secondary" data-accordion-action="expand" aria-controls="period-body-${periodNum}" aria-label="Expandir todo del periodo ${periodNum}"><i class="fa-solid fa-angles-down" aria-hidden="true"></i> Expandir contenido</button>
                            <button type="button" class="btn btn-secondary" data-accordion-action="collapse" aria-controls="period-body-${periodNum}" aria-label="Contraer todo del periodo ${periodNum}"><i class="fa-solid fa-angles-up" aria-hidden="true"></i> Contraer contenido</button>
                        </div>
                        <button class="btn btn-secondary btn-add-est" data-period="${periodNum}"><i class="fa-solid fa-plus"></i> Añadir Estándar</button>
                    </div>
                    ${estandaresHtml}
                </div>
            `;

            periodsContainer.appendChild(block);
        }

        bindActionButtons();
        periodsContainer.querySelectorAll('details[data-accordion-key]').forEach(details => {
            details.addEventListener('toggle', () => {
                if (details.isConnected) accordionState.set(details.dataset.accordionKey, details.open);
            });
        });
    }

    function construirAuditoria() {
        const dbasPorEstandar = new Map();
        const ejesPorDba = new Map();

        dbData.dbas.forEach(dba => {
            const clave = String(dba.id_estandar);
            if (!dbasPorEstandar.has(clave)) dbasPorEstandar.set(clave, []);
            dbasPorEstandar.get(clave).push(dba);
        });
        dbData.ejes_tematicos.forEach(eje => {
            const clave = String(eje.id_dba);
            if (!ejesPorDba.has(clave)) ejesPorDba.set(clave, []);
            ejesPorDba.get(clave).push(eje);
        });

        const tieneNombre = (registro, campo) => String(registro[campo] || '').trim() !== '';
        const auditoria = [];

        dbData.grados.forEach(grado => {
            dbData.materias.forEach(materia => {
                for (let periodo = 1; periodo <= 4; periodo++) {
                    const estandares = dbData.estandares.filter(estandar =>
                        String(estandar.grado) === String(grado.id_grado)
                        && String(estandar.id_materia_oficial) === String(materia.id_materia)
                        && String(estandar.id_periodo) === String(periodo)
                    );
                    const dbas = estandares.flatMap(estandar => dbasPorEstandar.get(String(estandar.id_estandar)) || []);
                    const ejes = dbas.flatMap(dba => ejesPorDba.get(String(dba.id_dba)) || []);
                    const pendientes = [];

                    if (estandares.length === 0) {
                        pendientes.push('Estándares', 'DBA', 'Ejes temáticos');
                    } else {
                        const estandaresSinNombre = estandares.filter(estandar => !tieneNombre(estandar, 'nombre_estandar'));
                        const estandaresSinDba = estandares.filter(estandar => !(dbasPorEstandar.get(String(estandar.id_estandar)) || []).length);

                        if (estandaresSinNombre.length) pendientes.push(`${estandaresSinNombre.length} estándar(es) sin nombre`);
                        if (dbas.length === 0) {
                            pendientes.push('DBA');
                            pendientes.push('Ejes temáticos');
                        } else {
                            const dbasSinNombre = dbas.filter(dba => !tieneNombre(dba, 'nombre_dba'));
                            const dbasSinEje = dbas.filter(dba => !(ejesPorDba.get(String(dba.id_dba)) || []).length);

                            if (estandaresSinDba.length) pendientes.push(`${estandaresSinDba.length} estándar(es) sin DBA`);
                            if (dbasSinNombre.length) pendientes.push(`${dbasSinNombre.length} DBA sin nombre`);
                            if (ejes.length === 0) {
                                pendientes.push('Ejes temáticos');
                            } else {
                                if (dbasSinEje.length) pendientes.push(`${dbasSinEje.length} DBA sin eje temático`);
                                const ejesSinNombre = ejes.filter(eje => !tieneNombre(eje, 'nombre_eje_tematico'));
                                if (ejesSinNombre.length) pendientes.push(`${ejesSinNombre.length} eje(s) sin nombre`);
                            }
                        }
                    }

                    auditoria.push({
                        grado,
                        materia,
                        periodo,
                        estandares: estandares.length,
                        dbas: dbas.length,
                        ejes: ejes.length,
                        pendientes
                    });
                }
            });
        });

        return auditoria;
    }

    function renderAuditReport() {
        if (!auditTableBody || !auditSummary) return;

        const gradoFiltro = reporteGrado.value;
        const materiaFiltro = reporteMateria.value;
        const periodoFiltro = reportePeriodo.value;
        const mostrarCompletos = reporteMostrarCompletos.checked;
        const auditoriaFiltrada = construirAuditoria().filter(registro =>
            (!gradoFiltro || String(registro.grado.id_grado) === String(gradoFiltro))
            && (!materiaFiltro || String(registro.materia.id_materia) === String(materiaFiltro))
            && (!periodoFiltro || String(registro.periodo) === String(periodoFiltro))
        );
        const pendientes = auditoriaFiltrada.filter(registro => registro.pendientes.length > 0);
        const completos = auditoriaFiltrada.length - pendientes.length;
        const registrosVisibles = mostrarCompletos ? auditoriaFiltrada : pendientes;

        auditSummary.innerHTML = `
            <div class="audit-summary-card">
                <span class="label">Combinaciones revisadas</span>
                <strong class="value">${auditoriaFiltrada.length}</strong>
            </div>
            <div class="audit-summary-card pending">
                <span class="label">Con pendientes</span>
                <strong class="value">${pendientes.length}</strong>
            </div>
            <div class="audit-summary-card complete">
                <span class="label">Completas</span>
                <strong class="value">${completos}</strong>
            </div>
        `;

        if (registrosVisibles.length === 0) {
            auditTableBody.innerHTML = '<tr><td class="audit-empty" colspan="6">No hay pendientes con los filtros seleccionados.</td></tr>';
            return;
        }

        auditTableBody.innerHTML = registrosVisibles.map(registro => {
            const registrado = `Estándares: ${registro.estandares}<br>DBA: ${registro.dbas}<br>Ejes: ${registro.ejes}`;
            const detallePendientes = registro.pendientes.length
                ? `<div class="audit-pending-list">${registro.pendientes.map(pendiente => `<span class="audit-pending-tag">${escapeHtml(pendiente)}</span>`).join('')}</div>`
                : '<span class="audit-complete-tag">Completo</span>';
            return `
                <tr>
                    <td>Grado ${escapeHtml(registro.grado.nombre)}</td>
                    <td>${escapeHtml(registro.materia.nombre_materia)}</td>
                    <td>Período ${registro.periodo}</td>
                    <td><div class="audit-counts">${registrado}</div></td>
                    <td>${detallePendientes}</td>
                    <td><button type="button" class="btn btn-secondary btn-audit-open" data-grado="${registro.grado.id_grado}" data-materia="${registro.materia.id_materia}">Diligenciar</button></td>
                </tr>
            `;
        }).join('');

        auditTableBody.querySelectorAll('.btn-audit-open').forEach(button => {
            button.addEventListener('click', () => abrirEnEditor(button.dataset.grado, button.dataset.materia));
        });
    }

    function abrirEnEditor(gradoId, materiaId) {
        const grado = dbData.grados.find(item => String(item.id_grado) === String(gradoId));
        const materia = dbData.materias.find(item => String(item.id_materia) === String(materiaId));
        if (!grado || !materia) return;

        currentState.gradoId = Number(grado.id_grado);
        currentState.gradoName = grado.nombre;
        currentState.materiaId = Number(materia.id_materia);
        currentState.materiaName = materia.nombre_materia;
        selectGrado.value = currentState.gradoId;
        selectMateria.value = currentState.materiaId;
        switchView('editor');
        showOperation(`Abra y diligencie los períodos pendientes de Grado ${grado.nombre} - ${materia.nombre_materia}.`);
    }

    function switchView(view) {
        activeView = view;
        document.querySelectorAll('.view-tab').forEach(tab => { tab.tabIndex = tab.dataset.view === view ? 0 : -1; });
        const esEditor = view === 'editor';
        const esPendientes = view === 'pendientes';
        editorView.classList.toggle('hidden', !esEditor);
        pendientesView.classList.toggle('hidden', !esPendientes);
        importarView.classList.toggle('hidden', view !== 'importar');
        document.getElementById('exportar-view').classList.toggle('hidden', view !== 'exportar');
        document.getElementById('tab-exportar').classList.toggle('active', view === 'exportar');
        document.getElementById('tab-exportar').setAttribute('aria-selected', String(view === 'exportar'));
        document.getElementById('evidencias-view').classList.toggle('hidden', view !== 'evidencias');
        document.getElementById('tab-evidencias').classList.toggle('active', view === 'evidencias');
        document.getElementById('tab-evidencias').setAttribute('aria-selected', String(view === 'evidencias'));
        tabEditor.classList.toggle('active', esEditor);
        tabPendientes.classList.toggle('active', esPendientes);
        tabImportar.classList.toggle('active', view === 'importar');
        tabEditor.setAttribute('aria-selected', String(esEditor));
        tabPendientes.setAttribute('aria-selected', String(esPendientes));
        tabImportar.setAttribute('aria-selected', String(view === 'importar'));

        if (esEditor) {
            viewTitle.textContent = 'Configuración Curricular';
            viewSubtitle.textContent = 'Seleccione un Grado y Materia en el panel lateral para empezar.';
            renderPeriods();
        } else if (esPendientes) {
            viewTitle.textContent = 'Reporte de pendientes';
            viewSubtitle.textContent = 'Auditoría de estándares, DBA y ejes temáticos por grado, materia y período.';
            renderAuditReport();
        } else if (view === 'evidencias') {
            viewTitle.textContent = 'Evidencias de aprendizaje';
            viewSubtitle.textContent = 'Organice, importe y cree evidencias conectadas con su malla curricular.';
            document.dispatchEvent(new CustomEvent('dba:filters'));
        } else if (view === 'exportar') {
            viewTitle.textContent = 'Exportar malla curricular';
            viewSubtitle.textContent = 'Descargue la información por grado, materia y periodo en JSON, CSV o TXT.';
        } else {
            viewTitle.textContent = 'Importar DBA';
            viewSubtitle.textContent = 'Cargue una estructura curricular desde JSON, CSV o TXT.';
        }
    }

    tabEditor.addEventListener('click', () => switchView('editor'));
    tabPendientes.addEventListener('click', () => switchView('pendientes'));
    tabImportar.addEventListener('click', () => switchView('importar'));
    document.getElementById('tab-exportar').addEventListener('click', () => switchView('exportar'));
    document.getElementById('tab-evidencias').addEventListener('click', () => switchView('evidencias'));
    const tabNavigation = document.querySelector('.view-tabs');
    tabNavigation.setAttribute('role', 'tablist');
    document.querySelectorAll('.view-tab').forEach(tab => {
        tab.setAttribute('role', 'tab');
        tab.setAttribute('aria-controls', `${tab.dataset.view}-view`);
        tab.tabIndex = tab.dataset.view === activeView ? 0 : -1;
        const panel = document.getElementById(`${tab.dataset.view}-view`);
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', tab.id);
    });
    tabNavigation.addEventListener('keydown', event => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        const tabs = [...tabNavigation.querySelectorAll('.view-tab')];
        const index = tabs.indexOf(event.target);
        if (index < 0) return;
        event.preventDefault();
        const next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
        switchView(tabs[next].dataset.view);
        tabs[next].focus();
    });
    [reporteGrado, reporteMateria, reportePeriodo, reporteMostrarCompletos].forEach(control => {
        control.addEventListener('change', renderAuditReport);
    });

    importFile.addEventListener('change', () => {
        const file = importFile.files[0];
        importButton.disabled = !file;
        importFileName.textContent = file ? `${file.name} (${Math.ceil(file.size / 1024)} KB)` : 'Aún no ha seleccionado un archivo.';
        importResult.textContent = '';
        importResult.className = 'import-result';
    });

    importButton.addEventListener('click', async () => {
        const file = importFile.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('archivo', file);
        importButton.disabled = true;
        importButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importando…';
        importResult.textContent = '';

        try {
            const res = await fetch('api/import.php', { method: 'POST', headers: { 'X-CSRF-Token': csrfToken }, body: formData });
            const result = await res.json().catch(() => null);
            if (!res.ok || !result || result.status !== 'success') {
                throw new Error(result?.message || 'No fue posible importar el archivo.');
            }
            const creados = result.created || {};
            const omitidos = result.skipped || {};
            importResult.textContent = `${result.message} Registros procesados: ${result.records}. Creados: ${creados.estandares || 0} estándares, ${creados.dbas || 0} DBA y ${creados.ejes || 0} ejes. Omitidos por duplicado: ${(omitidos.estandares || 0) + (omitidos.dbas || 0) + (omitidos.ejes || 0)}.`;
            importResult.className = 'import-result success';
            importFile.value = '';
            importFileName.textContent = 'Aún no ha seleccionado un archivo.';
            await fetchData();
        } catch (error) {
            console.error(error);
            importResult.textContent = `Error: ${error.message}`;
            importResult.className = 'import-result error';
        } finally {
            importButton.disabled = !importFile.files[0];
            importButton.innerHTML = '<i class="fa-solid fa-file-import"></i> Importar archivo';
        }
    });

    function bindActionButtons() {
        // Add listeners
        document.querySelectorAll('.btn-add-est').forEach(btn => {
            btn.addEventListener('click', (e) => openModal('estandar', 'create', { period: e.currentTarget.dataset.period }));
        });
        document.querySelectorAll('.btn-add-dba').forEach(btn => {
            btn.addEventListener('click', (e) => openModal('dba', 'create', { estandarId: e.currentTarget.dataset.est }));
        });
        document.querySelectorAll('.btn-add-eje').forEach(btn => {
            btn.addEventListener('click', (e) => openModal('eje', 'create', { dbaId: e.currentTarget.dataset.dba }));
        });

        // Edit listeners
        document.querySelectorAll('.btn-edit-est').forEach(btn => {
            btn.addEventListener('click', (e) => openModal('estandar', 'edit', { id: e.currentTarget.dataset.id }));
        });
        document.querySelectorAll('.btn-edit-dba').forEach(btn => {
            btn.addEventListener('click', (e) => openModal('dba', 'edit', { id: e.currentTarget.dataset.id }));
        });
        document.querySelectorAll('.btn-edit-eje').forEach(btn => {
            btn.addEventListener('click', (e) => openModal('eje', 'edit', { id: e.currentTarget.dataset.id }));
        });

        // Delete listeners
        document.querySelectorAll('.btn-del-est').forEach(btn => {
            btn.addEventListener('click', (e) => deleteItem('estandar', e.currentTarget.dataset.id));
        });
        document.querySelectorAll('.btn-del-dba').forEach(btn => {
            btn.addEventListener('click', (e) => deleteItem('dba', e.currentTarget.dataset.id));
        });
        document.querySelectorAll('.btn-del-eje').forEach(btn => {
            btn.addEventListener('click', (e) => deleteItem('eje_tematico', e.currentTarget.dataset.id));
        });
    }

    // Modal Logic
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.modal-overlay').forEach(m => m.classList.add('hidden'));
        });
    });

    function openModal(type, action, params) {
        document.querySelectorAll('.modal-status').forEach(note => note.remove());
        activeModalParams = { ...params, action, type };
        const prefix = action === 'create' ? 'Añadir' : 'Editar';
        const record = action === 'edit' ? findRecord(type, params.id) : null;
        if (action === 'edit' && !record) {
            showOperation('No fue posible encontrar el registro para editar. Actualice la página e inténtelo de nuevo.', 'error');
            return;
        }
        
        if(type === 'estandar') {
            document.getElementById('title-modal-estandar').textContent = `${prefix} Estándar`;
            document.getElementById('input-nombre-estandar').value = record?.nombre_estandar || '';
            document.getElementById('input-descripcion-estandar').value = record?.descripcion_estandar || '';
            document.getElementById('modal-estandar').classList.remove('hidden');
        } else if(type === 'dba') {
            document.getElementById('title-modal-dba').textContent = `${prefix} DBA`;
            document.getElementById('input-nombre-dba').value = record?.nombre_dba || '';
            document.getElementById('input-descripcion-dba').value = record?.descripcion_dba || '';
            document.getElementById('input-palabras-dba').value = record?.palabras_clave || '';
            document.getElementById('modal-dba').classList.remove('hidden');
        } else if(type === 'eje') {
            document.getElementById('title-modal-eje').textContent = `${prefix} Eje Temático`;
            document.getElementById('input-nombre-eje').value = record?.nombre_eje_tematico || '';
            document.getElementById('input-descripcion-eje').value = record?.descripcion_eje_tematico || '';
            document.getElementById('modal-eje').classList.remove('hidden');
        }
    }

    // Guardar (Create or Edit)
    document.getElementById('btn-save-estandar').addEventListener('click', async () => {
        const val = document.getElementById('input-nombre-estandar').value.trim();
        const descripcion = document.getElementById('input-descripcion-estandar').value.trim();
        if(!val) return showOperation('Escriba el nombre del estándar antes de guardar.', 'error');
        
        const payload = { action: activeModalParams.action, type: 'estandar', nombre_estandar: val, descripcion_estandar: descripcion };
        if (activeModalParams.action === 'create') {
            payload.grado = currentState.gradoId;
            payload.id_materia_oficial = currentState.materiaId;
            payload.id_periodo = activeModalParams.period;
        } else {
            payload.id_estandar = activeModalParams.id;
        }

        if (await doCrud(payload)) document.getElementById('modal-estandar').classList.add('hidden');
    });

    document.getElementById('btn-save-dba').addEventListener('click', async () => {
        const val = document.getElementById('input-nombre-dba').value.trim();
        const descripcion = document.getElementById('input-descripcion-dba').value.trim();
        if(!val) return showOperation('Escriba el nombre del DBA antes de guardar.', 'error');
        
        const payload = { action: activeModalParams.action, type: 'dba', nombre_dba: val, descripcion_dba: descripcion, palabras_clave: document.getElementById('input-palabras-dba').value };
        if (activeModalParams.action === 'create') {
            payload.id_estandar = activeModalParams.estandarId;
        } else {
            payload.id_dba = activeModalParams.id;
        }

        if (await doCrud(payload)) document.getElementById('modal-dba').classList.add('hidden');
    });

    document.getElementById('btn-save-eje').addEventListener('click', async () => {
        const val = document.getElementById('input-nombre-eje').value.trim();
        const descripcion = document.getElementById('input-descripcion-eje').value.trim();
        if(!val) return showOperation('Escriba el nombre del eje temático antes de guardar.', 'error');
        
        const payload = { action: activeModalParams.action, type: 'eje_tematico', nombre_eje_tematico: val, descripcion_eje_tematico: descripcion };
        if (activeModalParams.action === 'create') {
            payload.id_dba = activeModalParams.dbaId;
        } else {
            payload.id_eje_tematico = activeModalParams.id;
        }

        if (await doCrud(payload)) document.getElementById('modal-eje').classList.add('hidden');
    });

    async function deleteItem(type, id) {
        let msg = "¿Seguro que deseas eliminar este registro?";
        if(type === 'estandar') msg = "¡ATENCIÓN! Al borrar un Estándar se borrarán en cascada todos sus DBAs, evidencias y Ejes Temáticos.\n\n¿Estás completamente seguro?";
        if(type === 'dba') msg = "Al borrar un DBA se borrarán también sus evidencias y Ejes Temáticos.\n\n¿Estás seguro?";

        if(!confirm(msg)) return;

        let payload = { action: 'delete', type: type };
        if(type === 'estandar') payload.id_estandar = id;
        if(type === 'dba') payload.id_dba = id;
        if(type === 'eje_tematico') payload.id_eje_tematico = id;

        await doCrud(payload);
    }

    window.dbaWorkspace = { getData: () => dbData, getFilters: () => currentState, refresh: fetchData, switchView, editDba: id => openModal('dba', 'edit', { id }) };
    async function doCrud(payload) {
        const buttons = document.querySelectorAll('.modal-footer .btn-primary');
        if ([...buttons].some(button => button.disabled)) return false;
        buttons.forEach(button => { button.disabled = true; });
        try {
            const res = await fetch('api/crud.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
                body: JSON.stringify(payload)
            });
            const result = await res.json().catch(() => null);
            if (!res.ok || !result || result.status !== 'success') {
                throw new Error(result?.message || 'No fue posible guardar los cambios.');
            }
            if (!await fetchData()) throw new Error('El cambio se guardó, pero no se pudo actualizar la vista.');
            showOperation(result.message || 'Cambios guardados correctamente.', 'success');
            return true;
        } catch(e) {
            console.error(e);
            showOperation(`Error en la operación: ${e.message}`, 'error');
            return false;
        } finally {
            buttons.forEach(button => { button.disabled = false; });
        }
    }
});
