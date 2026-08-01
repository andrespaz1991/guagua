document.addEventListener('DOMContentLoaded', () => {
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
    const viewTitle = document.getElementById('view-title');
    const viewSubtitle = document.getElementById('view-subtitle');
    const operationStatus = document.getElementById('operation-status');

    // Init
    fetchData();

    async function fetchData() {
        try {
            const res = await fetch('api/get_data.php');
            const result = await res.json();
            if (!res.ok || result.status !== 'success') throw new Error(result.message || 'No fue posible cargar los datos.');

            dbData = result.data;
            initSelects();
            renderPeriods();
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
    }

    selectGrado.addEventListener('change', (e) => {
        currentState.gradoId = parseInt(e.target.value);
        currentState.gradoName = e.target.options[e.target.selectedIndex].text;
        renderPeriods();
    });

    selectMateria.addEventListener('change', (e) => {
        currentState.materiaId = parseInt(e.target.value);
        currentState.materiaName = e.target.options[e.target.selectedIndex].text;
        renderPeriods();
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
        if (!operationStatus) return;
        operationStatus.textContent = message;
        operationStatus.className = `operation-status ${type}`.trim();
    }

    function renderPeriods() {
        periodsContainer.innerHTML = '';
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
        viewSubtitle.textContent = 'Gestione los Estándares, DBAs y Ejes Temáticos directamente en la base de datos.';

        // Filtrar estándares de este grado y materia
        const estandaresList = dbData.estandares.filter(e => e.grado == currentState.gradoId && e.id_materia_oficial == currentState.materiaId);

        for (let periodNum = 1; periodNum <= 4; periodNum++) {
            const periodEstandares = estandaresList.filter(e => e.id_periodo == periodNum);
            
            const block = document.createElement('div');
            block.className = 'period-block';
            
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
                                    <div class="eje-item">
                                            <div class="eje-title">${escapeHtml(eje.nombre_eje_tematico)}${renderDescription(eje.descripcion_eje_tematico)}</div>
                                            <div class="actions-group">
                                                <button class="btn-icon btn-edit-eje" data-id="${eje.id_eje_tematico}" data-name="${escapeHtml(eje.nombre_eje_tematico)}" title="Editar Eje"><i class="fa-solid fa-pen"></i></button>
                                                <button class="btn-icon delete btn-del-eje" data-id="${eje.id_eje_tematico}" title="Borrar Eje"><i class="fa-solid fa-trash-can"></i></button>
                                            </div>
                                        </div>
                                    `;
                                });
                            }

                            dbasHtml += `
                                <div class="dba-card">
                                    <div class="dba-header">
                                        <div class="dba-title">${escapeHtml(dba.nombre_dba)}${renderDescription(dba.descripcion_dba)}</div>
                                        <div class="actions-group">
                                            <button class="btn-icon btn-edit-dba" data-id="${dba.id_dba}" data-name="${escapeHtml(dba.nombre_dba)}" title="Editar DBA"><i class="fa-solid fa-pen"></i></button>
                                            <button class="btn-icon btn-add-eje" data-dba="${dba.id_dba}" title="Añadir Eje Temático"><i class="fa-solid fa-plus"></i></button>
                                            <button class="btn-icon delete btn-del-dba" data-id="${dba.id_dba}" title="Borrar DBA (Cascada)"><i class="fa-solid fa-trash-can"></i></button>
                                        </div>
                                    </div>
                                    <div class="ejes-list">
                                        ${ejesHtml}
                                    </div>
                                </div>
                            `;
                        });
                    }

                    estandaresHtml += `
                        <div class="estandar-card">
                            <div class="estandar-header">
                                <div class="estandar-title">${escapeHtml(est.nombre_estandar)}${renderDescription(est.descripcion_estandar)}</div>
                                <div class="actions-group">
                                    <button class="btn-icon btn-edit-est" data-id="${est.id_estandar}" data-name="${escapeHtml(est.nombre_estandar)}" title="Editar Estándar"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn-icon btn-add-dba" data-est="${est.id_estandar}" title="Añadir DBA"><i class="fa-solid fa-plus"></i></button>
                                    <button class="btn-icon delete btn-del-est" data-id="${est.id_estandar}" title="Borrar Estándar (Cascada)"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </div>
                            <div class="estandar-body">
                                ${dbasHtml}
                            </div>
                        </div>
                    `;
                });
            }

            block.innerHTML = `
                <div class="period-header">
                    <span>Periodo ${periodNum}</span>
                    <button class="btn btn-secondary btn-add-est" data-period="${periodNum}"><i class="fa-solid fa-plus"></i> Añadir Estándar</button>
                </div>
                <div class="period-body">
                    ${estandaresHtml}
                </div>
            `;

            periodsContainer.appendChild(block);
        }

        bindActionButtons();
    }

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
        
        const payload = { action: activeModalParams.action, type: 'dba', nombre_dba: val, descripcion_dba: descripcion };
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
        if(type === 'estandar') msg = "¡ATENCIÓN! Al borrar un Estándar se borrarán en cascada todos sus DBAs y Ejes Temáticos.\n\n¿Estás completamente seguro?";
        if(type === 'dba') msg = "Al borrar un DBA se borrarán también sus Ejes Temáticos.\n\n¿Estás seguro?";

        if(!confirm(msg)) return;

        let payload = { action: 'delete', type: type };
        if(type === 'estandar') payload.id_estandar = id;
        if(type === 'dba') payload.id_dba = id;
        if(type === 'eje_tematico') payload.id_eje_tematico = id;

        await doCrud(payload);
    }

    async function doCrud(payload) {
        try {
            const res = await fetch('api/crud.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
        }
    }
});
