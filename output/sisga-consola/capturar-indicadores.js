/*
 * SISGA: capturar los indicadores marcados y descargar un script reutilizable.
 *
 * 1. En la planilla real, abre «Gestionar Indicadores».
 * 2. Marca las fortalezas/dificultades que quieras conservar.
 * 3. Pega TODO este archivo en la consola, ANTES de asignar.
 *
 * Se seleccionan todos los estudiantes disponibles y se descarga
 * «asignar-indicadores-MATERIA-grado-GRADO-periodo-PERIODO.js».
 * La captura NO pulsa Asignar seleccionados.
 * Repite en otras materias/grados/períodos: cada descarga incluye todo lo guardado.
 *
 * Para reutilizar: abre la planilla y su modal, y pega el archivo DESCARGADO.
 * Ese archivo elige la selección correspondiente y SÍ pulsa Asignar seleccionados.
 * No requiere instalar bibliotecas ni cambiar datos en el código.
 */
(async function gestorIndicadoresSISGA(opciones) {
    'use strict';
    opciones = opciones || {};

    const ALMACEN = 'guagua.sisga.selecciones-consola.v1';
    const FORMATO = 'sisga.selecciones-consola.v1';
    const texto = valor => String(valor ?? '').replace(/\s+/g, ' ').trim();
    const normal = valor => texto(valor).normalize('NFC').toLocaleLowerCase('es');
    const copia = valor => JSON.parse(JSON.stringify(valor));
    const $ = selector => document.querySelector(selector);
    const $$ = selector => Array.from(document.querySelectorAll(selector));
    const SEL_EST = '#lista-estudiantes-ind .chk-est-ind';
    const SEL_IND = '#lista-indicadores-ind .chk-ind-ind';

    if (window.IndicadoresSISGA?.ocupado) {
        throw new Error('Hay una asignación en curso. Espera su resultado antes de ejecutar otro script.');
    }

    let perfiles = [];
    try {
        const guardado = localStorage.getItem(ALMACEN);
        if (guardado) {
            const datos = JSON.parse(guardado);
            if (datos.formato !== FORMATO || !Array.isArray(datos.perfiles)) {
                throw new Error('El formato del almacenamiento no es compatible.');
            }
            perfiles = datos.perfiles;
        }
    } catch (error) {
        console.warn('No se pudo leer el respaldo del navegador. Los datos de esta sesión se podrán descargar.', error);
    }

    function contexto() {
        const config = window.PLANILLA_CONFIG;
        if (!config || !$('#modal-indicadores') || !$('#lista-indicadores-ind')) {
            throw new Error('Ejecuta el script en la planilla real de SISGA, con «Gestionar Indicadores» abierto; no en el HTML descargado ni en Ver código fuente.');
        }
        const params = new URL(location.href).searchParams;
        // PLANILLA_CONFIG tiene prioridad: el grupo puede cambiar sin cambiar la URL.
        const actual = {
            origen: location.origin,
            institucion: texto(config.InicialesInstitucion),
            materia: { codigo: texto(config.Asi ?? params.get('Asi')), nombre: texto(config.NombreAsignatura ?? params.get('sAsi')) },
            grado: { codigo: texto(config.Gra ?? params.get('Gra')), nombre: texto(config.NombreGrado ?? params.get('sGra')) },
            periodo: { codigo: texto(config.Per ?? params.get('Per')), nombre: texto(config.NombrePeriodo ?? params.get('sPer')) },
            nivel: texto(config.Niv ?? params.get('sNiv') ?? 0),
            grupoOrigen: { codigo: texto(config.Gru), nombre: texto(config.NombreGrupo) }
        };
        if (!actual.materia.codigo || !actual.grado.codigo || !actual.periodo.codigo) {
            throw new Error('No se pudo identificar la materia, el grado y el período. No se modificó la selección.');
        }
        return actual;
    }

    // El grupo no forma parte de la clave: la selección sirve para los grupos del mismo grado.
    function clave(c) {
        return JSON.stringify([c.origen, normal(c.institucion), c.materia.codigo,
            normal(c.grado.nombre) || c.grado.codigo, c.periodo.codigo, c.nivel]);
    }

    function equivalente(a, b) {
        if (a.origen !== b.origen || normal(a.institucion) !== normal(b.institucion)
            || a.periodo.codigo !== b.periodo.codigo || a.nivel !== b.nivel) return false;
        const materia = a.materia.nombre && b.materia.nombre
            ? normal(a.materia.nombre) === normal(b.materia.nombre)
            : a.materia.codigo === b.materia.codigo;
        const grado = a.grado.nombre && b.grado.nombre
            ? normal(a.grado.nombre) === normal(b.grado.nombre)
            : a.grado.codigo === b.grado.codigo;
        return materia && grado;
    }

    function incorporar(perfil) {
        if (!perfil?.contexto || !Array.isArray(perfil.indicadores) || !perfil.indicadores.length) {
            throw new Error('El archivo contiene una selección inválida.');
        }
        perfiles = perfiles.filter(p => clave(p.contexto) !== clave(perfil.contexto)
            && !equivalente(p.contexto, perfil.contexto));
        perfiles.push(copia(perfil));
    }

    (opciones.perfiles || []).forEach(incorporar);

    function persistir() {
        try {
            localStorage.setItem(ALMACEN, JSON.stringify({ formato: FORMATO, perfiles }));
        } catch (error) {
            console.warn('El navegador no pudo guardar el respaldo. Conserva el archivo JS descargado.', error);
        }
    }

    function comprobarPantalla() {
        const config = window.PLANILLA_CONFIG;
        const estudiantes = $$(SEL_EST);
        const indicadores = $$(SEL_IND);
        const boton = $('#btn-asignar-ind');
        if (!estudiantes.length || !indicadores.length) {
            throw new Error('Abre «Gestionar Indicadores». Debe haber indicadores y estudiantes con nota definitiva guardada.');
        }
        if (config.PeriodoRestringido || !boton || boton.disabled || getComputedStyle(boton).display === 'none') {
            throw new Error('La página no permite asignar en este período.');
        }
        if ([...estudiantes, ...indicadores].some(elemento => elemento.disabled)) {
            throw new Error('Hay casillas deshabilitadas. Revisa el estado de la planilla.');
        }
        if (/guardando/i.test($('#msg-asignar')?.textContent || '')) {
            throw new Error('La página está guardando una asignación. Espera su resultado.');
        }
        return { estudiantes, indicadores, boton };
    }

    function limite(cantidad) {
        const maximo = Number(window.PLANILLA_CONFIG.MaxIndicadores || 0);
        if (maximo > 0 && cantidad > maximo) {
            throw new Error(`La selección contiene ${cantidad} indicadores; la página permite como máximo ${maximo} por asignación.`);
        }
    }

    function datosIndicador(casilla) {
        const config = window.PLANILLA_CONFIG;
        const dato = (config.IndicadoresAsignatura || []).find(ind => texto(ind.CodigoP) === casilla.value);
        const bloque = casilla.closest('#lista-indicadores-ind > div[id]');
        const categoriaDOM = bloque?.previousElementSibling?.getAttribute('data-cat');
        const nombreCategoria = texto(dato?.TipindNombre || categoriaDOM);
        const tipo = (config.TiposIndicadores || []).find(t => normal(t.Nombre) === normal(nombreCategoria));
        const resultado = {
            codigo: casilla.value,
            texto: texto(dato?.Nombre || casilla.closest('label')?.textContent),
            categoria: { codigo: texto(dato?.TipindCodigoP ?? tipo?.CodigoP), nombre: nombreCategoria }
        };
        if (!resultado.codigo || !resultado.texto || !resultado.categoria.nombre) {
            throw new Error('No se pudo leer el texto o la categoría de un indicador. No se guardó una selección incompleta.');
        }
        return resultado;
    }

    function marcar(casilla, estado) {
        if (casilla.checked === estado) return;
        casilla.checked = estado;
        casilla.dispatchEvent(new Event('input', { bubbles: true }));
        casilla.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function todos(estudiantes) {
        const filtro = $('#filtro-nombre-est');
        if (filtro) {
            filtro.value = '';
            filtro.dispatchEvent(new Event('input', { bubbles: true }));
        }
        // Incluye filas ocultas por el filtro, igual que «Todos» sin filtro.
        estudiantes.forEach(casilla => marcar(casilla, true));
    }

    function listar() {
        console.table(perfiles.map(p => ({
            Materia: p.contexto.materia.nombre || p.contexto.materia.codigo,
            Grado: p.contexto.grado.nombre || p.contexto.grado.codigo,
            Periodo: p.contexto.periodo.nombre || p.contexto.periodo.codigo,
            Indicadores: p.indicadores.length,
            Guardado: p.guardadoEn
        })));
        return copia(perfiles);
    }

    function exportar() {
        if (!perfiles.length) throw new Error('Todavía no hay selecciones guardadas. Marca indicadores y ejecuta IndicadoresSISGA.guardar().');
        const actual = contexto();
        const referencia = (perfiles.find(p => equivalente(p.contexto, actual)) || perfiles[perfiles.length - 1]).contexto;
        const parteNombre = valor => texto(valor).normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-zA-Z0-9_-]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 100) || 'sin-nombre';
        const nombreArchivo = `asignar-indicadores-${parteNombre(referencia.materia.nombre || referencia.materia.codigo)}`
            + `-grado-${parteNombre(referencia.grado.nombre || referencia.grado.codigo)}`
            + `-periodo-${parteNombre(referencia.periodo.nombre || referencia.periodo.codigo)}.js`;
        const contenido = '// SISGA: abre la planilla y Gestionar Indicadores antes de pegar este script.\n'
            + '// Selecciona TODOS los estudiantes disponibles y ASIGNA los indicadores de la combinación actual.\n'
            + '// DATOS GUARDADOS: cada objeto conserva materia, grado, período y categorías.\n'
            + '(' + gestorIndicadoresSISGA.toString() + ')('
            + JSON.stringify({ modo: 'aplicar', perfiles }, null, 2)
            + ').catch(error => console.error("SISGA:", error.message));\n';
        const url = URL.createObjectURL(new Blob([contenido], { type: 'text/javascript;charset=utf-8' }));
        const enlace = document.createElement('a');
        enlace.href = url;
        enlace.download = nombreArchivo;
        document.body.appendChild(enlace);
        enlace.click();
        enlace.remove();
        setTimeout(() => URL.revokeObjectURL(url), 10000);
        console.info(`Descargado ${nombreArchivo} con ${perfiles.length} combinación(es). Conserva la descarga más reciente.`);
        return contenido;
    }

    function guardar({ descargar = true } = {}) {
        if (api.ocupado) throw new Error('Espera a que termine la asignación en curso.');
        const actual = contexto();
        const pantalla = comprobarPantalla();
        const seleccionados = pantalla.indicadores.filter(c => c.checked);
        if (!seleccionados.length) {
            throw new Error('No hay indicadores marcados. Marca las fortalezas/dificultades y vuelve a ejecutar IndicadoresSISGA.guardar(), antes de asignar.');
        }
        limite(seleccionados.length);
        const perfil = { contexto: actual, guardadoEn: new Date().toISOString(), indicadores: seleccionados.map(datosIndicador) };
        incorporar(perfil);
        persistir();
        todos(pantalla.estudiantes);
        console.info(`Guardados ${perfil.indicadores.length} indicadores y seleccionados ${pantalla.estudiantes.length} estudiantes. La captura no pulsa Asignar.`);
        console.table(perfil.indicadores.map(i => ({ Categoria: i.categoria.nombre, Codigo: i.codigo, Indicador: i.texto })));
        if (descargar) exportar();
        return copia(perfil);
    }

    function resolver(perfil, casillas) {
        const catalogo = casillas.map(casilla => ({ casilla, dato: datosIndicador(casilla) }));
        return perfil.indicadores.map(ind => {
            const coincide = item => normal(item.dato.texto) === normal(ind.texto)
                && normal(item.dato.categoria.nombre) === normal(ind.categoria.nombre);
            // Se verifica el texto aunque el código siga existiendo.
            let encontrados = catalogo.filter(item => item.dato.codigo === ind.codigo && coincide(item));
            // Si cambiaron los códigos, solo se acepta texto y categoría exactos y únicos.
            if (!encontrados.length) encontrados = catalogo.filter(coincide);
            if (encontrados.length !== 1) {
                throw new Error(`Indicador ${encontrados.length ? 'ambiguo' : 'no encontrado o modificado'}: [${ind.categoria.nombre}] ${ind.texto}. No se pulsó Asignar.`);
            }
            return encontrados[0].casilla;
        });
    }

    async function aplicar({ asignar = true } = {}) {
        if (api.ocupado) throw new Error('Ya hay una asignación en curso.');
        const actual = contexto();
        const candidatos = perfiles.filter(p => equivalente(p.contexto, actual));
        if (candidatos.length !== 1) {
            listar();
            throw new Error(candidatos.length
                ? 'Hay varias selecciones para esta combinación. Vuelve a capturar la selección en la planilla actual.'
                : 'No hay una selección guardada para esta materia, grado, período, institución y nivel. Captúrala primero o usa la descarga más reciente.');
        }
        const perfil = candidatos[0];
        const pantalla = comprobarPantalla();
        limite(perfil.indicadores.length);
        const elegidos = new Set(resolver(perfil, pantalla.indicadores));
        if (elegidos.size !== perfil.indicadores.length) throw new Error('La selección guardada contiene indicadores repetidos.');
        pantalla.indicadores.forEach(casilla => marcar(casilla, elegidos.has(casilla)));
        todos(pantalla.estudiantes);
        if (pantalla.indicadores.some(c => c.checked !== elegidos.has(c))
            || pantalla.estudiantes.some(c => !c.checked)) {
            throw new Error('La página no conservó todas las casillas seleccionadas. No se pulsó Asignar.');
        }
        console.info(`Preparados ${elegidos.size} indicadores para ${pantalla.estudiantes.length} estudiantes: ${actual.materia.nombre}, grado ${actual.grado.nombre}, período ${actual.periodo.nombre}.`);
        if (!asignar) return copia(perfil);

        const mensaje = $('#msg-asignar');
        if (!mensaje) throw new Error('No se encontró el mensaje de resultado de SISGA.');
        api.ocupado = true;
        try {
            // Solo se usa el botón de la página, con su validación y petición habituales.
            // No se reintenta automáticamente una petición de resultado desconocido.
            return await new Promise((resolve, reject) => {
                let terminado = false;
                let temporizador;
                const observador = new MutationObserver(revisar);
                function terminar(error, resultado) {
                    if (terminado) return;
                    terminado = true;
                    observador.disconnect();
                    clearTimeout(temporizador);
                    if (error) reject(error);
                    else resolve(resultado);
                }
                function revisar() {
                    const contenido = texto(mensaje.textContent);
                    if (!contenido || /guardando/i.test(contenido)) return;
                    const color = mensaje.style.color;
                    if (color === 'red' || color === 'rgb(255, 0, 0)') {
                        terminar(new Error('SISGA rechazó la asignación: ' + contenido));
                    } else if (color === 'green' || color === 'rgb(0, 128, 0)'
                        || color === 'rgb(133, 100, 4)' || color === '#856404') {
                        const aviso = /omitid/i.test(contenido);
                        (aviso ? console.warn : console.info)('SISGA: ' + contenido);
                        terminar(null, { estado: aviso ? 'con_omisiones' : 'asignado', mensaje: contenido });
                    }
                }
                mensaje.textContent = '';
                mensaje.style.color = '';
                observador.observe(mensaje, { childList: true, characterData: true, subtree: true, attributes: true, attributeFilter: ['style'] });
                temporizador = setTimeout(() => terminar(new Error('SISGA no confirmó el resultado en 30 segundos. Revisa el mensaje de la página antes de repetir.')), 30000);
                try { pantalla.boton.click(); revisar(); }
                catch (error) { terminar(error); }
            });
        } finally {
            api.ocupado = false;
        }
    }

    const api = window.IndicadoresSISGA = {
        guardar, aplicar, preparar: () => aplicar({ asignar: false }), exportar, listar, ocupado: false
    };
    if (opciones.perfiles?.length) persistir();
    console.info('Comandos disponibles: IndicadoresSISGA.guardar(), .aplicar(), .preparar(), .exportar(), .listar().');
    if (opciones.modo === 'aplicar') return aplicar();
    return guardar();
})().catch(error => console.error('SISGA:', error.message));
