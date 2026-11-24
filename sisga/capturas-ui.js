(function () {
    'use strict';
    const $ = id => document.getElementById('si-' + id);
    if (!$('capturas')) return;
    let registros = [], seleccionado = null, generado = null, csrf = '', cargado = false, ocupado = false;
    const nombre = (p, campo) => p.contexto[campo].nombre || p.contexto[campo].codigo;
    const parte = valor => String(valor).normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-zA-Z0-9_-]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 100) || 'sin-nombre';
    const nombreArchivo = p => `asignar-indicadores-${parte(nombre(p, 'materia'))}-grado-${parte(nombre(p, 'grado'))}-periodo-${parte(nombre(p, 'periodo'))}.js`;
    function generar(perfil) {
        return '// SISGA: abre la planilla y Gestionar Indicadores antes de pegar este script.\n'
            + '// Selecciona todos los estudiantes disponibles y pulsa Asignar seleccionados.\n'
            + '(' + window.SisgaCapturasRuntime.toString() + ')('
            + JSON.stringify({ modo: 'aplicar', perfiles: [perfil] }, null, 2)
            + ').catch(error => console.error("SISGA:", error.message));\n';
    }
    function capturador() {
        return '// Marca los indicadores en Gestionar Indicadores y ejecuta antes de asignar.\n('
            + window.SisgaCapturasRuntime.toString() + ')().catch(error => console.error("SISGA:", error.message));\n';
    }
    window.SisgaCapturas = { generar, nombreArchivo, capturador };
    function estado(mensaje, error = false) { $('status').textContent = mensaje; $('status').dataset.error = String(error); }
    function invalidar() { generado = null; $('result').hidden = true; $('script').value = ''; }
    function bloquear(valor) {
        ocupado = valor;
        for (const id of ['import', 'refresh', 'subject', 'grade', 'period']) $(id).disabled = valor;
        $('generate').disabled = valor || !seleccionado;
        $('capturas').setAttribute('aria-busy', String(valor));
        $('import-zone').setAttribute('aria-busy', String(valor));
        $('files').disabled = valor;
    }
    const seguro = fn => async evento => { try { await fn(evento); } catch (error) { estado(error.message, true); } };
    function descargar(contenido, archivo) {
        const url = URL.createObjectURL(new Blob([contenido], { type: 'text/javascript;charset=utf-8' }));
        const enlace = document.createElement('a'); enlace.href = url; enlace.download = archivo;
        document.body.appendChild(enlace); enlace.click(); enlace.remove(); setTimeout(() => URL.revokeObjectURL(url), 10000);
    }
    async function solicitar(opciones) {
        const respuesta = await fetch('api_indicadores.php', { credentials: 'same-origin', cache: 'no-store', ...opciones });
        let datos;
        try { datos = await respuesta.json(); } catch (_) { throw new Error('El servidor no devolvió una respuesta válida. Comprueba tu sesión en Guagua.'); }
        if (!respuesta.ok || !datos.ok) throw new Error(datos.mensaje || datos.message || 'No fue posible consultar los indicadores.');
        return datos;
    }
    function opciones(id, valores, inicial) {
        const select = $(id), anterior = select.value;
        const crear = (texto, valor) => { const option = document.createElement('option'); option.textContent = texto; option.value = valor; return option; };
        const unicos = [...new Set(valores)].sort((a, b) => a.localeCompare(b, 'es', { numeric: true }));
        select.replaceChildren(crear(inicial, ''), ...unicos.map(valor => crear(valor, valor)));
        if (unicos.includes(anterior)) select.value = anterior;
    }
    function filtrar() {
        return registros.filter(r => (!$('subject').value || nombre(r.perfil, 'materia') === $('subject').value)
            && (!$('grade').value || nombre(r.perfil, 'grado') === $('grade').value)
            && (!$('period').value || nombre(r.perfil, 'periodo') === $('period').value));
    }
    function filtros() {
        opciones('subject', registros.map(r => nombre(r.perfil, 'materia')), 'Todas las materias');
        const materia = registros.filter(r => !$('subject').value || nombre(r.perfil, 'materia') === $('subject').value);
        opciones('grade', materia.map(r => nombre(r.perfil, 'grado')), 'Todos los grados');
        const grado = materia.filter(r => !$('grade').value || nombre(r.perfil, 'grado') === $('grade').value);
        opciones('period', grado.map(r => nombre(r.perfil, 'periodo')), 'Todos los períodos');
    }
    function vistaPrevia() {
        $('preview').hidden = !seleccionado;
        $('generate').disabled = ocupado || !seleccionado;
        if (!seleccionado) { $('selection-label').textContent = 'Selecciona una combinación de la lista.'; return; }
        const p = seleccionado.perfil;
        const descripcion = `${nombre(p, 'materia')} · Grado ${nombre(p, 'grado')} · Período ${nombre(p, 'periodo')}`;
        $('selection-label').textContent = descripcion;
        $('preview-title').textContent = descripcion;
        const fecha = new Intl.DateTimeFormat('es-CO', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'America/Bogota' }).format(new Date(p.guardadoEn));
        $('preview-date').textContent = `Capturado el ${fecha} · Institución SISGA: ${p.contexto.institucion}`;
        const grupos = new Map();
        for (const ind of p.indicadores) {
            if (!grupos.has(ind.categoria.nombre)) grupos.set(ind.categoria.nombre, []);
            grupos.get(ind.categoria.nombre).push(ind);
        }
        $('indicators').replaceChildren(...[...grupos].map(([categoria, indicadores]) => {
            const caja = document.createElement('div'); caja.className = 'si-indicator-group'; caja.dataset.category = categoria.toLocaleLowerCase('es');
            const titulo = document.createElement('h3'); titulo.textContent = categoria;
            const lista = document.createElement('ul');
            for (const indicador of indicadores) { const li = document.createElement('li'); li.textContent = indicador.texto; lista.append(li); }
            caja.append(titulo, lista); return caja;
        }));
    }
    function tabla() {
        const visibles = filtrar();
        if (!visibles.some(r => r.id === seleccionado?.id)) seleccionado = null;
        if (visibles.length === 1) seleccionado = visibles[0];
        $('count').textContent = `${visibles.length} de ${registros.length} combinaciones`;
        $('empty').hidden = visibles.length > 0;
        $('rows').replaceChildren(...visibles.map(registro => {
            const p = registro.perfil, fila = document.createElement('tr');
            fila.dataset.selected = String(seleccionado?.id === registro.id);
            for (const valor of [nombre(p, 'materia'), nombre(p, 'grado'), nombre(p, 'periodo'), String(p.indicadores.length)]) {
                const celda = document.createElement('td'); celda.textContent = valor; fila.append(celda);
            }
            const detalle = document.createElement('small'); detalle.textContent = `${p.contexto.institucion} · Nivel ${p.contexto.nivel}`;
            fila.firstChild.append(detalle);
            const accion = document.createElement('td'), boton = document.createElement('button'); boton.type = 'button';
            boton.textContent = seleccionado?.id === registro.id ? 'Seleccionado' : 'Seleccionar';
            boton.setAttribute('aria-label', `Seleccionar ${nombre(p, 'materia')}, grado ${nombre(p, 'grado')}, período ${nombre(p, 'periodo')}`);
            boton.addEventListener('click', () => {
                seleccionado = registro; invalidar(); $('subject').value = nombre(p, 'materia'); filtros();
                $('grade').value = nombre(p, 'grado'); filtros(); $('period').value = nombre(p, 'periodo'); tabla();
            });
            accion.append(boton); fila.append(accion); return fila;
        }));
        vistaPrevia();
    }
    function presentar(lista) {
        registros = lista;
        if (seleccionado) seleccionado = registros.find(r => r.id === seleccionado.id) || null;
        const materias = new Set(registros.map(r => nombre(r.perfil, 'materia'))).size;
        const grados = new Set(registros.map(r => nombre(r.perfil, 'grado'))).size;
        $('summary').textContent = `${registros.length} combinaciones · ${materias} materias · ${grados} grados`;
        filtros(); tabla(); invalidar();
    }
    async function cargar() {
        if (ocupado) return;
        bloquear(true); estado('Consultando indicadores guardados…');
        try {
            const datos = await solicitar(); csrf = datos.csrf; presentar(datos.perfiles); cargado = true;
            estado(registros.length ? 'Elige una combinación para revisar sus indicadores y generar el script.' : 'Importa los scripts que capturaste en SISGA para comenzar.');
        } finally { bloquear(false); }
    }
    for (const id of ['subject', 'grade', 'period']) $(id).addEventListener('change', () => {
        seleccionado = null; invalidar(); filtros(); tabla();
    });
    $('refresh').addEventListener('click', seguro(cargar));
    $('form').addEventListener('submit', seguro(evento => {
        evento.preventDefault();
        if (!seleccionado || ocupado) throw new Error('Selecciona una combinación guardada.');
        generado = { contenido: generar(seleccionado.perfil), archivo: nombreArchivo(seleccionado.perfil) };
        $('script').value = generado.contenido; $('filename').textContent = generado.archivo; $('result').hidden = false;
        estado('Script generado. Al ejecutarlo en SISGA se asignarán estos indicadores a todos los estudiantes disponibles.');
        $('result').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }));
    $('download').addEventListener('click', seguro(() => { if (generado) descargar(generado.contenido, generado.archivo); }));
    $('copy').addEventListener('click', seguro(async () => {
        if (!generado) return;
        try { await navigator.clipboard.writeText(generado.contenido); estado('Script copiado. Pégalo en la consola de la planilla correspondiente.'); }
        catch (_) { $('script').focus(); $('script').select(); estado('Script seleccionado. Usa Ctrl+C para copiarlo.'); }
    }));
    $('capture').addEventListener('click', () => {
        descargar(capturador(), 'capturar-indicadores.js'); estado('Capturador descargado. Ejecútalo antes de asignar en SISGA e importa aquí el archivo que genere.');
    });
    $('capture-copy').addEventListener('click', seguro(async () => {
        const codigo = capturador();
        try {
            await navigator.clipboard.writeText(codigo);
            $('capture-fallback').hidden = true;
            estado('Capturador copiado. Pégalo en la consola de «Gestionar Indicadores» antes de asignar.');
        } catch (_) {
            $('capture-script').value = codigo; $('capture-fallback').hidden = false;
            $('capture-script').focus(); $('capture-script').select();
            $('capture-fallback').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            estado('Capturador seleccionado. Usa Ctrl+C para copiarlo y pégalo en la consola de SISGA antes de asignar.');
        }
    }));
    async function importar(archivos) {
        if (!archivos.length) return;
        if (ocupado) throw new Error('Hay una consulta o importación en curso. Espera a que termine.');
        if (archivos.some(f => !/\.(js|json)$/i.test(f.name))) throw new Error('Selecciona o arrastra únicamente scripts .js o archivos .json de indicadores.');
        if (archivos.length > 100 || archivos.some(f => f.size > 3 * 1024 * 1024) || archivos.reduce((n, f) => n + f.size, 0) > 8 * 1024 * 1024) {
            throw new Error('Importa hasta 100 archivos de máximo 3 MB cada uno y 8 MB en total.');
        }
        if (!csrf) await cargar();
        bloquear(true); estado('Importando capturas y conservando la versión más reciente de cada combinación…');
        try {
            const contenido = await Promise.all(archivos.map(async archivo => ({ nombre: archivo.name, contenido: await archivo.text() })));
            const datos = await solicitar({ method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf }, body: JSON.stringify({ archivos: contenido }) });
            seleccionado = null;
            for (const id of ['subject', 'grade', 'period']) $(id).value = '';
            presentar(datos.perfiles); cargado = true;
            const r = datos.resumen; estado(`Importación completada: ${r.nuevos} nuevas, ${r.actualizados} actualizadas y ${r.sinCambios} sin cambios. ${registros.length} combinaciones guardadas en la base de datos.`);
        } finally { bloquear(false); }
    }
    $('import').addEventListener('click', () => $('files').click());
    $('files').addEventListener('change', seguro(async () => {
        const archivos = [...$('files').files]; $('files').value = '';
        await importar(archivos);
    }));
    const zona = $('import-zone');
    let arrastres = 0;
    const sonArchivos = evento => Array.from(evento.dataTransfer?.types || []).includes('Files');
    zona.addEventListener('dragenter', evento => {
        if (!sonArchivos(evento)) return;
        evento.preventDefault(); arrastres++;
        zona.dataset.dragging = String(!ocupado);
    });
    zona.addEventListener('dragover', evento => {
        if (!sonArchivos(evento)) return;
        evento.preventDefault(); evento.dataTransfer.dropEffect = ocupado ? 'none' : 'copy';
    });
    zona.addEventListener('dragleave', evento => {
        if (!arrastres) return;
        evento.preventDefault(); arrastres = Math.max(0, arrastres - 1);
        if (!arrastres) zona.dataset.dragging = 'false';
    });
    zona.addEventListener('drop', seguro(async evento => {
        evento.preventDefault(); arrastres = 0; zona.dataset.dragging = 'false';
        await importar(Array.from(evento.dataTransfer?.files || []));
    }));
    document.addEventListener('sisga:tab-change', seguro(async evento => {
        if (evento.detail.panel === 'si-capturas' && !cargado) await cargar();
    }));
    if (!$('capturas').hidden) seguro(cargar)();
})();
