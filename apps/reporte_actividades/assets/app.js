(() => {
  'use strict';
  const C = window.ActividadesCore;
  const $ = id => document.getElementById(id);
  const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const number = (value, decimals = 0) => value === null ? '—' : new Intl.NumberFormat('es-CO', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }).format(value);
  const icon = name => `<svg class="ra-icon" aria-hidden="true"><use href="#ra-i-${name}"/></svg>`;
  const labels = { missing: 'Falta nota', not_achieved: 'No alcanzó (1)', low: 'Bajo desempeño', support: 'Necesita refuerzo', achieved: 'Alcanzó', invalid: 'Revisar dato' };
  const colors = { missing: '#e7ac4c', not_achieved: '#d57580', low: '#a691c7', achieved: '#409c8d', invalid: '#9aa9b7' };
  const filterIds = ['group', 'grade', 'period', 'subject', 'activity', 'student', 'status', 'threshold'];
  let data = null, allRecords = [], scoped = [], visible = [], currentView = 'overview';
  let sorting = 'alerts', activitySorting = 'alerts', compact = false;
  let dialogStudent = '', dialogRows = [], dialogStatus = '', searchTimer, toastTimer;
  let printDetails = [];
  let printMode = false, lastFilterSignature = '';
  const pages = new Map(), matrixOpen = new Map();
  const filters = () => ({ ...Object.fromEntries(filterIds.map(id => [id, id === 'threshold' ? Number($(id).value) : $(id).value])), query: $('search').value.trim() });
  const plural = (count, single, multiple = single + 's') => `${number(count)} ${count === 1 ? single : multiple}`;
  const initials = name => name.trim().split(/\s+/).slice(0, 2).map(s => s[0]).join('');
  const avatar = name => `<span class="ra-avatar" aria-hidden="true">${escape(initials(name))}</span>`;
  const pill = (state, text = labels[state]) => `<span class="ra-pill ${state}"><i></i>${escape(text)}</span>`;
  const sortGroups = groups => groups.sort((a, b) => b.alerts - a.alerts || b.missing - a.missing || (a.mean ?? 6) - (b.mean ?? 6));
  const grouped = (rows, key) => C.grouped(rows, key, filters().threshold);
  const studentGroups = rows => sortGroups(grouped(rows, r => r.studentId));
  const activityGroups = rows => sortGroups(grouped(rows, r => r.activityId));
  const activityLabel = r => `${r.source.subject} · ${r.activity.type} ${r.activity.number}: ${r.activity.name}${r.activity.grades.length ? ' · Grado ' + r.activity.grades.join(', ') : ''} · ${r.student.group}`;
  function optionList(id, options, first) {
    const selected = $(id).value;
    $(id).innerHTML = `<option value="">${first}</option>` + options.map(([value, label]) => `<option value="${escape(value)}">${escape(label)}</option>`).join('');
    if (options.some(([value]) => value === selected)) $(id).value = selected;
  }
  function uniqueOptions(rows, value, label) {
    return [...new Map(rows.map(r => [value(r), label(r)]))].sort((a, b) => a[1].localeCompare(b[1], 'es', { numeric: true }));
  }
  function updateOptions(changed = '') {
    if (changed === 'group') { $('grade').value = ''; $('activity').value = ''; $('student').value = ''; }
    if (['grade', 'period', 'subject'].includes(changed)) { $('activity').value = ''; $('student').value = ''; }
    const inGroup = C.filter(allRecords, filters(), ['grade', 'period', 'subject', 'activity', 'student', 'status', 'query']);
    optionList('grade', uniqueOptions(inGroup, r => r.student.grade, r => `Grado ${r.student.grade}`), 'Todos los grados');
    const inGrade = C.filter(allRecords, filters(), ['period', 'subject', 'activity', 'student', 'status', 'query']);
    optionList('period', uniqueOptions(inGrade, r => r.source.period, r => `Período ${r.source.period || 'sin especificar'}`), 'Todos los períodos');
    const inPeriod = C.filter(allRecords, filters(), ['subject', 'activity', 'student', 'status', 'query']);
    optionList('subject', uniqueOptions(inPeriod, r => r.source.subjectKey, r => r.source.subject), 'Todas las materias');
    const context = C.filter(allRecords, filters(), ['activity', 'student', 'status', 'query']);
    optionList('activity', uniqueOptions(context, r => r.activityId, activityLabel), 'Todas las actividades');
    const inActivity = C.filter(allRecords, filters(), ['student', 'status', 'query']);
    optionList('student', uniqueOptions(inActivity, r => r.studentId, r => `${r.student.name} · ${r.student.grade}°`), 'Todos los estudiantes');
  }
  function toggleAdvanced(open = $('advanced-filters').hidden) {
    $('advanced-filters').hidden = !open; $('toggle-filters').setAttribute('aria-expanded', String(open));
  }
  function drawFilterChips() {
    const f = filters(), names = { group: 'Grupo', grade: 'Grado', period: 'Período', subject: 'Materia', activity: 'Actividad', student: 'Estudiante', status: 'Estado', threshold: 'Nota mínima' };
    const active = filterIds.filter(id => id === 'threshold' ? f[id] !== 3 : Boolean(f[id]));
    $('filter-chips').innerHTML = active.map(id => `<button class="ra-filter-chip" data-remove-filter="${id}" aria-label="Quitar filtro ${names[id]}"><span>${escape($(id).selectedOptions[0]?.textContent || f[id])}</span>${icon('close')}</button>`).join('') + (f.query ? `<button class="ra-filter-chip" data-remove-filter="query" aria-label="Quitar búsqueda"><span>${icon('search')}${escape(f.query)}</span>${icon('close')}</button>` : '');
    $('active-filters').hidden = !active.length && !f.query;
    const extra = ['activity', 'student', 'status'].filter(id => f[id]).length + (f.threshold !== 3 ? 1 : 0);
    $('advanced-count').hidden = !extra; $('advanced-count').textContent = extra;
    const summary = active.map(id => `${names[id]}: ${$(id).selectedOptions[0]?.textContent || f[id]}`);
    if (f.query) summary.push('Búsqueda: ' + f.query);
    $('print-context').textContent = summary.length ? summary.join(' / ') : 'Todos los grupos, grados y materias';
  }
  function progress(value) { return `<span class="ra-progress" aria-hidden="true"><i style="width:${Math.max(0, Math.min(100, value))}%"></i></span>`; }
  function drawMetrics(stats) {
    const support = stats.not_achieved + stats.low, subjects = new Set(scoped.map(r => r.source.subjectKey)).size;
    $('metrics').innerHTML = `
      <button class="ra-metric" data-switch="students"><span class="ra-metric-head">Estudiantes${icon('users')}</span><strong>${number(stats.students)}</strong><span class="ra-metric-note">${plural(subjects, 'materia')} · ${plural(stats.activities, 'actividad', 'actividades')}</span><span class="ra-metric-link">Ver seguimiento ${icon('arrow')}</span></button>
      <button class="ra-metric ra-metric-pending ${filters().status === 'missing' ? 'selected' : ''}" data-focus-status="missing"><span class="ra-metric-head">Notas pendientes${icon('clock')}</span><strong>${number(stats.missing)}</strong><span class="ra-metric-note">${number(stats.total ? stats.missing / stats.total * 100 : 0)}% de los registros de tu selección</span><span class="ra-metric-link">Revisar pendientes ${icon('arrow')}</span></button>
      <button class="ra-metric ra-metric-support ${filters().status === 'support' ? 'selected' : ''}" data-focus-status="support"><span class="ra-metric-head">Notas para refuerzo${icon('alert')}</span><strong>${number(support)}</strong><span class="ra-metric-note"><b>${number(stats.not_achieved)}</b> con nota 1 · <b>${number(stats.low)}</b> bajo desempeño</span><span class="ra-metric-link">Ver resultados ${icon('arrow')}</span></button>
      <article class="ra-metric ra-metric-coverage"><span class="ra-metric-head">Registro de notas${icon('check')}</span><strong>${number(stats.coverage)}<small>%</small></strong><span class="ra-metric-note">${number(stats.graded)} de ${number(stats.total)} notas válidas</span>${progress(stats.coverage)}</article>`;
    $('students-count').textContent = stats.students; $('activities-count').textContent = stats.activities;
  }
  function drawQuickFilters(stats) {
    const options = [['', 'Todas', stats.total], ['missing', 'Sin nota', stats.missing], ['not_achieved', 'Con nota 1', stats.not_achieved], ['low', 'Bajo desempeño', stats.low]];
    if (stats.invalid || filters().status === 'invalid') options.push(['invalid', 'Revisar dato', stats.invalid]);
    if (filters().status === 'support') options.push(['support', 'Necesita refuerzo', stats.not_achieved + stats.low]);
    if (filters().status === 'achieved') options.push(['achieved', 'Alcanzó', stats.achieved]);
    $('quick-filters').innerHTML = options.map(([state, label, count]) => `<button class="ra-status-tab ${filters().status === state ? 'active' : ''}" data-status="${state}" aria-pressed="${filters().status === state}">${state ? `<i style="background:${colors[state] || colors.not_achieved}"></i>` : ''}${label}<span>${number(count)}</span></button>`).join('');
    $('filter-summary').textContent = visible.length === stats.total ? plural(visible.length, 'registro') : `${number(visible.length)} de ${number(stats.total)} registros`;
  }
  const legend = () => `<div class="ra-legend">${C.statuses.map(s => `<span><i style="background:${colors[s]}"></i>${labels[s]}</span>`).join('')}</div>`;
  function stacked(stats) {
    return `<span class="ra-bar" role="img" aria-label="${escape(C.statuses.map(s => labels[s] + ': ' + stats[s]).join(', '))}">${C.statuses.map(s => `<i style="width:${stats.total ? stats[s] / stats.total * 100 : 0}%;background:${colors[s]}" title="${labels[s]}: ${stats[s]}"></i>`).join('')}</span>`;
  }
  const panelHead = (title, subtitle, action = '') => `<div class="ra-panel-head"><div><h2>${title}</h2><p>${subtitle}</p></div>${action}</div>`;
  const viewHead = (title, subtitle, action = '') => `<div class="ra-view-heading"><div><h2>${title}</h2><p>${subtitle}</p></div>${action}</div>`;
  const empty = (title, text, reset = true) => `<div class="ra-empty">${icon('search')}<h2>${escape(title)}</h2><p>${escape(text)}</p>${reset ? '<button class="ra-button" data-reset>Limpiar filtros</button>' : ''}</div>`;
  function pageOf(items, key, defaultSize = 10) {
    if (!pages.has(key)) pages.set(key, { page: 1, size: defaultSize });
    const state = pages.get(key), result = C.paginate(items, state.page, printMode ? 'all' : state.size);
    if (!printMode) state.page = result.page;
    return result;
  }
  function pagination(key, page, noun, compactFooter = false) {
    if (printMode) return '';
    const singular = { estudiantes: 'estudiante', actividades: 'actividad', archivos: 'archivo', secciones: 'sección' };
    const range = `${number(page.start)}–${number(page.end)} de ${plural(page.total, singular[noun] || noun, noun)}`;
    if (compactFooter && page.pageCount === 1) return `<div class="ra-table-range no-print">${range}</div>`;
    const state = pages.get(key), sizes = key === 'matrix' ? [5, 10, 25] : [10, 25, 50];
    const candidates = new Set([1, page.pageCount, page.page - 1, page.page, page.page + 1]);
    if (page.page <= 3) [2, 3, 4].forEach(n => candidates.add(n));
    if (page.page >= page.pageCount - 2) [page.pageCount - 3, page.pageCount - 2, page.pageCount - 1].forEach(n => candidates.add(n));
    const numbers = [...candidates].filter(n => n >= 1 && n <= page.pageCount).sort((a, b) => a - b);
    const button = (target, text, label, disabled = false, active = false) => `<button class="ra-page-button${active ? ' active' : ''}" data-page-key="${escape(key)}" data-page-target="${target}" aria-label="${escape(label)}" ${disabled ? 'disabled' : ''}${active ? ' aria-current="page"' : ''}>${text}</button>`;
    return `<nav class="ra-pagination no-print" aria-label="Paginación de ${escape(noun)}"><span class="ra-page-range" aria-live="polite">${range}</span><label class="ra-page-size">${key === 'matrix' ? 'Secciones' : 'Filas'} por página<select data-page-size="${escape(key)}" aria-label="${key === 'matrix' ? 'Secciones' : 'Filas'} por página de ${escape(noun)}">${sizes.map(size => `<option value="${size}" ${state.size === size ? 'selected' : ''}>${size}</option>`).join('')}</select></label><div class="ra-page-buttons">${button(page.page - 1, '‹', 'Página anterior de ' + noun, page.page === 1)}${numbers.map((n, i) => (i && n - numbers[i - 1] > 1 ? '<span class="ra-page-gap" aria-hidden="true">…</span>' : '') + button(n, number(n), 'Página ' + n + ' de ' + noun, false, n === page.page)).join('')}${button(page.page + 1, '›', 'Página siguiente de ' + noun, page.page === page.pageCount)}</div></nav>`;
  }
  function tableBanner(title, count, unit, symbol) {
    return `<div class="ra-table-banner"><span class="ra-table-symbol">${icon(symbol)}</span><div><strong>${title}</strong><span>${plural(count, unit === 'estudiantes' ? 'estudiante' : 'actividad', unit)} en tu selección</span></div><span class="ra-table-hint">${icon('info')}${symbol === 'list' ? 'Abre una actividad para ver su matriz' : 'Selecciona un nombre para ver el detalle'}</span></div>`;
  }
  function overview(stats) {
    const subjects = sortGroups(grouped(scoped, r => r.source.subjectKey));
    const studentIds = new Set(visible.map(r => r.studentId)), activityIds = new Set(visible.map(r => r.activityId));
    const students = studentGroups(scoped).filter(s => studentIds.has(s.id) && s.alerts).slice(0, 5);
    const activities = activityGroups(scoped).filter(s => activityIds.has(s.id) && s.alerts).slice(0, 5);
    let stop = 0;
    const gradient = C.statuses.map(s => { const start = stop; stop += stats.total ? stats[s] / stats.total * 100 : 0; return `${colors[s]} ${start}% ${stop}%`; }).join(',');
    const topPending = [...subjects].sort((a, b) => b.missing / b.total - a.missing / a.total)[0];
    const insight = topPending?.missing ? `${escape(topPending.rows[0].source.subject)} tiene un ${number(topPending.missing / topPending.total * 100)}% de notas pendientes. Revisa el registro antes de valorar el desempeño.` : 'Todas las actividades de tu selección tienen una nota registrada. Revisa los resultados que requieren refuerzo.';
    return `<div class="ra-overview-grid"><section class="ra-panel ra-subject-panel">${panelHead('Registro por materia', 'Compara pendientes y resultados en toda tu selección.', '<span class="ra-section-tag">' + plural(subjects.length, 'materia') + '</span>')}
      ${legend()}<div class="ra-subject-labels"><span>Materia</span><span>Distribución de notas</span><span>Registradas</span></div><div class="ra-subject-list">${subjects.map(s => `<button class="ra-subject-row" data-subject="${escape(s.id)}" aria-label="Ver matriz de ${escape(s.rows[0].source.subject)}"><span class="ra-subject-name">${escape(s.rows[0].source.subject)}<small>${number(s.missing)} sin nota · ${number(s.not_achieved + s.low)} para refuerzo</small></span>${stacked(s)}<span class="ra-subject-value">${number(s.coverage)}%${icon('arrow')}</span></button>`).join('')}</div><div class="ra-insight">${icon('info')}<p>${insight}</p></div>
      </section><section class="ra-panel ra-distribution-panel">${panelHead('Estado del registro', 'Notas válidas sobre actividades asignadas.')}<div class="ra-donut-wrap"><div class="ra-donut" style="background:conic-gradient(${gradient})" role="img" aria-label="${number(stats.coverage)}% de notas registradas"><div><strong>${number(stats.coverage)}<small>%</small></strong><span>notas registradas</span></div></div></div><div class="ra-state-list">${C.statuses.map(s => `<button data-status="${s}"><span><i style="background:${colors[s]}"></i>${labels[s]}</span><strong>${number(stats[s])}</strong></button>`).join('')}</div><div class="ra-average"><span>Promedio de actividades<small>Solo notas válidas</small></span><strong>${number(stats.mean, 2)}<small>/ 5</small></strong></div></section></div>
      <div class="ra-overview-bottom"><section class="ra-panel">${panelHead('Estudiantes para acompañar', filters().status ? 'Coinciden con el estado seleccionado; se muestra su contexto completo.' : 'Prioriza los registros pendientes y las actividades por reforzar.', '<button class="ra-text-button no-print" data-switch="students">Ver todos ' + icon('arrow') + '</button>')}
      ${students.length ? students.map(s => `<button class="ra-student-row" data-student-detail="${escape(s.id)}">${avatar(s.rows[0].student.name)}<span class="ra-person-name">${escape(s.rows[0].student.name)}<small>Grado ${escape(s.rows[0].student.grade)} · ${s.missing} sin nota · ${s.not_achieved + s.low} para refuerzo</small></span><span class="ra-review-count">${s.alerts}<small>por revisar</small></span>${icon('arrow')}</button>`).join('') : '<p class="ra-inline-empty">No hay estudiantes con alertas para este estado.</p>'}</section>
      <section class="ra-panel">${panelHead('Actividades que requieren atención', 'Distingue lo que falta registrar de lo que necesita refuerzo.', '<button class="ra-text-button no-print" data-switch="activities">Ver todas ' + icon('arrow') + '</button>')}${activities.length ? activities.map(s => { const r = s.rows[0]; return `<button class="ra-activity-row" data-activity="${escape(s.id)}"><span class="ra-activity-code">${escape(r.activity.key)}</span><span class="ra-person-name">${escape(r.activity.name)}<small>${escape(r.source.subject)} · ${r.activity.grades.length ? 'Grado ' + r.activity.grades.join(', ') : 'Grupo ' + r.student.group}</small></span><span class="ra-review-count">${s.alerts}<small>de ${s.total}</small></span>${icon('arrow')}</button>`; }).join('') : '<p class="ra-inline-empty">Sin actividades que requieran revisión para este estado.</p>'}</section></div>`;
  }
  function scoreCell(r, dim = false, interactive = true) {
    if (!r) return '<span class="ra-not-applicable" title="Sin actividad asignada">—</span>';
    const state = C.state(r, filters().threshold), text = state === 'missing' ? 'Sin nota' : state === 'invalid' ? 'Revisar' : number(r.score, 1);
    if (!interactive) return `<span class="ra-score ${state}">${text}</span>`;
    return `<button class="ra-score ${state}${dim ? ' dim' : ''}" data-record="${r.uiIndex}" aria-label="${escape(`${r.student.name}: ${labels[state]}. ${r.activity.name}`)}" title="Ver nota y ubicación en Excel">${text}</button>`;
  }
  function matrix(rows = visible) {
    const sets = grouped(rows, r => r.sourceId + '|' + r.student.grade).sort((a, b) => a.rows[0].source.subject.localeCompare(b.rows[0].source.subject, 'es') || Number(a.rows[0].student.grade) - Number(b.rows[0].student.grade));
    const sectionPage = pageOf(sets, 'matrix', 5);
    const f = filters(), lookup = new Map(allRecords.map(r => [r.sourceId + '|' + r.studentId + '|' + r.activityId, r]));
    return viewHead('Matriz de notas', 'Selecciona una nota para ver su origen en Excel. Abre un estudiante para revisar su detalle.', `<div class="ra-section-actions no-print"><button class="ra-button quiet" id="toggle-density" aria-pressed="${compact}">${icon('grid')}${compact ? 'Vista cómoda' : 'Vista compacta'}</button><button class="ra-button quiet" id="expand-matrix">Desplegar todas</button></div>`) + (f.status ? '<p class="ra-context-note">Las celdas atenuadas corresponden a otros estados. El CSV incluye únicamente las coincidencias del filtro.</p>' : '') + sectionPage.items.map((set, index) => {
      const first = set.rows[0];
      const acts = [...new Map(set.rows.map(r => [r.activityId, r.activity])).values()].sort((a, b) => a.key[0].localeCompare(b.key[0]) || Number(a.number) - Number(b.number));
      const students = [...new Map(set.rows.map(r => [r.studentId, r.student])).values()].sort((a, b) => a.name.localeCompare(b.name, 'es'));
      const rowKey = 'matrix-rows-' + set.id, studentPage = pageOf(students, rowKey);
      return `<details class="ra-panel ra-matrix-panel" data-matrix-id="${escape(set.id)}" data-page-region="${escape(rowKey)}" ${(matrixOpen.has(set.id) ? matrixOpen.get(set.id) : f.subject || f.student || sets.length <= 3 || index === 0) ? 'open' : ''}><summary><span class="ra-matrix-subject"><strong>${escape(first.source.subject)}</strong><span>Grado ${escape(first.student.grade)}<i></i>Período ${escape(first.source.period || 's/d')}</span></span><span class="ra-matrix-count">${plural(students.length, 'estudiante')} · ${plural(acts.length, 'actividad', 'actividades')}</span>${set.alerts ? pill('missing', set.alerts + ' por revisar') : pill('achieved', 'Sin alertas')}${icon('chevron')}</summary><div class="ra-matrix-body"><div class="ra-table-wrap"><table class="ra-table ra-matrix${compact ? ' compact' : ''}"><caption class="ra-sr-only">${escape(first.source.subject)}, grado ${escape(first.student.grade)}, período ${escape(first.source.period)}</caption><thead><tr><th scope="col" class="student-col">Estudiante</th>${acts.map(a => `<th scope="col"><button class="ra-column-activity" data-activity="${escape(a.id)}"><b>${escape(a.type)} ${escape(a.number)}</b><span>${escape(a.name)}</span></button></th>`).join('')}<th scope="col">Promedio<small>Notas visibles válidas</small></th></tr></thead><tbody>${studentPage.items.map(student => {
        const cells = acts.map(a => lookup.get(first.sourceId + '|' + student.id + '|' + a.id)), valid = cells.filter(r => r && r.score !== null);
        const mean = valid.length ? valid.reduce((sum, r) => sum + r.score, 0) / valid.length : null;
        return `<tr><th scope="row" class="student-col"><button class="ra-name-button" data-student-detail="${escape(student.id)}">${escape(student.name)}</button><small>${escape(student.document)}</small></th>${cells.map(r => `<td>${scoreCell(r, Boolean(f.status && r && !C.filter([r], { status: f.status, threshold: f.threshold }).length))}</td>`).join('')}<td><strong class="ra-table-mean">${number(mean, 2)}</strong></td></tr>`;
      }).join('')}</tbody></table></div>${pagination(rowKey, studentPage, 'estudiantes', true)}<p class="ra-matrix-source">${icon('file')}${escape(first.source.file)} · Hoja ${escape(first.source.sheet)}</p></div></details>`;
    }).join('') + pagination('matrix', sectionPage, 'secciones') + legend();
  }
  function studentTable() {
    const ids = new Set(visible.map(r => r.studentId)), groups = studentGroups(scoped).filter(s => ids.has(s.id));
    if (sorting === 'name') groups.sort((a, b) => a.rows[0].student.name.localeCompare(b.rows[0].student.name, 'es'));
    if (sorting === 'mean') groups.sort((a, b) => (a.mean ?? 6) - (b.mean ?? 6));
    const page = pageOf(groups, 'students');
    return viewHead('Seguimiento por estudiante', 'Las columnas resumen todas las actividades del estudiante en tu selección.', `<label class="ra-sort-label no-print" for="sort-students">Ordenar<select id="sort-students"><option value="alerts" ${sorting === 'alerts' ? 'selected' : ''}>Más registros por revisar</option><option value="name" ${sorting === 'name' ? 'selected' : ''}>Nombre de A a Z</option><option value="mean" ${sorting === 'mean' ? 'selected' : ''}>Menor promedio</option></select></label>`) + `<div class="ra-panel ra-table-panel" data-page-region="students">${tableBanner('Seguimiento individual', groups.length, 'estudiantes', 'users')}<div class="ra-table-wrap"><table class="ra-table"><caption class="ra-sr-only">Resumen de ${groups.length} estudiantes</caption><thead><tr><th scope="col">Estudiante</th><th scope="col">Grado</th><th scope="col" class="num">Sin nota</th><th scope="col" class="num">Con nota 1</th><th scope="col" class="num">Bajo desempeño</th><th scope="col" class="num">Revisar dato</th><th scope="col" class="num">Promedio</th><th scope="col">Registro de notas</th><th scope="col"><span class="ra-sr-only">Acciones</span></th></tr></thead><tbody>${page.items.map(s => {
      const student = s.rows[0].student;
      return `<tr><th scope="row"><div class="ra-student-cell">${avatar(student.name)}<div><button class="ra-name-button" data-student-detail="${escape(s.id)}">${escape(student.name)}</button><small>${plural(new Set(s.rows.map(r => r.source.subjectKey)).size, 'materia')} · ${plural(s.total, 'registro')}</small></div></div></th><td><span class="ra-grade-tag">${escape(student.grade)}°</span></td><td class="num">${s.missing ? pill('missing', s.missing) : '<span class="ra-zero">0</span>'}</td><td class="num">${s.not_achieved ? pill('not_achieved', s.not_achieved) : '<span class="ra-zero">0</span>'}</td><td class="num">${s.low ? pill('low', s.low) : '<span class="ra-zero">0</span>'}</td><td class="num">${s.invalid ? pill('invalid', s.invalid) : '<span class="ra-zero">0</span>'}</td><td class="num"><strong class="ra-table-mean">${number(s.mean, 2)}</strong></td><td><div class="ra-registration"><span>${number(s.coverage)}%</span>${progress(s.coverage)}</div></td><td><button class="ra-icon-button" data-student-detail="${escape(s.id)}" aria-label="Ver detalle de ${escape(student.name)}">${icon('arrow')}</button></td></tr>`;
    }).join('')}</tbody></table></div>${pagination('students', page, 'estudiantes')}</div>`;
  }
  function activityTable() {
    const ids = new Set(visible.map(r => r.activityId)), groups = activityGroups(scoped).filter(s => ids.has(s.id));
    if (activitySorting === 'name') groups.sort((a, b) => a.rows[0].activity.name.localeCompare(b.rows[0].activity.name, 'es'));
    if (activitySorting === 'mean') groups.sort((a, b) => (a.mean ?? 6) - (b.mean ?? 6));
    const page = pageOf(groups, 'activities');
    return viewHead('Desempeño por actividad', 'Compara resultados y abre la matriz para identificar a los estudiantes.', `<label class="ra-sort-label no-print" for="sort-activities">Ordenar<select id="sort-activities"><option value="alerts" ${activitySorting === 'alerts' ? 'selected' : ''}>Más registros por revisar</option><option value="name" ${activitySorting === 'name' ? 'selected' : ''}>Nombre de A a Z</option><option value="mean" ${activitySorting === 'mean' ? 'selected' : ''}>Menor promedio</option></select></label>`) + `<div class="ra-panel ra-table-panel" data-page-region="activities">${tableBanner('Resultados por actividad', groups.length, 'actividades', 'list')}<div class="ra-table-wrap"><table class="ra-table"><caption class="ra-sr-only">Resumen de ${groups.length} actividades</caption><thead><tr><th scope="col">Actividad</th><th scope="col">Materia / grupo</th><th scope="col" class="num">Estudiantes</th><th scope="col" class="num">Sin nota</th><th scope="col" class="num">Con nota 1</th><th scope="col" class="num">Bajo</th><th scope="col" class="num">Revisar</th><th scope="col" class="num">Promedio</th><th scope="col">Distribución</th><th scope="col"><span class="ra-sr-only">Acciones</span></th></tr></thead><tbody>${page.items.map(s => {
      const r = s.rows[0];
      return `<tr><th scope="row"><div class="ra-student-cell"><span class="ra-activity-code">${escape(r.activity.key)}</span><div><button class="ra-name-button" data-activity="${escape(s.id)}">${escape(r.activity.name)}</button><small>${escape(r.activity.type)} ${escape(r.activity.number)} · ${escape(r.activity.date || 'Sin fecha')}</small></div></div></th><td>${escape(r.source.subject)}<small>${r.activity.grades.length ? 'Grado ' + r.activity.grades.join(', ') : 'Grupo ' + r.student.group} · Período ${escape(r.source.period || 's/d')}</small></td><td class="num">${s.students}</td><td class="num">${s.missing ? pill('missing', s.missing) : '<span class="ra-zero">0</span>'}</td><td class="num">${s.not_achieved ? pill('not_achieved', s.not_achieved) : '<span class="ra-zero">0</span>'}</td><td class="num">${s.low ? pill('low', s.low) : '<span class="ra-zero">0</span>'}</td><td class="num">${s.invalid}</td><td class="num"><strong class="ra-table-mean">${number(s.mean, 2)}</strong></td><td class="ra-distribution-cell">${stacked(s)}</td><td><button class="ra-icon-button" data-activity="${escape(s.id)}" aria-label="Ver matriz de ${escape(r.activity.name)}">${icon('arrow')}</button></td></tr>`;
    }).join('')}</tbody></table></div>${pagination('activities', page, 'actividades')}</div>${legend()}`;
  }
  function draw() {
    if (!data) return;
    const signature = JSON.stringify(filters());
    if (signature !== lastFilterSignature) {
      for (const [key, state] of pages) if (key !== 'sources') state.page = 1;
      matrixOpen.clear(); lastFilterSignature = signature;
    }
    scoped = C.filter(allRecords, filters(), ['status']); visible = C.filter(scoped, filters());
    const stats = C.summarize(scoped, filters().threshold);
    drawMetrics(stats); drawQuickFilters(stats); drawFilterChips();
    $('export').disabled = !visible.length; $('print').disabled = !visible.length;
    document.querySelectorAll('[data-view]').forEach(button => { const active = button.dataset.view === currentView; button.classList.toggle('active', active); if (active) button.setAttribute('aria-current', 'page'); else button.removeAttribute('aria-current'); });
    $('view').innerHTML = !scoped.length ? empty('No encontramos coincidencias', 'Prueba otro nombre, actividad o combinación de filtros.') : !visible.length ? empty('Sin registros en este estado', 'No hay notas que coincidan con el estado seleccionado. Puedes volver a ver todos los estados.', false) + '<div class="ra-empty-action"><button class="ra-button" data-status="">Ver todos los estados</button></div>' : currentView === 'overview' ? overview(stats) : currentView === 'matrix' ? matrix() : currentView === 'students' ? studentTable() : activityTable();
  }
  function drawStudentDialog() {
    const student = dialogRows[0].student, stats = C.summarize(dialogRows, filters().threshold);
    const rows = dialogStatus ? C.filter(dialogRows, { status: dialogStatus, threshold: filters().threshold }) : dialogRows;
    const page = pageOf(rows, 'student-detail');
    const bySubject = grouped(page.items, r => r.sourceId).sort((a, b) => a.rows[0].source.subject.localeCompare(b.rows[0].source.subject, 'es'));
    $('dialog-body').innerHTML = `<div class="ra-student-profile">${avatar(student.name)}<div><strong>Grado ${escape(student.grade)}</strong><p>Grupo ${escape(student.group)} · ${plural(new Set(dialogRows.map(r => r.source.subjectKey)).size, 'materia')} · ${plural(stats.total, 'actividad', 'actividades')}</p></div></div><div class="ra-dialog-metrics"><div><span>Sin nota</span><strong class="ra-text-pending">${stats.missing}</strong></div><div><span>Para refuerzo</span><strong class="ra-text-support">${stats.not_achieved + stats.low}</strong></div><div><span>Promedio de actividades</span><strong>${number(stats.mean, 2)}</strong></div></div><div class="ra-dialog-filters" aria-label="Filtrar detalle individual">${[['', 'Todas', stats.total], ['missing', 'Sin nota', stats.missing], ['support', 'Para refuerzo', stats.not_achieved + stats.low]].map(([state, label, count]) => `<button class="ra-status-tab ${dialogStatus === state ? 'active' : ''}" data-dialog-status="${state}" aria-pressed="${dialogStatus === state}">${label}<span>${count}</span></button>`).join('')}</div>${bySubject.length ? bySubject.map(s => `<section class="ra-detail-subject"><h3>${escape(s.rows[0].source.subject)}<span>Período ${escape(s.rows[0].source.period || 's/d')}</span></h3><div class="ra-table-wrap"><table class="ra-table ra-detail-table"><thead><tr><th>Actividad</th><th>Estado</th><th class="num">Nota</th></tr></thead><tbody>${s.rows.map(r => `<tr><th scope="row"><button class="ra-name-button" data-record="${r.uiIndex}">${escape(r.activity.name)}</button><small>${escape(r.activity.type)} ${escape(r.activity.number)}${r.activity.date ? ' · ' + escape(r.activity.date) : ''}</small></th><td>${pill(C.state(r, filters().threshold))}</td><td class="num">${scoreCell(r)}</td></tr>`).join('')}</tbody></table></div></section>`).join('') : '<p class="ra-inline-empty">Este estudiante no tiene registros en ese estado.</p>'}${pagination('student-detail', page, 'actividades')}`;
  }
  function showStudent(id) {
    dialogRows = scoped.filter(r => r.studentId === id); if (!dialogRows.length) return;
    dialogStudent = id; dialogStatus = ''; $('dialog-title').textContent = dialogRows[0].student.name;
    if (pages.has('student-detail')) pages.get('student-detail').page = 1;
    drawStudentDialog(); if (!$('student-dialog').open) $('student-dialog').showModal();
  }
  function showRecord(index) {
    const r = allRecords[Number(index)]; if (!r) return;
    const state = C.state(r, filters().threshold); $('record-title').textContent = r.activity.name;
    $('record-body').innerHTML = `<p class="ra-record-student">${escape(r.student.name)}<span>Grado ${escape(r.student.grade)} · ${escape(r.source.subject)}</span></p><div class="ra-record-result">${scoreCell(r, false, false)}${pill(state)}</div><dl class="ra-record-origin"><div><dt>Archivo de origen</dt><dd>${escape(r.source.file)}</dd></div><div><dt>Hoja y celda</dt><dd>${escape(r.source.sheet)} <b>!${escape(r.cell)}</b></dd></div><div><dt>Actividad</dt><dd>${escape(r.activity.type)} ${escape(r.activity.number)} · Período ${escape(r.source.period || 's/d')}</dd></div>${state === 'invalid' ? '<div><dt>Valor encontrado</dt><dd>' + escape(r.raw) + '</dd></div>' : ''}</dl><div class="ra-insight">${icon('info')}<p>${state === 'missing' ? 'La celda está vacía. Esto indica que falta registrar la nota; no confirma que la actividad no se haya entregado.' : 'Esta nota se lee del archivo de origen.'} Para corregirla, edita Excel, guarda el archivo y pulsa Actualizar notas.</p></div>`;
    if (!$('record-dialog').open) $('record-dialog').showModal();
  }
  function drawDiagnostics() {
    const warnings = [...data.warnings, ...data.sources.flatMap(s => s.warnings.map(w => `${s.file}: ${w}`))];
    const page = pageOf(data.sources, 'sources');
    $('diagnostics').hidden = false;
    $('diagnostics-summary').textContent = `Archivos de origen y calidad de datos${warnings.length ? ' · ' + plural(warnings.length, 'observación', 'observaciones') : ''}`;
    $('source-count').textContent = `${plural(data.sources.length, 'archivo')} de origen`;
    $('diagnostics-content').innerHTML = `<p class="ra-source-explanation">El reporte utiliza los archivos guardados. Los cambios sin guardar en Excel todavía no se reflejan aquí.</p><div class="ra-table-wrap"><table class="ra-table"><thead><tr><th>Archivo</th><th>Grupo</th><th>Materia</th><th>Período</th><th>Último guardado</th></tr></thead><tbody>${page.items.map(s => `<tr><td>${escape(s.file)}</td><td>${escape(s.group)}</td><td>${escape(s.subject)}</td><td>${escape(s.period)}</td><td>${escape(s.modified ? new Date(s.modified).toLocaleString('es-CO') : 'Sin información')}</td></tr>`).join('')}</tbody></table></div>${pagination('sources', page, 'archivos')}${warnings.length ? '<h3>Observaciones para revisar en Excel</h3><ul class="ra-warning-list">' + warnings.map(w => `<li>${escape(w)}</li>`).join('') + '</ul>' : '<p class="ra-source-explanation">No se encontraron inconsistencias de estructura.</p>'}`;
  }
  function notify(text) {
    clearTimeout(toastTimer); $('toast').innerHTML = icon('check') + `<span>${escape(text)}</span>`; $('toast').hidden = false;
    toastTimer = setTimeout(() => { $('toast').hidden = true; }, 3500);
  }
  function redrawPage(key) {
    if (key === 'sources') drawDiagnostics();
    else if (key === 'student-detail') drawStudentDialog();
    else draw();
  }
  async function load() {
    const refreshing = Boolean(data);
    $('refresh').disabled = true; $('refresh').classList.add('is-loading'); $('message').hidden = true;
    $('loading').hidden = refreshing; $('report').setAttribute('aria-busy', 'true');
    const controller = new AbortController(), timeout = setTimeout(() => controller.abort(), 90000);
    try {
      const response = await fetch('api.php', { cache: 'no-store', credentials: 'same-origin', signal: controller.signal });
      const payload = await response.json();
      if (!response.ok || !payload.ok) { const error = new Error(payload.message || 'No se pudo leer el reporte. Vuelve a intentar.'); error.status = response.status; throw error; }
      if (!Array.isArray(payload.data?.sources)) throw new Error('La respuesta del reporte no tiene el formato esperado. Vuelve a intentar.');
      data = payload.data; allRecords = C.flatten(data).map((r, uiIndex) => ({ ...r, uiIndex }));
      updateOptions(); draw(); drawDiagnostics();
      $('updated').textContent = 'Última lectura: ' + new Date(data.readAt).toLocaleString('es-CO', { dateStyle: 'medium', timeStyle: 'short' });
      $('source-dot').className = 'ra-source-dot connected'; $('report').hidden = false;
      if (refreshing) notify('Notas y actividades actualizadas');
    } catch (error) {
      const text = error.name === 'AbortError' ? 'La lectura tardó demasiado. Comprueba que Google Drive esté conectado.' : error instanceof SyntaxError ? 'No se pudo recibir el reporte. Comprueba tu sesión e intenta de nuevo.' : error.message;
      $('message').className = 'ra-message error'; $('message').hidden = false;
      $('message').innerHTML = `${icon('alert')}<div><strong>${refreshing ? 'No se pudo actualizar' : 'No se pudo cargar el reporte'}</strong><p>${escape(text)}${refreshing ? ' Se conserva la última lectura disponible.' : ''}</p></div>${error.status === 401 ? '<a class="ra-button" href="../../usuario/login.php" target="_top">Iniciar sesión</a>' : '<button class="ra-button" data-retry>Intentar de nuevo</button>'}`;
      $('source-dot').className = 'ra-source-dot stale';
      if (!refreshing) { $('updated').textContent = 'Sin conexión con las valoraciones'; $('export').disabled = true; $('print').disabled = true; }
    } finally { clearTimeout(timeout); $('loading').hidden = true; $('refresh').disabled = false; $('refresh').classList.remove('is-loading'); $('report').removeAttribute('aria-busy'); }
  }
  function resetFilters() {
    filterIds.forEach(id => { $(id).value = id === 'threshold' ? '3' : ''; }); $('search').value = ''; updateOptions(); draw();
  }
  function switchView(view, scroll = false) {
    currentView = view; draw();
    if (scroll) $('view-nav').scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth', block: 'start' });
  }
  filterIds.forEach(id => $(id).addEventListener('change', () => { updateOptions(id); draw(); }));
  $('search').addEventListener('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(draw, 120); });
  $('search').addEventListener('keydown', event => { if (event.key === 'Enter') { clearTimeout(searchTimer); draw(); } });
  $('clear').addEventListener('click', resetFilters);
  $('toggle-filters').addEventListener('click', () => toggleAdvanced());
  $('refresh').addEventListener('click', load);
  $('print').addEventListener('click', () => { clearTimeout(searchTimer); draw(); if (visible.length) window.print(); });
  $('show-sources').addEventListener('click', () => { if (data) { $('diagnostics').open = true; $('diagnostics').scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth', block: 'start' }); } });
  $('export').addEventListener('click', () => {
    clearTimeout(searchTimer); draw(); if (!visible.length) return;
    const url = URL.createObjectURL(new Blob([C.csv(visible, filters().threshold)], { type: 'text/csv;charset=utf-8;' }));
    const today = new Date();
    const stamp = [today.getFullYear(), String(today.getMonth() + 1).padStart(2, '0'), String(today.getDate()).padStart(2, '0')].join('-');
    const a = document.createElement('a'); a.href = url; a.download = `reporte-actividades-${stamp}.csv`;
    a.click(); setTimeout(() => URL.revokeObjectURL(url), 1000); notify(`${plural(visible.length, 'registro')} exportados en CSV`);
  });
  $('close-dialog').addEventListener('click', () => $('student-dialog').close());
  $('print-student').addEventListener('click', () => {
    $('student').value = dialogStudent; $('status').value = ''; $('student-dialog').close(); currentView = 'matrix'; draw(); window.requestAnimationFrame(() => window.print());
  });
  document.addEventListener('click', event => {
    const button = event.target.closest('button'); if (!button) return;
    const d = button.dataset;
    if (d.view || d.switch) switchView(d.view || d.switch, Boolean(d.switch));
    if ('status' in d) { $('status').value = d.status; draw(); }
    if (d.focusStatus) { $('status').value = d.focusStatus; switchView('students', true); }
    if (d.subject) { $('subject').value = d.subject; updateOptions('subject'); switchView('matrix', true); }
    if (d.activity) { $('activity').value = d.activity; updateOptions('activity'); switchView('matrix', true); }
    if (d.studentDetail) showStudent(d.studentDetail);
    if ('record' in d) showRecord(d.record);
    if ('dialogStatus' in d) { dialogStatus = d.dialogStatus; if (pages.has('student-detail')) pages.get('student-detail').page = 1; drawStudentDialog(); }
    if ('pageKey' in d && pages.has(d.pageKey)) {
      pages.get(d.pageKey).page = Number(d.pageTarget); redrawPage(d.pageKey);
      const active = [...document.querySelectorAll('button[data-page-key]')].find(el => el.dataset.pageKey === d.pageKey && el.hasAttribute('aria-current'));
      active?.focus({ preventScroll: true });
    }
    if (d.close) $(d.close).close();
    if ('retry' in d) load();
    if ('reset' in d) resetFilters();
    if ('removeFilter' in d) { const id = d.removeFilter; $(id === 'query' ? 'search' : id).value = id === 'threshold' ? '3' : ''; updateOptions(id); draw(); }
    if (button.id === 'toggle-density') { compact = !compact; draw(); }
    if (button.id === 'expand-matrix') {
      const details = [...$('view').querySelectorAll('.ra-matrix-panel')], expand = details.some(detail => !detail.open);
      details.forEach(detail => { detail.open = expand; }); button.textContent = expand ? 'Contraer todas' : 'Desplegar todas';
    }
  });
  document.addEventListener('change', event => {
    const key = event.target.dataset.pageSize;
    if (key && pages.has(key)) {
      const size = Number(event.target.value);
      if (![5, 10, 25, 50].includes(size)) return;
      pages.set(key, { page: 1, size }); redrawPage(key);
      [...document.querySelectorAll('select[data-page-size]')].find(el => el.dataset.pageSize === key)?.focus({ preventScroll: true });
    }
    if (event.target.id === 'sort-students') { sorting = event.target.value; if (pages.has('students')) pages.get('students').page = 1; draw(); }
    if (event.target.id === 'sort-activities') { activitySorting = event.target.value; if (pages.has('activities')) pages.get('activities').page = 1; draw(); }
  });
  document.addEventListener('toggle', event => {
    if (!printMode && event.target.dataset.matrixId && event.target.isConnected) matrixOpen.set(event.target.dataset.matrixId, event.target.open);
  }, true);
  document.addEventListener('keydown', event => {
    if (event.key === '/' && !event.ctrlKey && !event.metaKey && !event.altKey && !event.target.closest('input,select,textarea,[contenteditable="true"]') && !$('student-dialog').open && !$('record-dialog').open) { event.preventDefault(); $('search').focus(); }
  });
  window.addEventListener('beforeprint', () => {
    if (printMode) return;
    printMode = true; draw();
    printDetails = [...$('view').querySelectorAll('details:not([open])')];
    printDetails.forEach(detail => { detail.open = true; });
  });
  window.addEventListener('afterprint', () => {
    if (!printMode) return;
    printMode = false; printDetails = []; draw();
  });
  load();
})();
