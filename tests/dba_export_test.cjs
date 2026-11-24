const assert = require('node:assert/strict');
const { selectData, flatRows, serialize } = require('../apps/dba/assets/js/exportar.js');

const data = {
    grados: [{ id_grado: '6', nombre: 'Sexto' }, { id_grado: '7', nombre: 'Séptimo' }],
    materias: [{ id_materia: '1', nombre_materia: 'Ciencias' }, { id_materia: '2', nombre_materia: 'Matemáticas' }],
    estandares: [
        { id_estandar: '10', grado: '6', id_materia_oficial: '1', id_periodo: '2', nombre_estandar: 'Comprende "células", tejidos\ny órganos', descripcion_estandar: 'Descripción áéíóú ñ' },
        { id_estandar: '11', grado: '6', id_materia_oficial: '1', id_periodo: '1', nombre_estandar: 'Otro periodo' },
        { id_estandar: '12', grado: '7', id_materia_oficial: '1', id_periodo: '2', nombre_estandar: 'Otro grado' },
        { id_estandar: '13', grado: '6', id_materia_oficial: '2', id_periodo: '2', nombre_estandar: 'Otra materia' },
        { id_estandar: '14', grado: '6', id_materia_oficial: '1', id_periodo: '2', nombre_estandar: 'Sin DBA' }
    ],
    dbas: [
        { id_dba: '20', id_estandar: '10', nombre_dba: '=1+1', descripcion_dba: 'Con evidencias', palabras_clave: 'célula, tejido' },
        { id_dba: '21', id_estandar: '10', nombre_dba: 'Sin hijos' },
        { id_dba: '22', id_estandar: '11', nombre_dba: 'Fuera del periodo' }
    ],
    ejes_tematicos: [
        { id_eje_tematico: '30', id_dba: '20', nombre_eje_tematico: 'Eje A', descripcion_eje_tematico: 'Descripción A' },
        { id_eje_tematico: '31', id_dba: '20', nombre_eje_tematico: 'Eje B' },
        { id_eje_tematico: '32', id_dba: '22', nombre_eje_tematico: 'Fuera' }
    ],
    evidencias: [
        { id_evidencia_aprendizaje: '40', id_dba: '20', descripcion_evidencia: 'Explica; "compara"\nórganos', origen: 'ia_pendiente', modelo: 'modelo local', fecha_creacion: '2026-09-13 10:00:00', campo_adicional: 'Se conserva' },
        { id_evidencia_aprendizaje: '41', id_dba: '20', descripcion_evidencia: '\t=2+2', origen: 'manual' },
        { id_evidencia_aprendizaje: '42', id_dba: '22', descripcion_evidencia: 'Fuera' }
    ]
};
const selected = selectData(data, { grado: 6, materia: '1', periodo: '2' }, new Date('2026-09-13T15:00:00Z'));
assert.deepEqual(selected.totales, { estandares: 2, dbas: 2, ejes_tematicos: 2, evidencias: 2 });
assert.deepEqual(selected.estandares.map(e => e.id_estandar), ['10', '14']);
assert.deepEqual(selected.grados, [data.grados[0]]);
assert.deepEqual(selected.materias, [data.materias[0]]);
assert.equal(selectData(data, { periodo: '2' }).estandares.length, 4);
assert.equal(selectData(data, { grado: '6' }).estandares.length, 4);
assert.equal(selectData(data, { materia: '1' }).estandares.length, 4);
assert.equal(selectData(data).estandares.length, 5);
assert.equal(selectData(data, { periodo: '4' }).dbas.length, 0);
assert.equal(flatRows(selected).length, 8, 'No multiplicar ejes por evidencias ni perder padres sin hijos');
assert.deepEqual(JSON.parse(serialize(selected, 'json').text), selected);
assert.throws(() => serialize(selected, 'html'), /no admitido/);

// Analizador independiente: comprueba estructura CSV y recuperación de celdas multilínea.
function parseCsv(input) {
    const rows = []; let row = [], cell = '', quoted = false;
    input = input.replace(/^\uFEFF/, '');
    for (let i = 0; i < input.length; i++) {
        const c = input[i];
        if (c === '"') {
            if (quoted && input[i + 1] === '"') { cell += '"'; i++; }
            else quoted = !quoted;
        } else if (c === ',' && !quoted) { row.push(cell); cell = ''; }
        else if (c === '\r' && input[i + 1] === '\n' && !quoted) {
            row.push(cell); rows.push(row); row = []; cell = ''; i++;
        } else cell += c;
    }
    assert.equal(quoted, false);
    return rows;
}
const csv = serialize(selected, 'csv').text;
assert.equal(csv.charCodeAt(0), 0xFEFF, 'CSV UTF-8 compatible con tildes en Excel');
const [header, ...rows] = parseCsv(csv);
assert.equal(rows.length, 8);
assert.ok(rows.every(row => row.length === header.length));
const objects = rows.map(row => Object.fromEntries(header.map((key, i) => [key, row[i]])));
assert.equal(objects[0].estandar, data.estandares[0].nombre_estandar);
assert.equal(objects.find(row => row.tipo_registro === 'dba').dba, "'=1+1");
assert.equal(objects.find(row => row.id_evidencia_aprendizaje === '40').evidencia, data.evidencias[0].descripcion_evidencia);
assert.equal(objects.find(row => row.id_evidencia_aprendizaje === '41').evidencia, "'\t=2+2");
assert.deepEqual(JSON.parse(objects.find(row => row.id_evidencia_aprendizaje === '40').datos_evidencia), data.evidencias[0]);
const txt = serialize(selected, 'txt').text;
for (const value of ['Sexto', 'Ciencias', 'Periodo: 2', 'Descripción áéíóú ñ', 'célula, tejido', 'Descripción A', 'ia_pendiente', 'modelo local', 'Se conserva', 'Sin DBA', 'Sin hijos']) assert.ok(txt.includes(value), value);
assert.ok(!txt.includes('Otro periodo'));
assert.equal(JSON.stringify(data.dbas[0].nombre_dba), '"=1+1"', 'La exportación no modifica el origen');
console.log('OK: filtros individuales/combinados, relaciones, registros sin hijos, JSON completo, CSV multilínea/UTF-8/fórmulas y TXT.');

module.exports = data;
