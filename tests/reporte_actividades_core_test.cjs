const assert = require('node:assert/strict');
const C = require('../apps/reporte_actividades/assets/core.js');
const student = { name: '=HYPERLINK("bad")', document: '123', grade: '6', group: '6-8' };
const source = { subjectKey: 'matematicas', subject: 'Matemáticas', period: '3', file: 'notas.xlsx', sheet: 'valoraciones' };
const activity = { id: 'a', type: 'Taller', number: '1', name: 'Portada; personal', date: '', grades: [] };
const base = { student, source, activity, studentId: 's', activityId: 'a', sourceId: 'src', cell: 'F12' };
const records = [
  { ...base, status: 'missing', score: null, raw: '' },
  { ...base, status: 'not_achieved', score: 1, raw: '1' },
  { ...base, status: 'graded', score: 2.5, raw: '2,5' },
  { ...base, status: 'graded', score: 3, raw: '3' },
  { ...base, status: 'graded', score: 5, raw: '5' },
  { ...base, status: 'invalid', score: null, raw: '0' },
];
const stats = C.summarize(records);
assert.equal(stats.missing, 1); assert.equal(stats.not_achieved, 1);
assert.equal(stats.low, 1); assert.equal(stats.achieved, 2); assert.equal(stats.invalid, 1);
assert.equal(stats.total, 6); assert.equal(stats.graded, 4);
assert.equal(stats.mean, 2.875); assert.equal(stats.students, 1); assert.equal(stats.activities, 1);
assert.equal(C.summarize(records, 3.5).low, 2);
assert.equal(C.filter(records, { status: 'missing', threshold: 3 }).length, 1);
assert.equal(C.filter(records, { grade: '7' }).length, 0);
assert.equal(C.filter(records, { period: '2' }).length, 0);
assert.equal(C.filter(records, { activity: 'b' }).length, 0);
assert.equal(C.filter(records, { student: 'another' }).length, 0);
assert.equal(C.filter(records, { status: 'support', threshold: 3 }).length, 2);
assert.equal(C.filter(records, { status: 'support', threshold: 3.5 }).length, 3);
assert.equal(C.filter(records, { status: 'support', threshold: 3 }, ['status']).length, 6);
assert.equal(C.filter(records, { query: 'MATEMATICAS portada' }).length, 6);
assert.equal(C.filter(records, { query: '123' }).length, 6);
assert.equal(C.filter(records, { query: 'matematicas otra actividad' }).length, 0);
assert.equal(C.filter(records, { query: 'sin coincidencia' }, ['query']).length, 6);
assert.equal(C.normalize('  MÉTODOS ÁNGEL  '), 'metodos angel');
assert.equal(C.grouped(records, r => r.studentId)[0].total, 6);
const csv = C.csv(records);
assert.ok(csv.startsWith('\ufeff')); assert.ok(csv.includes("\"'=HYPERLINK"));
assert.ok(csv.includes('"Portada; personal"')); assert.ok(csv.includes('"Falta nota"'));
assert.equal(C.summarize([]).mean, null); assert.equal(C.summarize([]).coverage, 0);
const elements = Object.freeze(Array.from({ length: 23 }, (_, index) => index + 1));
assert.deepEqual(C.paginate(elements).items, elements.slice(0, 10));
const lastPage = C.paginate(elements, 3, 10);
assert.deepEqual(lastPage, { items: [21, 22, 23], total: 23, page: 3, pageSize: 10, pageCount: 3, start: 21, end: 23 });
assert.deepEqual(C.paginate(elements, '2.9', '10.9').items, elements.slice(10, 20));
assert.equal(C.paginate(elements, -3, -1).page, 1);
assert.equal(C.paginate(elements, -3, -1).pageSize, 10);
assert.equal(C.paginate(elements, Infinity, NaN).pageSize, 10);
assert.equal(C.paginate(elements, Number.MAX_VALUE, 10).page, 3);
assert.equal(C.paginate(elements, 1, 0.5).pageSize, 1);
const afterFiltering = C.paginate(elements.slice(0, 4), 3, 10);
assert.equal(afterFiltering.page, 1); assert.equal(afterFiltering.start, 1); assert.equal(afterFiltering.end, 4);
assert.deepEqual(C.paginate([], 5, 25), { items: [], total: 0, page: 1, pageSize: 25, pageCount: 1, start: 0, end: 0 });
const printPage = C.paginate(elements, 2, 'all');
assert.deepEqual(printPage.items, elements); assert.notEqual(printPage.items, elements);
assert.equal(printPage.page, 1); assert.equal(printPage.pageSize, 'all'); assert.equal(printPage.pageCount, 1);
assert.equal(printPage.start, 1); assert.equal(printPage.end, elements.length);
assert.equal(elements.length, 23); assert.equal(elements[0], 1);
console.log('OK: conteos, filtros, búsqueda, CSV seguro y paginación (límites, vacíos, cambios de filtro e impresión completa).');
