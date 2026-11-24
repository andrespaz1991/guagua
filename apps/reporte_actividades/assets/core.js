(function (root) {
  'use strict';
  const statuses = ['missing', 'not_achieved', 'low', 'achieved', 'invalid'];
  function state(record, threshold = 3) {
    if (record.status === 'missing' || record.status === 'invalid' || record.status === 'not_achieved') return record.status;
    return record.score < threshold ? 'low' : 'achieved';
  }
  function normalize(value) {
    return String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es').trim();
  }
  function flatten(data) {
    const records = [];
    for (const source of data.sources) {
      const students = new Map(source.students.map(s => [s.id, s]));
      const activities = new Map(source.activities.map(a => [a.id, a]));
      for (const record of source.records) records.push({ ...record, student: students.get(record.studentId), activity: activities.get(record.activityId), source });
    }
    return records;
  }
  function filter(records, filters, ignore = []) {
    const terms = normalize(filters.query).split(/\s+/).filter(Boolean);
    return records.filter(r =>
      (ignore.includes('group') || !filters.group || r.student.group === filters.group) &&
      (ignore.includes('grade') || !filters.grade || r.student.grade === filters.grade) &&
      (ignore.includes('period') || !filters.period || r.source.period === filters.period) &&
      (ignore.includes('subject') || !filters.subject || r.source.subjectKey === filters.subject) &&
      (ignore.includes('activity') || !filters.activity || r.activityId === filters.activity) &&
      (ignore.includes('student') || !filters.student || r.studentId === filters.student) &&
      (ignore.includes('status') || !filters.status || (filters.status === 'support'
        ? ['not_achieved', 'low'].includes(state(r, filters.threshold))
        : state(r, filters.threshold) === filters.status)) &&
      (ignore.includes('query') || !terms.length || terms.every(term => normalize([
        r.student.name, r.student.document, r.activity.name, r.source.subject,
        r.activity.type, r.activity.key,
      ].join(' ')).includes(term))));
  }
  function summarize(records, threshold = 3) {
    const counts = Object.fromEntries(statuses.map(s => [s, 0]));
    let sum = 0, graded = 0;
    const students = new Set(), activities = new Set();
    for (const r of records) {
      counts[state(r, threshold)]++;
      if (r.score !== null) { sum += r.score; graded++; }
      students.add(r.studentId); activities.add(r.activityId);
    }
    return { ...counts, total: records.length, students: students.size, activities: activities.size, graded, mean: graded ? sum / graded : null, coverage: records.length ? graded / records.length * 100 : 0, alerts: counts.missing + counts.not_achieved + counts.low + counts.invalid };
  }
  function grouped(records, key, threshold = 3) {
    const groups = new Map();
    for (const record of records) { const id = key(record); if (!groups.has(id)) groups.set(id, []); groups.get(id).push(record); }
    return [...groups].map(([id, rows]) => ({ id, rows, ...summarize(rows, threshold) }));
  }
  /** Rangos 1-based para la interfaz; conserva el orden y el array original. */
  function paginate(items, page = 1, pageSize = 10) {
    const positiveInteger = (value, fallback) => {
      const numeric = Number(value);
      return Number.isFinite(numeric) && numeric > 0
        ? Math.max(1, Math.min(Number.MAX_SAFE_INTEGER, Math.floor(numeric)))
        : fallback;
    };
    const total = items.length;
    const size = pageSize === 'all' ? 'all' : positiveInteger(pageSize, 10);
    const pageCount = size === 'all' ? 1 : Math.max(1, Math.ceil(total / size));
    const current = Math.min(positiveInteger(page, 1), pageCount);
    const offset = size === 'all' ? 0 : (current - 1) * size;
    const end = size === 'all' ? total : Math.min(offset + size, total);
    return { items: items.slice(offset, end), total, page: current, pageSize: size,
      pageCount, start: total ? offset + 1 : 0, end };
  }
  function csv(records, threshold = 3) {
    const labels = { missing: 'Falta nota', not_achieved: 'No alcanzó (1)', low: 'Bajo desempeño', achieved: 'Alcanzó', invalid: 'Revisar dato' };
    const escape = v => { let text = String(v ?? ''); if (/^[=+@\-\t\r\n]/.test(text)) text = "'" + text; return '"' + text.replace(/"/g, '""') + '"'; };
    const rows = [['Grupo', 'Grado', 'Período', 'Materia', 'Estudiante', 'Documento en archivo', 'Tipo', 'Número', 'Actividad', 'Fecha', 'Nota', 'Estado', 'Archivo', 'Hoja', 'Celda']];
    for (const r of records) rows.push([r.student.group, r.student.grade, r.source.period, r.source.subject, r.student.name, r.student.document, r.activity.type, r.activity.number, r.activity.name, r.activity.date, r.score === null ? r.raw : String(r.score).replace('.', ','), labels[state(r, threshold)], r.source.file, r.source.sheet, r.cell]);
    return '\ufeff' + rows.map(row => row.map(escape).join(';')).join('\r\n');
  }
  const api = { statuses, state, normalize, flatten, filter, summarize, grouped, paginate, csv };
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
  root.ActividadesCore = api;
})(typeof globalThis === 'undefined' ? window : globalThis);
