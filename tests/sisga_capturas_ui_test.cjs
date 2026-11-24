// node tests/sisga_capturas_ui_test.cjs <directorio que contenga node_modules/jsdom>
const fs = require('node:fs'), path = require('node:path'), vm = require('node:vm'), assert = require('node:assert/strict');
const { createRequire } = require('node:module');
const { JSDOM } = createRequire(path.resolve(process.argv[2] || '.', 'package.json'))('jsdom');
const root = path.join(__dirname, '../sisga'), leer = nombre => fs.readFileSync(path.join(root, nombre), 'utf8');
function perfil(materia, grado, periodo) {
    return {
        contexto: { origen: 'https://www.sisga.com.co', institucion: 'j3', materia: { codigo: materia === 'Matemáticas' ? '0501' : '0401', nombre: materia }, grado: { codigo: String(68 + grado), nombre: String(grado) }, periodo: { codigo: String(periodo), nombre: String(periodo) }, nivel: '0', grupoOrigen: { codigo: '1', nombre: 'Grupo' } },
        guardadoEn: '2026-11-20T12:00:00.000Z', indicadores: [
            { codigo: '100', texto: 'Comprende los contenidos.', categoria: { codigo: '1', nombre: 'FORTALEZAS' } },
            { codigo: '101', texto: '<img src=x onerror=alert(1)> Presenta dificultad.', categoria: { codigo: '2', nombre: 'DIFICULTADES' } }
        ]
    };
}
const registros = [perfil('Matemáticas', 6, 4), perfil('Matemáticas', 7, 3), perfil('Ciencias sociales', 6, 4)].map((perfil, i) => ({ id: i + 1, perfil, archivoOrigen: 'prueba.js' }));
const tick = () => new Promise(resolve => setTimeout(resolve, 0));

async function main() {
    const dom = new JSDOM(leer('index.html'), { url: 'http://localhost/guagua/sisga/index.html?guagua_embedded=1', runScripts: 'outside-only' });
    const w = dom.window, $ = id => w.document.getElementById(id), pedidos = [], descargas = [], nombres = [];
    let perfilesServidor = registros, codigoCopiado = '';
    Object.defineProperty(w.navigator, 'clipboard', { configurable: true, value: { writeText: async codigo => { codigoCopiado = codigo; } } });
    w.HTMLElement.prototype.scrollIntoView = () => {};
    w.URL.createObjectURL = blob => { descargas.push(blob); return 'blob:prueba'; }; w.URL.revokeObjectURL = () => {};
    w.HTMLAnchorElement.prototype.click = function () { nombres.push(this.download); };
    w.fetch = async (url, opciones) => {
        pedidos.push({ url, opciones });
        if (opciones?.method === 'POST') return { ok: true, json: async () => ({ ok: true, perfiles: perfilesServidor, resumen: { nuevos: perfilesServidor.length - registros.length, actualizados: 0, sinCambios: 3 } }) };
        return { ok: true, json: async () => ({ ok: true, perfiles: perfilesServidor, csrf: 'prueba-csrf' }) };
    };
    for (const archivo of ['indicadores-core.js', 'indicadores-runtime.js', 'indicadores-ui.js', 'indicadores-consola.js', 'capturas-ui.js']) w.eval(leer(archivo));
    assert.equal(pedidos.length, 0, 'No consulta el banco mientras se usa el generador de notas');
    $('sg-tab-capturas').click(); await tick();
    assert.equal($('sg-notas').hidden, true); assert.equal($('sg-indicadores').hidden, true); assert.equal($('si-capturas').hidden, false);
    assert.equal($('si-rows').children.length, 3); assert.equal(pedidos.length, 1);
    assert.match($('si-summary').textContent, /3 combinaciones · 2 materias · 2 grados/);
    $('si-subject').value = 'Matemáticas'; $('si-subject').dispatchEvent(new w.Event('change'));
    assert.equal($('si-rows').children.length, 2); assert.equal($('si-generate').disabled, true);
    $('si-grade').value = '7'; $('si-grade').dispatchEvent(new w.Event('change'));
    assert.equal($('si-rows').children.length, 1); assert.equal($('si-generate').disabled, false);
    assert.equal($('si-preview').hidden, false); assert.equal($('si-indicators').querySelector('img'), null);
    assert.equal($('si-period').options.length, 2); assert.equal($('si-period').options[1].value, '3');
    $('si-form').dispatchEvent(new w.Event('submit', { bubbles: true, cancelable: true })); await tick();
    assert.equal($('si-result').hidden, false);
    assert.equal($('si-filename').textContent, 'asignar-indicadores-Matematicas-grado-7-periodo-3.js');
    const script = $('si-script').value; new vm.Script(script);
    const datos = JSON.parse(script.slice(script.lastIndexOf(')({') + 2, script.lastIndexOf('}).catch(') + 1));
    assert.equal(datos.perfiles.length, 1); assert.equal(datos.perfiles[0].contexto.grado.nombre, '7');
    assert.equal(datos.perfiles[0].contexto.periodo.codigo, '3');
    $('si-download').click(); assert.equal(nombres[0], $('si-filename').textContent);
    $('si-grade').value = '6'; $('si-grade').dispatchEvent(new w.Event('change'));
    assert.equal($('si-result').hidden, true, 'El script se invalida cuando cambian los filtros');
    $('si-capture').click(); assert.equal(nombres.at(-1), 'capturar-indicadores.js');
    const blobTexto = await new Promise(resolve => { const reader = new w.FileReader(); reader.onload = () => resolve(reader.result); reader.readAsText(descargas.at(-1)); });
    new vm.Script(blobTexto);
    $('si-capture-copy').click(); await tick();
    assert.equal(codigoCopiado, blobTexto, 'Copiar y descargar usan exactamente el mismo capturador');
    assert.match($('si-status').textContent, /Capturador copiado/);
    w.navigator.clipboard.writeText = async () => { throw new Error('Portapapeles no disponible'); };
    $('si-capture-copy').click(); await tick();
    assert.equal($('si-capture-fallback').hidden, false);
    assert.equal($('si-capture-script').value, blobTexto.replace(/\r\n/g, '\n'));
    assert.equal($('si-capture-script').selectionStart, 0);
    assert.equal($('si-capture-script').selectionEnd, $('si-capture-script').value.length);
    const archivos = [{ name: 'captura.js', size: 100, text: async () => 'window.__noEjecutar = true;' }];
    Object.defineProperty($('si-files'), 'files', { configurable: true, value: archivos });
    $('si-files').dispatchEvent(new w.Event('change')); await tick(); await tick();
    assert.equal(w.__noEjecutar, undefined, 'Los archivos importados no se ejecutan');
    assert.equal(pedidos.at(-1).opciones.headers['X-CSRF-Token'], 'prueba-csrf');
    assert.equal(JSON.parse(pedidos.at(-1).opciones.body).archivos[0].nombre, 'captura.js');
    assert.match($('si-status').textContent, /Importación completada/);
    const nuevos = [9, 10, 11].map((grado, i) => ({ id: 10 + i, perfil: perfil('Matemáticas', grado, 4), archivoOrigen: `grado-${grado}.js` }));
    perfilesServidor = [...registros, ...nuevos];
    const archivosNuevos = nuevos.map(r => ({ name: r.archivoOrigen, size: 100, text: async () => JSON.stringify(r.perfil) }));
    const arrastrar = (tipo, archivos = archivosNuevos, objetivo = $('si-import-zone')) => {
        const evento = new w.Event(tipo, { bubbles: true, cancelable: true });
        Object.defineProperty(evento, 'dataTransfer', { value: { types: ['Files'], files: archivos, dropEffect: 'none' } });
        objetivo.dispatchEvent(evento); return evento;
    };
    arrastrar('dragenter');
    assert.equal($('si-import-zone').dataset.dragging, 'true');
    arrastrar('dragenter', archivosNuevos, $('si-import-zone').querySelector('h2'));
    arrastrar('dragleave', archivosNuevos, $('si-import-zone').querySelector('h2'));
    assert.equal($('si-import-zone').dataset.dragging, 'true', 'Moverse dentro de la zona no elimina el resaltado');
    assert.equal(arrastrar('dragover').defaultPrevented, true, 'Permite soltar archivos');
    assert.equal(arrastrar('drop').defaultPrevented, true, 'Evita que el navegador abra los archivos soltados');
    await tick(); await tick();
    assert.equal($('si-import-zone').dataset.dragging, 'false');
    assert.equal($('si-import-zone').getAttribute('aria-busy'), 'false');
    assert.equal(JSON.parse(pedidos.at(-1).opciones.body).archivos.length, 3);
    assert.equal(pedidos.at(-1).opciones.headers['X-CSRF-Token'], 'prueba-csrf');
    assert.equal($('si-subject').value, ''); assert.equal($('si-grade').value, '');
    assert.deepEqual([...$('si-grade').options].map(o => o.value), ['', '6', '7', '9', '10', '11']);
    assert.equal($('si-rows').children.length, 6);
    assert.match($('si-status').textContent, /3 nuevas.*6 combinaciones guardadas en la base de datos/);
    $('si-grade').value = '11'; $('si-grade').dispatchEvent(new w.Event('change'));
    assert.equal($('si-rows').children.length, 1);
    $('si-form').dispatchEvent(new w.Event('submit', { bubbles: true, cancelable: true })); await tick();
    assert.equal($('si-filename').textContent, 'asignar-indicadores-Matematicas-grado-11-periodo-4.js');
    $('si-refresh').click(); await tick();
    assert.ok([...$('si-grade').options].some(o => o.value === '11'), 'Los grados importados se recuperan desde el servidor al actualizar');
    const antesInvalido = pedidos.length;
    arrastrar('drop', [{ name: 'invalido.txt', size: 10, text: async () => 'texto' }]); await tick();
    assert.equal(pedidos.length, antesInvalido);
    assert.equal($('si-status').dataset.error, 'true');
    assert.match($('si-status').textContent, /únicamente scripts/);
    $('sg-tab-periodos').dispatchEvent(new w.KeyboardEvent('keydown', { key: 'ArrowRight', bubbles: true }));
    assert.equal($('sg-notas').hidden, false, 'La navegación por teclado vuelve a la primera pestaña');
    $('sg-tab-notas').dispatchEvent(new w.KeyboardEvent('keydown', { key: 'End', bubbles: true }));
    assert.equal($('sp-periodos').hidden, false);
    $('sg-tab-capturas').click();
    assert.equal($('si-capturas').hidden, false);
    w.fetch = async () => ({ ok: false, json: async () => ({ status: 'error', message: 'Debe iniciar sesión.' }) });
    $('si-refresh').click(); await tick(); assert.match($('si-status').textContent, /Debe iniciar sesión/);
    assert.equal($('si-status').dataset.error, 'true');
    dom.window.close();
    console.log('OK: pestaña, carga de BD, filtros, script autónomo, copiar capturador con alternativa, arrastre múltiple, grados 9/10/11, CSRF, teclado y errores de sesión.');
}
main().catch(error => { console.error(error); process.exitCode = 1; });
