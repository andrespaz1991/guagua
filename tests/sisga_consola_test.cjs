// Pruebas de captura/exportación/asignación en un DOM aislado, sin acceder a SISGA.
// node tests/sisga_consola_test.cjs <directorio que contenga node_modules/jsdom>
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const { createRequire } = require('node:module');
const { JSDOM } = createRequire(path.resolve(process.argv[2] || '.', 'package.json'))('jsdom');
const codigo = fs.readFileSync(path.join(__dirname, '../output/sisga-consola/capturar-indicadores.js'), 'utf8');
const ALMACEN = 'guagua.sisga.selecciones-consola.v1';
const copiar = x => JSON.parse(JSON.stringify(x));
const configBase = {
    Gra: 74, NombreGrado: '6', Asi: '0401', NombreAsignatura: 'Ciencias sociales',
    Per: 4, NombrePeriodo: '4', Gru: 969, NombreGrupo: 'SEXTO 1', Niv: 0,
    InicialesInstitucion: 'j3', PeriodoRestringido: false, MaxIndicadores: 5,
    TiposIndicadores: [{ CodigoP: '1', Nombre: 'FORTALEZAS' }, { CodigoP: '2', Nombre: 'DIFICULTADES' }],
    IndicadoresAsignatura: [
        { CodigoP: '60318', Nombre: '1.7 - Analiza los legados prehispánicos.', TipindCodigoP: '1', TipindNombre: 'FORTALEZAS' },
        { CodigoP: '60319', Nombre: '1.8 - Comprende la diversidad.', TipindCodigoP: '1', TipindNombre: 'FORTALEZAS' },
        { CodigoP: '60326', Nombre: '2.7 - Se le dificulta analizar los legados.', TipindCodigoP: '2', TipindNombre: 'DIFICULTADES' }
    ]
};
const sesiones = [];

function crear({ config = {}, marcado = true, almacenamiento, respuesta = 'ok' } = {}) {
    const dom = new JSDOM(`<!doctype html><body><div id="modal-indicadores">
        <input id="filtro-nombre-est" value="una persona">
        <div id="lista-estudiantes-ind">
          <table><tbody><tr data-mat="1"><td><input type="checkbox" class="chk-est-ind" value="1"></td></tr>
          <tr data-mat="2" style="display:none"><td><input type="checkbox" class="chk-est-ind" value="2"></td></tr></tbody></table>
        </div><div id="lista-indicadores-ind"></div>
        <button id="btn-asignar-ind">Asignar seleccionados</button><span id="msg-asignar"></span>
        <input id="ajeno" type="checkbox" checked>
        </div></body>`, {
        url: 'https://www.sisga.com.co/Saga/calificacionNumerica/NuevoCalificaciones/Calificar?Gra=74&Gru=969&Per=4&Asi=0401&sGra=6&sPer=4',
        runScripts: 'outside-only'
    });
    const w = dom.window;
    const errores = [], mensajes = [], descargas = [], urls = [];
    const $ = s => w.document.querySelector(s);
    const $$ = s => [...w.document.querySelectorAll(s)];
    w.console = { info: (...a) => mensajes.push(a.join(' ')), warn: (...a) => mensajes.push(a.join(' ')), error: (...a) => errores.push(a.join(' ')), table: () => {} };
    w.PLANILLA_CONFIG = { ...copiar(configBase), ...copiar(config) };
    if (almacenamiento) w.localStorage.setItem(ALMACEN, almacenamiento);
    w.URL.createObjectURL = blob => { descargas.push(blob); return 'blob:prueba'; };
    w.URL.revokeObjectURL = () => {};
    w.HTMLAnchorElement.prototype.click = function () { urls.push(this.download); };
    $('#filtro-nombre-est').oninput = () => $$('tr[data-mat]').forEach(tr => { tr.style.display = ''; });
    for (const tipo of w.PLANILLA_CONFIG.TiposIndicadores) {
        const cabecera = w.document.createElement('div'); cabecera.setAttribute('data-cat', tipo.Nombre);
        const bloque = w.document.createElement('div'); bloque.id = 'cat-' + tipo.CodigoP; bloque.style.display = 'none';
        for (const ind of w.PLANILLA_CONFIG.IndicadoresAsignatura.filter(i => i.TipindCodigoP === tipo.CodigoP)) {
            const label = w.document.createElement('label');
            const check = w.document.createElement('input'); check.type = 'checkbox'; check.className = 'chk-ind-ind'; check.value = ind.CodigoP;
            check.checked = marcado && ['60318', '60326'].includes(ind.CodigoP);
            label.append(check, w.document.createTextNode(ind.Nombre)); bloque.append(label);
        }
        $('#lista-indicadores-ind').append(cabecera, bloque);
    }
    let clicks = 0;
    $('#btn-asignar-ind').onclick = () => {
        clicks++;
        const msg = $('#msg-asignar');
        msg.textContent = 'Guardando...'; msg.style.color = 'gray';
        if (respuesta === 'pendiente') return;
        w.setTimeout(() => {
            msg.style.color = respuesta === 'error' ? 'red' : respuesta === 'omisiones' ? '#856404' : 'green';
            msg.textContent = respuesta === 'error' ? 'Error de red. Intente de nuevo.'
                : respuesta === 'omisiones' ? '2 asignados. Omitidos por no tener nota: prueba.' : '4 indicador(es) asignado(s) exitosamente.';
            if (respuesta !== 'error') $$('.chk-ind-ind:checked, .chk-est-ind:checked').forEach(c => { c.checked = false; });
        }, 0);
    };
    const ejecutar = script => w.eval(script);
    const sesion = { dom, w, $, $$, errores, mensajes, descargas, urls, ejecutar, clicks: () => clicks };
    sesiones.push(sesion);
    return sesion;
}

async function leerBlob(sesion, blob) {
    return new Promise(resolve => {
        const reader = new sesion.w.FileReader(); reader.onload = () => resolve(reader.result); reader.readAsText(blob);
    });
}

async function verificarFuenteOriginal(archivo) {
    const vista = new JSDOM(fs.readFileSync(archivo, 'utf8'));
    const fuente = [...vista.window.document.querySelectorAll('td.line-content')].map(td => td.textContent).join('\n');
    vista.window.close();
    const config = JSON.parse(fuente.match(/window\.PLANILLA_CONFIG\s*=\s*(\{[^\n]*\});/)[1]);
    config.Estudiantes = [1, 2, 3].map(numero => ({
        Matricula: String(numero), NombreCompleto: 'Prueba ' + numero, NumeroLista: numero, NotaDefinitiva: 3.5
    }));
    const dom = new JSDOM(fuente, {
        url: 'https://www.sisga.com.co/Saga/calificacionNumerica/NuevoCalificaciones/Calificar?Gra=74&Gru=969&Per=4&Asi=0401',
        runScripts: 'outside-only'
    });
    const w = dom.window;
    const errores = [], peticiones = [], blobs = [];
    sesiones.push({ dom });
    w.console = { info: () => {}, table: () => {}, warn: () => {}, error: (...args) => errores.push(args.join(' ')) };
    w.PLANILLA_CONFIG = config;
    w.$ = selector => ({ modal: () => { w.document.querySelector(selector).style.display = 'block'; } });
    w.URL.createObjectURL = blob => { blobs.push(blob); return 'blob:prueba'; };
    w.URL.revokeObjectURL = () => {};
    w.HTMLAnchorElement.prototype.click = () => {};
    w.fetch = async (url, opciones) => {
        if (url.endsWith('/AsignarIndicadores')) peticiones.push(JSON.parse(opciones.body));
        return { json: async () => ({ Ok: true, Mensaje: '6 indicador(es) asignado(s) exitosamente.', Indicadores: [] }) };
    };
    const principio = fuente.indexOf('var _TOKEN');
    const fin = fuente.indexOf('// ─── Fin gestión de indicadores', principio);
    assert.ok(principio > 0 && fin > principio, 'Se localizan las funciones originales');
    w.eval('var config = window.PLANILLA_CONFIG;\n' + fuente.slice(principio, fin));
    w.abrirModalIndicadores();
    // outside-only desactiva los atributos onclick del HTML; enlazamos su función original.
    w.document.getElementById('btn-asignar-ind').onclick = () => w.asignarIndicadores();
    w.document.querySelector('.chk-ind-ind[value="60318"]').checked = true;
    w.document.querySelector('.chk-ind-ind[value="60326"]').checked = true;
    await w.eval(codigo);
    assert.equal(blobs.length, 1);
    assert.equal(peticiones.length, 0, 'Capturar no ejecuta la petición original de asignación');
    const resultado = await w.IndicadoresSISGA.aplicar();
    assert.equal(errores.length, 0);
    assert.equal(resultado.estado, 'asignado');
    assert.equal(peticiones.length, 1);
    assert.deepEqual(peticiones[0].Pares.map(p => p.IndCodigoP).sort(), [60318, 60318, 60318, 60326, 60326, 60326]);
    assert.equal(peticiones[0].Asi, '0401');
    assert.equal(peticiones[0].Gra, 74);
    assert.equal(peticiones[0].Per, 4);
    assert.equal(w.document.querySelectorAll('.chk-ind-ind:checked, .chk-est-ind:checked').length, 0);
    console.log('OK: compatibilidad con renderizadores, categorías, botón y petición de asignación del HTML original, usando respuestas simuladas.');
}

async function main() {
    new vm.Script(codigo);
    const captura = crear(); await captura.ejecutar(codigo);
    assert.equal(captura.errores.length, 0);
    assert.equal(captura.clicks(), 0, 'Capturar no asigna');
    assert.equal(captura.$$('.chk-est-ind:checked').length, 2, 'Todos incluye estudiantes ocultos por filtro');
    assert.equal(captura.$('#filtro-nombre-est').value, '');
    assert.equal(captura.$('#ajeno').checked, true);
    assert.equal(captura.urls[0], 'asignar-indicadores-Ciencias-sociales-grado-6-periodo-4.js');
    const primerPerfil = copiar(captura.w.IndicadoresSISGA.listar()[0]);
    assert.equal(primerPerfil.contexto.materia.codigo, '0401', 'Conserva ceros iniciales');
    assert.equal(primerPerfil.contexto.grado.nombre, '6');
    assert.equal(primerPerfil.contexto.periodo.codigo, '4');
    assert.deepEqual(primerPerfil.indicadores.map(i => i.categoria.nombre), ['FORTALEZAS', 'DIFICULTADES']);
    assert.equal(primerPerfil.indicadores[0].codigo, '60318');
    assert.equal(primerPerfil.estudiantes, undefined, 'No guarda datos de alumnos');
    const primerJS = await leerBlob(captura, captura.descargas[0]);
    new vm.Script(primerJS);

    // El JS exportado funciona en un navegador sin el respaldo local.
    const nuevo = crear({ marcado: false });
    nuevo.$('[value="60319"]').checked = true;
    const preparacion = nuevo.ejecutar(primerJS);
    assert.deepEqual(nuevo.$$('.chk-ind-ind:checked').map(c => c.value), ['60318', '60326']);
    assert.equal(nuevo.$$('.chk-est-ind:checked').length, 2);
    assert.equal(nuevo.clicks(), 1);
    await preparacion;
    assert.equal(nuevo.errores.length, 0);
    assert.match(nuevo.mensajes.join(' '), /asignado.*exitosamente/);
    assert.equal(nuevo.w.IndicadoresSISGA.ocupado, false);

    // Cambiar solo de grupo no invalida la selección.
    const otroGrupo = crear({ marcado: false, config: { Gru: 970, NombreGrupo: 'SEXTO 2' } });
    await otroGrupo.ejecutar(primerJS); assert.equal(otroGrupo.clicks(), 1);

    // El contexto activo tiene prioridad sobre los parámetros antiguos de la URL.
    const otraMateria = crear({ marcado: false, config: { Asi: '0501', NombreAsignatura: 'Matemáticas' } });
    await otraMateria.ejecutar(primerJS); assert.equal(otraMateria.clicks(), 0);
    assert.match(otraMateria.errores.join(' '), /No hay una selección guardada/);
    assert.equal(otraMateria.$$('.chk-est-ind:checked').length, 0);
    for (const diferente of [{ Gra: 75, NombreGrado: '7' }, { Per: 3, NombrePeriodo: '3' }, { InicialesInstitucion: 'otra' }, { Niv: 1 }]) {
        const s = crear({ config: diferente }); await s.ejecutar(primerJS); assert.equal(s.clicks(), 0);
    }

    // Códigos nuevos: se acepta únicamente texto y categoría exactos y únicos.
    const cambioCodigo = copiar(configBase.IndicadoresAsignatura);
    cambioCodigo[0].CodigoP = '99001';
    const nuevosCodigos = crear({ marcado: false, config: { Gra: 174, IndicadoresAsignatura: cambioCodigo } });
    const asignacionNueva = nuevosCodigos.ejecutar(primerJS);
    assert.equal(nuevosCodigos.$('[value="99001"]').checked, true);
    await asignacionNueva; assert.equal(nuevosCodigos.clicks(), 1);
    const modificados = copiar(configBase.IndicadoresAsignatura); modificados[0].Nombre = 'Otro significado';
    const modificado = crear({ config: { IndicadoresAsignatura: modificados } });
    await modificado.ejecutar(primerJS); assert.equal(modificado.clicks(), 0);
    assert.match(modificado.errores.join(' '), /no encontrado o modificado/);
    const ambiguos = copiar(cambioCodigo); ambiguos.push({ ...ambiguos[0], CodigoP: '99002' });
    const ambiguo = crear({ config: { IndicadoresAsignatura: ambiguos } });
    await ambiguo.ejecutar(primerJS); assert.equal(ambiguo.clicks(), 0);
    assert.match(ambiguo.errores.join(' '), /ambiguo/);

    const restringido = crear({ config: { PeriodoRestringido: true } });
    await restringido.ejecutar(primerJS); assert.equal(restringido.clicks(), 0);
    const maximo = crear({ config: { MaxIndicadores: 1 } });
    await maximo.ejecutar(primerJS); assert.equal(maximo.clicks(), 0);
    assert.match(maximo.errores.join(' '), /máximo 1/);
    const vacio = crear({ marcado: false }); await vacio.ejecutar(codigo);
    assert.equal(vacio.descargas.length, 0); assert.match(vacio.errores.join(' '), /No hay indicadores marcados/);
    assert.equal(vacio.$$('.chk-est-ind:checked').length, 0);
    const faltante = crear(); faltante.$('#lista-estudiantes-ind').textContent = '';
    await faltante.ejecutar(primerJS); assert.equal(faltante.clicks(), 0);

    // Varias materias, grados y períodos se exportan y seleccionan por contexto.
    Object.assign(captura.w.PLANILLA_CONFIG, { Asi: '0501', NombreAsignatura: 'Matemáticas', Gra: 75, NombreGrado: '7', Per: 2, NombrePeriodo: '2' });
    captura.w.IndicadoresSISGA.guardar();
    assert.equal(captura.urls.at(-1), 'asignar-indicadores-Matematicas-grado-7-periodo-2.js');
    assert.equal(captura.w.IndicadoresSISGA.listar().length, 2);
    const acumulado = await leerBlob(captura, captura.descargas.at(-1));
    const segunda = crear({ marcado: false, config: { Asi: '0501', NombreAsignatura: 'Matemáticas', Gra: 75, NombreGrado: '7', Per: 2, NombrePeriodo: '2' } });
    await segunda.ejecutar(acumulado); assert.equal(segunda.clicks(), 1);
    const primeraOtraVez = crear({ marcado: false }); await primeraOtraVez.ejecutar(acumulado); assert.equal(primeraOtraVez.clicks(), 1);
    captura.$('[value="60326"]').checked = false;
    captura.w.IndicadoresSISGA.guardar({ descargar: false });
    assert.equal(captura.w.IndicadoresSISGA.listar().length, 2, 'Recapturar reemplaza solo la combinación actual');
    assert.equal(captura.w.IndicadoresSISGA.listar()[1].indicadores.length, 1);

    // Una segunda captura en otra página recupera el respaldo del navegador.
    const otraPagina = crear({ almacenamiento: captura.w.localStorage.getItem(ALMACEN), config: { Per: 1, NombrePeriodo: '1' } });
    await otraPagina.ejecutar(codigo); assert.equal(otraPagina.w.IndicadoresSISGA.listar().length, 3);
    await nuevo.w.IndicadoresSISGA.preparar();
    assert.equal(nuevo.clicks(), 1, 'Preparar no pulsa el botón');
    assert.equal(nuevo.$$('.chk-ind-ind:checked').length, 2);

    const error = crear({ respuesta: 'error' }); await error.ejecutar(primerJS);
    assert.match(error.errores.join(' '), /SISGA rechazó/); assert.equal(error.clicks(), 1);
    assert.equal(error.w.IndicadoresSISGA.ocupado, false);
    const omisiones = crear({ respuesta: 'omisiones' }); await omisiones.ejecutar(primerJS);
    assert.match(omisiones.mensajes.join(' '), /Omitidos/); assert.equal(omisiones.clicks(), 1);

    // Un doble pegado durante una petición no lanza una segunda asignación.
    const pendiente = crear({ respuesta: 'pendiente' });
    const enCurso = pendiente.ejecutar(primerJS);
    await pendiente.ejecutar(primerJS); assert.equal(pendiente.clicks(), 1);
    assert.match(pendiente.errores.join(' '), /asignación en curso/);
    pendiente.$('#msg-asignar').style.color = 'green'; pendiente.$('#msg-asignar').textContent = 'Indicadores asignados.';
    await enCurso;

    console.log('OK: captura, categorías cerradas, todos con filtro, JS autónomo, múltiples contextos, códigos nuevos, modificaciones, ambigüedad, restricciones, máximos, persistencia, preparación, respuesta de SISGA y concurrencia.');
    if (process.argv[3]) await verificarFuenteOriginal(process.argv[3]);
}

main().catch(error => { console.error(error); process.exitCode = 1; })
    .finally(() => { for (const sesion of sesiones) sesion.dom.window.close(); });
