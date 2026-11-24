const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const { spawnSync } = require('node:child_process');
const root = path.resolve(__dirname, '..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const tick = () => new Promise(resolve => setImmediate(resolve));
const classes = () => ({ add() {}, remove() {} });
const attendanceFixtures = [
    { id: 1, estudiante: 'ANA PEREZ', documento: 'ana_perez', materia: 'MATEMATICAS', fecha: '02/02/2026', estado: 'NO', fecha_actualizacion: '2026-02-02 08:00:00' },
    { id: 2, estudiante: 'Ana Pérez', documento: '101', materia: 'Matemáticas', fecha: '2026-02-02', estado: 'P', fecha_actualizacion: '2026-02-05 09:00:00' },
    { id: 3, estudiante: 'ANA PEREZ', documento: 'ana_perez', materia: 'MATEMATICAS', fecha: '03/02/2026', estado: 'NO', fecha_actualizacion: '2026-02-03 08:00:00' },
    { id: 4, estudiante: 'Ana Pérez', documento: '101', materia: 'Matemáticas', fecha: '2026-02-03', estado: 'SI', fecha_actualizacion: '2026-02-05 09:00:00' },
    { id: 5, estudiante: 'Ana Pérez', documento: '101', materia: 'Matemáticas', fecha: '2026-02-04', estado: 'NO', fecha_actualizacion: '2026-02-04 08:00:00' },
    { id: 6, estudiante: 'ANA PEREZ', documento: 'ana_perez', materia: 'MATEMATICAS', fecha: '04/02/2026', estado: 'NO', fecha_actualizacion: '2026-02-04 08:00:00' },
    { id: 7, estudiante: 'Ana Pérez', documento: '101', materia: 'Geometría', fecha: '2026-02-02', estado: 'NO', fecha_actualizacion: '2026-02-02 08:00:00' },
    { id: 8, estudiante: 'Ana Pérez', documento: '101', materia: 'Historia', fecha: '2026-02-02', estado: 'SI', fecha_actualizacion: '2026-02-02 08:00:00' },
];

async function reportRequest() {
    const source = read('reportes/informe_asistencia.php');
    const code = source.slice(source.indexOf('    async function load()'), source.indexOf("    e.filter.addEventListener"));
    const calls = [], rendered = [];
    const e = { start: { value: '2026-01-01' }, end: { value: '2026-10-25' }, subject: { value: 'Educación Física' }, loader: { classList: classes() }, content: { classList: classes() }, feedback: { classList: classes() } };
    const context = vm.createContext({ URL, window: { location: { href: 'http://localhost/guagua/reportes/informe_asistencia.php?guagua_embedded=1&subject=Vieja' } }, e,
        feedback(message) { throw new Error(message); }, render(data) { rendered.push(data); },
        fetch: async url => { calls.push(url); return { ok: true, json: async () => ({ success: true, data: { students: [] } }) }; } });
    vm.runInContext(code, context);
    await context.load();
    assert.equal(calls[0].searchParams.get('guagua_embedded'), '1', 'El AJAX no debe redirigirse al contenedor HTML');
    assert.equal(calls[0].searchParams.get('subject'), 'Educación Física');
    assert.equal(calls[0].searchParams.get('action'), 'fetch_report_data');
    assert.equal(rendered.length, 1);
    e.subject.value = '';
    await context.load();
    assert.equal(calls[1].searchParams.has('subject'), false, 'Elegir todas debe retirar la asignatura anterior');
}

async function absenceRequest() {
    const source = read('apps/asistencia/index.php');
    const code = source.slice(source.indexOf('        function ejecutarSincronizacion()'), source.indexOf('        function switchTab('));
    const calls = [], notices = [];
    const context = vm.createContext({ URL, window: { location: { href: 'http://localhost/guagua/apps/asistencia/?guagua_embedded=1&exportar=csv' } },
        document: { getElementById: () => ({ classList: classes() }) }, setTimeout() {}, hideToast() {},
        showToast: (...args) => notices.push(args), fetch: async url => { calls.push(url); return { ok: true, json: async () => ({ success: true, message: 'Actualizado', fallos: 0 }) }; } });
    vm.runInContext(code, context);
    context.ejecutarSincronizacion(); await tick();
    assert.equal(calls[0].searchParams.get('guagua_embedded'), '1');
    assert.equal(calls[0].searchParams.get('sync_bd'), '1');
    assert.equal(calls[0].searchParams.has('exportar'), false, 'La sincronización no debe ejecutar una exportación CSV');
    assert.equal(notices.at(-1)[1], 'Actualizado');
    assert.match(source, /href="\?guagua_embedded=1&amp;exportar=csv"/, 'La descarga CSV debe evitar la redirección HTML');
    assert.match(source, /<script src="\.\.\/\.\.\/comun\/js\/tailwindcss\.js"><\/script>/, 'Tailwind debe cargarse con extensión y MIME JavaScript');
}

function absenceApi(query = {}, failure = false) {
    const source = read('sisga/api_inasistencias.php').replace(/require_once[^;]+;/g, '').replace('declare(strict_types=1);', '');
    const setup = `<?php declare(strict_types=1);
        function guagua_ano_configurado($db) { return 2031; }
        require 'asistencia/lib/registros.php';
        class TestAbsenceRows { private $rows; public function __construct() { $this->rows=json_decode('${JSON.stringify(attendanceFixtures)}',true); } public function fetch_assoc() { return array_shift($this->rows); } }
        class TestAbsenceStatement { public function bind_param($types, &$a, &$b) { if (substr($a,0,4)!==substr($b,0,4) || substr($a,4)!=='-01-01' || substr($b,4)!=='-12-31') throw new RuntimeException('Rango anual incorrecto'); } public function execute() { return true; } public function get_result() { return new TestAbsenceRows(); } public function close() {} }
        class TestAbsenceDb { public function prepare($sql) { if (preg_match('/WHERE\\s+UPPER/i',$sql)) throw new RuntimeException('No se debe filtrar NO antes de elegir el último registro'); ${failure ? "throw new RuntimeException('Consulta fallida');" : 'return new TestAbsenceStatement();'} } }
        $mysqli = new TestAbsenceDb(); $_GET = json_decode('${JSON.stringify(query)}', true);
        register_shutdown_function(function() { file_put_contents('php://stderr', 'HTTP=' . (http_response_code() ?: 200)); });
    ?>${source}`;
    const result = spawnSync('php', [], { input: setup, encoding: 'utf8', cwd: root });
    assert.equal(result.status, 0, result.stderr);
    return { data: JSON.parse(result.stdout), stderr: result.stderr };
}

function uniqueReportRecords() {
    const identities = [
        { id: 1, estudiante: 'Ana Pérez', documento: '101', materia: 'Matemáticas', fecha: '2026-02-02', estado: 'NO', fecha_actualizacion: '2026-02-02 08:00:00' },
        { id: 2, estudiante: 'ANA PEREZ', documento: '202', materia: 'Matemáticas', fecha: '2026-02-02', estado: 'P', fecha_actualizacion: '2026-02-02 09:00:00' },
        { id: 3, estudiante: 'Ana Pérez', documento: 'ana_perez', materia: 'Matemáticas', fecha: '2026-02-02', estado: 'NO', fecha_actualizacion: '2026-02-02 10:00:00' },
    ];
    const renamed = [
        { ...identities[0], documento: 'n/a999' },
        { ...identities[1], documento: 'n/a999', estudiante: 'Ana Gómez' },
    ];
    const code = `<?php
        require 'asistencia/lib/registros.php';
        $rows=json_decode('${JSON.stringify(attendanceFixtures)}',true);
        $unique=guagua_asistencia_registros_unicos($rows);
        if(count($unique)!==5 || $unique[0]['estado']!=='P' || $unique[1]['estado']!=='SI' || $unique[2]['id']!==6) throw new RuntimeException('Se duplicó un día o se perdió el registro más reciente');
        $faltas=guagua_asistencia_faltas(array_reverse($rows));
        if(array_sum(array_column($faltas,'faltas'))!==2) throw new RuntimeException('NO anterior a P/SI sigue contando como falta');
        $homonimos=guagua_asistencia_registros_unicos(json_decode('${JSON.stringify(identities)}',true));
        if(count($homonimos)!==3 || count(array_unique(array_column($homonimos,'clave_estudiante')))!==3) throw new RuntimeException('Se fusionaron homónimos o se enlazó un alias ambiguo');
        $renombrado=guagua_asistencia_registros_unicos(json_decode('${JSON.stringify(renamed)}',true));
        if(count($renombrado)!==1 || $renombrado[0]['estado']!=='P') throw new RuntimeException('Un cambio de nombre dividió el mismo documento VARCHAR');
        $aliasEstados=[array_replace($rows[0],['fecha'=>'2026-03-01','estado'=>'FALTA']),array_replace($rows[0],['fecha'=>'2026-03-02','estado'=>'AUSENTE'])];
        $canonicos=guagua_asistencia_registros_unicos($aliasEstados);
        if(array_column($canonicos,'estado')!==['NO','NO'] || array_sum(array_column(guagua_asistencia_faltas($aliasEstados),'faltas'))!==2) throw new RuntimeException('FALTA/AUSENTE no coincide entre informe y SISGA');
        echo 'OK';`;
    const result = spawnSync('php', [], { input: code, encoding: 'utf8', cwd: root });
    assert.equal(result.status, 0, result.stderr); assert.equal(result.stdout, 'OK');
}

function zeroAbsencesInjector() {
    const source = read('sisga/index.html');
    const start = source.indexOf('    const faltasDelEstudiante = ');
    const lookup = source.slice(start, source.indexOf('\n\n    try {', start));
    const assignmentStart = source.indexOf('            const faltas = faltasDelEstudiante(');
    const assignment = source.slice(assignmentStart, source.indexOf('            if (tuvoCambios)', assignmentStart));
    function run(faltasGuagua) {
        const changes = [];
        const context = vm.createContext({ faltasGuagua, mismoEstudiante: (a, b) => a === b, coincidenciaMateria: (a, b) => Number(a === b),
            datos: { nombre: 'Ana Pérez' }, config: { NombreAsignatura: 'Historia' }, columnaInasistencias: 3, fila: 2,
            spreadsheet: { setValueFromCoords: (...args) => changes.push(args) }, marcarCambioCalNum() {} });
        vm.runInContext(lookup + '\nlet tuvoCambios=false, inasistenciasActualizadas=0;\n' + assignment, context);
        return changes;
    }
    assert.equal(run([]).length, 0, 'Sin registros de asistencia debe conservar el valor de SISGA');
    assert.equal(run([{ estudiante: 'Otro alumno', materia: 'Historia', faltas: 0 }]).length, 0, 'Una coincidencia de materia sin alumno no debe borrar faltas');
    const zero = run([{ estudiante: 'Ana Pérez', materia: 'Historia', faltas: 0 }]);
    assert.equal(zero.length, 1); assert.equal(zero[0][2], '0', 'Un cero explícito debe corregir las faltas anteriores de SISGA');
}

(async () => {
    await reportRequest(); await absenceRequest();
    uniqueReportRecords();
    zeroAbsencesInjector();
    const configured = absenceApi();
    assert.equal(configured.data.anio, 2031, 'SISGA debe consultar el año lectivo configurado');
    assert.equal(configured.data.faltas.reduce((sum, row) => sum + row.faltas, 0), 2, 'Permiso/presente recientes deben reemplazar la falta anterior');
    assert.equal(configured.data.faltas.find(row => row.materia === 'Historia').faltas, 0, 'La API debe distinguir cero faltas de falta de datos');
    assert.equal(absenceApi({ anio: '2026' }).data.anio, 2026);
    const invalid = absenceApi({ anio: 'invalid' });
    assert.equal(invalid.data.ok, false); assert.match(invalid.stderr, /HTTP=422/);
    const failed = absenceApi({}, true);
    assert.equal(failed.data.ok, false); assert.match(failed.stderr, /HTTP=500/);
    const updates = [...read('asistencia/index.php').matchAll(/UPDATE asistencias SET[^";]+/g)];
    assert.equal(updates.length, 4);
    for (const [sql] of updates) assert.match(sql, /fecha_actualizacion=CURRENT_TIMESTAMP/, 'La corrección del estado debe actualizar su fecha de modificación');
    for (const file of ['sisga/index.html', 'reportes/informe_asistencia.php', 'apps/asistencia/index.php']) {
        for (const match of read(file).matchAll(/<script(?![^>]*\bsrc=)[^>]*>([\s\S]*?)<\/script>/gi)) new vm.Script(match[1], { filename: file });
    }
    console.log('OK: informe AJAX, filtro de asignatura, conteo sin duplicados, sincronización y CSV dentro del contenedor; año lectivo, errores JSON y sintaxis JavaScript.');
})().catch(error => { console.error(error); process.exitCode = 1; });
