// node tests/sisga_periodos_test.cjs <directorio con node_modules/jsdom> [lista_periodo.html]
const fs = require('node:fs'), path = require('node:path'), vm = require('node:vm'), assert = require('node:assert/strict');
const { createRequire } = require('node:module');
const { JSDOM } = createRequire(path.resolve(process.argv[2] || '.', 'package.json'))('jsdom');
const raiz = path.join(__dirname, '..');
const leer = archivo => fs.readFileSync(path.join(raiz, archivo), 'utf8');
const runtime = leer('sisga/periodos-runtime.js');
const sesiones = [];
const tick = () => new Promise(resolve => setTimeout(resolve, 0));

function fuenteOriginal(archivo) {
    const html = fs.readFileSync(archivo, 'utf8');
    const vista = new JSDOM(html);
    const lineas = [...vista.window.document.querySelectorAll('td.line-content')];
    const fuente = lineas.length ? lineas.map(td => td.textContent).join('\n') : html;
    vista.window.close(); return fuente;
}
function tablaEjemplo() {
    return '<table id="tabla-carga-academica"><thead><tr>' + ['Sede', 'Grado', 'Grupo', 'Materia', 'Nivel', 'Período', 'Acciones'].map(t => `<th>${t}</th>`).join('')
        + '</tr></thead><tbody>' + Array.from({ length: 42 }, (_, i) => `<tr><td>Sede</td><td>6</td><td>Grupo</td><td>Materia ${i}</td><td>0</td><td><select class="sel-periodo"><option value="3" selected>3</option><option value="4">4</option><option value="5">5</option></select></td><td data-gra="74" data-sgra="6" data-sasi="Materia ${i}" data-sgru="Grupo"><button class="js-definitiva">Definitiva</button></td></tr>`).join('') + '</tbody></table>';
}
function iniciar(html, paginada = true) {
    const dom = new JSDOM(html, { url: 'https://www.sisga.com.co/Saga/calificacionNumerica/EvaluacionesParciales', runScripts: 'outside-only' });
    sesiones.push(dom);
    const w = dom.window, tabla = w.document.getElementById('tabla-carga-academica');
    const avisos = [], errores = [], mensajes = [];
    w.console = { info: texto => mensajes.push(texto), warn: texto => avisos.push(texto), error: texto => errores.push(texto), table: () => {} };
    w.fetch = () => { throw new Error('No debe solicitar datos al servidor.'); };
    w.open = () => { throw new Error('No debe abrir planillas.'); };
    let clics = 0; w.document.addEventListener('click', () => clics++);
    let dt = null, cambios = 0;
    if (paginada) {
        w.eval(leer('comun/js/jquery-3.6.0.min.js'));
        w.eval(leer('comun/js/jquery.dataTables.min.js'));
        const jq = w.jQuery;
        jq(tabla).on('change', '.sel-periodo', function () {
            cambios++;
            jq(this).closest('tr').find('.js-definitiva').toggleClass('d-none', this.value !== '5');
        });
        dt = jq(tabla).DataTable({ pageLength: 5, order: [], autoWidth: false, columnDefs: [{ targets: [-1, -2], orderable: false }] });
    }
    w.eval(runtime);
    const filas = () => dt ? dt.rows({ search: 'none', page: 'all' }).nodes().toArray() : [...tabla.querySelectorAll('tbody > tr')];
    const selects = () => filas().map(f => f.querySelector('.sel-periodo')).filter(Boolean);
    return { dom, w, tabla, dt, avisos, errores, mensajes, filas, selects, cambios: () => cambios, clics: () => clics };
}

async function main() {
    const html = process.argv[3] ? fuenteOriginal(process.argv[3]) : tablaEjemplo();
    const t = iniciar(html);
    const total = t.filas().length;
    assert.ok(total > 5, 'La prueba necesita más de una página');
    t.dt.page(2).draw('page');
    const pagina = t.dt.page();
    const r = t.w.SisgaPeriodosRuntime({ periodo: 4 });
    assert.equal(r.materias, total); assert.equal(r.seleccionadas, total);
    assert.ok(t.selects().every(s => s.value === '4'), 'Cambia las filas de todas las páginas, incluso las separadas del DOM');
    assert.equal(t.dt.page(), pagina, 'Conserva la página actual');
    assert.equal(t.dt.page.len(), 5, 'Conserva el tamaño de página');
    assert.equal(t.clics(), 0, 'No pulsa botones ni abre planillas');
    const otra = t.filas().find(f => !f.isConnected);
    assert.ok(otra, 'DataTables mantiene filas separadas del DOM');
    otra.querySelector('.sel-periodo').value = '3';
    t.dt.page('next').draw('page');
    assert.ok(t.selects().every(s => s.value === '4'), 'Reaplica al cambiar de página');
    t.dt.search('Matem').draw();
    const busqueda = t.dt.search();
    const repetido = t.w.SisgaPeriodosRuntime({ periodo: 4 });
    assert.equal(repetido.yaSeleccionadas, total); assert.equal(repetido.cambiadas, 0);
    assert.equal(t.dt.search(), busqueda, 'No borra la búsqueda');
    assert.equal(repetido.seleccionadas, total, 'Incluye las materias excluidas por la búsqueda');
    t.dt.search('').draw();
    t.w.SisgaPeriodosRuntime({ periodo: 5 });
    t.dt.page(0).draw('page');
    assert.ok(t.selects().every(s => s.value === '5'), 'Cambiar de período reemplaza el mantenimiento anterior');
    assert.ok([...t.tabla.querySelectorAll('.js-definitiva')].every(b => !b.classList.contains('d-none')), 'Se actualizan los controles delegados de las filas visibles');
    assert.ok(t.cambios() > 0);
    t.w.PeriodosSISGA.detener();
    t.selects()[0].value = '3'; t.dt.draw(false);
    assert.equal(t.selects()[0].value, '3', 'Se puede detener el mantenimiento');
    assert.throws(() => t.w.SisgaPeriodosRuntime({ periodo: 0 }), /mayor que cero/);
    assert.throws(() => t.w.SisgaPeriodosRuntime({ periodo: 1.5 }), /entero/);
    const omitido = t.w.SisgaPeriodosRuntime({ periodo: 99 });
    assert.equal(omitido.seleccionadas, 0); assert.equal(omitido.omitidas, total); assert.ok(t.avisos.length);
    t.w.PeriodosSISGA.detener();
    t.dt.settings()[0].oFeatures.bServerSide = true;
    assert.throws(() => t.w.SisgaPeriodosRuntime({ periodo: 4 }), /servidor/);
    t.dt.settings()[0].oFeatures.bServerSide = false;

    const simple = iniciar(html, false);
    const control = simple.w.document.createElement('select'); control.id = 'otro-periodo';
    control.innerHTML = '<option value="3">3</option><option value="4">4</option>'; simple.w.document.body.append(control);
    const originales = simple.selects();
    originales[0].disabled = true;
    originales[1].querySelector('[value="4"]').remove();
    originales[2].querySelector('[value="4"]').disabled = true;
    const parcial = simple.w.SisgaPeriodosRuntime({ periodo: 4 });
    assert.equal(parcial.omitidas, 3); assert.equal(parcial.seleccionadas, total - 3);
    assert.equal(control.value, '3', 'No toca otros selectores de la página');
    assert.equal(simple.w.PeriodosSISGA.informe().length, total);

    const ui = new JSDOM(leer('sisga/index.html'), { url: 'http://localhost/guagua/sisga/index.html?guagua_embedded=1#periodos', runScripts: 'outside-only' });
    sesiones.push(ui);
    const w = ui.window, $ = id => w.document.getElementById(id), descargas = [];
    w.HTMLElement.prototype.scrollIntoView = () => {};
    w.URL.createObjectURL = () => 'blob:prueba'; w.URL.revokeObjectURL = () => {};
    w.HTMLAnchorElement.prototype.click = function () { descargas.push(this.download); };
    for (const archivo of ['indicadores-core.js', 'indicadores-runtime.js', 'indicadores-ui.js', 'periodos-runtime.js', 'periodos-ui.js']) w.eval(leer('sisga/' + archivo));
    assert.equal($('sp-periodos').hidden, false); assert.equal($('sg-notas').hidden, true);
    $('sp-periodo').value = '4'; $('sp-form').dispatchEvent(new w.Event('submit', { cancelable: true, bubbles: true }));
    assert.equal($('sp-result').hidden, false);
    assert.equal($('sp-filename').textContent, 'seleccionar-periodo-4-todas-las-materias.js');
    const generado = $('sp-script').value; new vm.Script(generado);
    const autonomo = iniciar(html);
    autonomo.w.SisgaPeriodosRuntime = undefined;
    autonomo.w.eval(generado);
    assert.equal(autonomo.w.PeriodosSISGA.resumen.seleccionadas, total, 'El script exportado funciona sin el generador');
    $('sp-download').click(); assert.equal(descargas[0], 'seleccionar-periodo-4-todas-las-materias.js');
    $('sp-periodo').value = '3'; $('sp-periodo').dispatchEvent(new w.Event('input'));
    assert.equal($('sp-result').hidden, true, 'Invalida el script cuando cambia el período');
    $('sp-periodo').value = '0'; $('sp-form').dispatchEvent(new w.Event('submit', { cancelable: true }));
    assert.equal($('sp-status').dataset.error, 'true');
    await tick();
    console.log(`OK: ${total} materias del HTML, DataTables real, páginas separadas del DOM, filtros, cambio de período, controles delegados, omisiones, restricciones, pestaña y script autónomo.`);
}
main().catch(error => { console.error(error); process.exitCode = 1; }).finally(() => sesiones.forEach(s => s.window.close()));
