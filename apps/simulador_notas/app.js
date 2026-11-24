(() => {
    'use strict';
    const $ = id => document.getElementById(id);
    const payload = JSON.parse($('sn-data').textContent);
    const records = payload.data.records;
    const byId = new Map(records.map(row => [row.id, row]));
    const key = `guagua-simulador:${payload.scope}:${payload.fingerprint}`;
    const collator = new Intl.Collator('es', { numeric: true, sensitivity: 'base' });
    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const fmt = value => Number.isFinite(value) ? Number(value.toFixed(10)).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—';
    const inputValue = value => Number.isFinite(value) ? fmt(value).replace(',', '.') : '';
    const icon = name => `<svg class="sn-icon" aria-hidden="true"><use href="#sn-i-${name}"></use></svg>`;
    const normalize = value => String(value).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    const studentKey = row => JSON.stringify([row.year, row.group, row.code]);
    const initials = name => name.trim().split(/\s+/).slice(0, 2).map(part => part[0]).join('').toUpperCase();
    const groupLabel = value => {
        const match = /^\d+ Completa (\d+) (\d+) 20\d{2}$/.exec(value);
        return match ? `Grado ${match[1]}${match[1] === match[2] ? '' : ' · Grupo ' + match[2]}` : value;
    };
    const unique = (rows, field) => [...new Set(rows.map(row => row[field]))].sort(collator.compare);
    const table = $('rows').closest('table');
    const mobile = window.matchMedia('(max-width: 950px)');
    let weights = [25, 25, 25, 25], goal = 3, scenarios = {}, selected = null;
    let current = [], roster = [], visibleRoster = [], page = 1, toastTimer, storageFailed = false;
    let editingId = null, editHasUndo = false, bulkRows = [];
    let mode = 'consultation', alertPage = 1, alertExportRows = [], lastConsultStudent = null, missingPage = 1, missingExportRows = [];
    let reportPage = 1, reportGroups = [];
    const views = ['consultation', 'alerts', 'reports', 'missing'];
    const drafts = new Map(), history = [];

    function readStorage(suffix, fallback) {
        try { return JSON.parse(sessionStorage.getItem(key + suffix) || 'null') ?? fallback; } catch { return fallback; }
    }
    const savedGrades = readStorage('', {});
    if (savedGrades && typeof savedGrades === 'object' && !Array.isArray(savedGrades)) {
        for (const [id, value] of Object.entries(savedGrades)) {
            if (byId.has(id) && (value === null || (Number.isFinite(value) && value >= 1 && value <= 5))) scenarios[id] = value;
        }
    }
    const savedSettings = readStorage(':settings', null);
    if (savedSettings && Array.isArray(savedSettings.weights) && savedSettings.weights.length === 4 && NotasModel.calculate({ p1: 3, p2: 3, p3: 3 }, 3, savedSettings.weights, savedSettings.target).valid) {
        weights = savedSettings.weights; goal = savedSettings.target;
    }
    const savedView = readStorage(':view', {});
    function setOptions(id, values, placeholder, label = value => value) {
        const old = $(id).value;
        $(id).innerHTML = `<option value="">${placeholder}</option>` + values.map(value => `<option value="${esc(value)}" title="${esc(value)}">${esc(label(value))}</option>`).join('');
        if (values.includes(old)) $(id).value = old;
    }
    setOptions('year', unique(records, 'year'), 'Todos los años');
    $('year').value = unique(records, 'year').includes(savedView.year) ? savedView.year : (unique(records, 'year').at(-1) || '');

    function scopeRows() {
        return records.filter(row => (!$('year').value || row.year === $('year').value) && (!$('group').value || row.group === $('group').value));
    }
    function studentRows() { return scopeRows().filter(row => selected === null || studentKey(row) === selected); }
    function activeScenarios() { return scenarios; }
    function activeHistory() { return history; }
    function isEdited(row) { return Object.hasOwn(activeScenarios(), row.id); }
    function baselineP4(row) { return NotasModel.resolveP4(row, {}); }
    function p4(row) { return drafts.has(row.id) ? null : NotasModel.resolveP4(row, scenarios); }
    function calc(row) { return NotasModel.calculate(row, p4(row), weights, goal); }
    function parseGrade(raw) {
        const text = raw.trim();
        if (text === '') return { valid: true, value: null };
        const value = Number(text.replace(',', '.'));
        return { valid: /^\d(?:[.,]\d{0,2})?$/.test(text) && value >= 1 && value <= 5, value };
    }
    function setGrade(row, value) {
        const changes = activeScenarios();
        if (value === baselineP4(row)) delete changes[row.id]; else changes[row.id] = value;
        drafts.delete(row.id);
    }
    function save() {
        try {
            sessionStorage.setItem(key, JSON.stringify(scenarios));
            sessionStorage.setItem(key + ':settings', JSON.stringify({ weights, target: goal }));
            sessionStorage.setItem(key + ':view', JSON.stringify({ year: $('year').value, group: $('group').value, student: selected, mode }));
            storageFailed = false;
        } catch { storageFailed = true; }
        updateSaveState();
    }
    function updateSaveState() {
        $('pdf-reset').disabled = !Object.keys(scenarios).length && !drafts.size;
        const count = Object.keys(scenarios).length;
        $('save-state').classList.toggle('sn-storage-error', storageFailed || drafts.size > 0);
        $('save-state').innerHTML = icon(storageFailed || drafts.size ? 'info' : 'check') + `<span>${storageFailed ? 'El navegador no permite guardar este escenario' : drafts.size ? `Revisa ${drafts.size} nota${drafts.size === 1 ? '' : 's'} de P4 antes de exportar` : count ? `${count} nota${count === 1 ? '' : 's'} simulada${count === 1 ? '' : 's'} · guardada${count === 1 ? '' : 's'} en esta pestaña` : 'Tus escenarios se guardan en esta pestaña'}</span>`;
    }
    function pushUndo() {
        const stack = activeHistory();
        stack.push({ ...activeScenarios() });
        if (stack.length > 30) stack.shift();
        $('undo').disabled = false;
    }
    function notify(text) {
        clearTimeout(toastTimer);
        $('notice').textContent = text;
        $('notice').hidden = false;
        toastTimer = setTimeout(() => { $('notice').hidden = true; }, 5000);
    }

    $('pdf-reset').addEventListener('click', () => {
        if (!Object.keys(scenarios).length && !drafts.size) return;
        pushUndo(); scenarios = {}; drafts.clear(); save(); render();
        notify('Se restauraron todas las notas de P4 de consulta. Puedes deshacer el cambio.');
    });

    function rebuildRoster(preserve = true) {
        const years = records.filter(row => !$('year').value || row.year === $('year').value);
        setOptions('group', unique(years, 'group'), 'Todos', groupLabel);
        const students = new Map();
        scopeRows().forEach(row => { if (!students.has(studentKey(row))) students.set(studentKey(row), row); });
        roster = [...students.values()].sort((a, b) => collator.compare(a.student, b.student) || collator.compare(a.year, b.year));
        if (!preserve || (selected !== null && !students.has(selected))) selected = roster[0] ? studentKey(roster[0]) : null;
        $('student-count').textContent = roster.length;
        $('mobile-roster-count').textContent = `${roster.length} disponibles`;
        $('overview').querySelector('small').textContent = $('group').value ? 'Todos los estudiantes del grupo' : $('year').value ? 'Todos los estudiantes del año' : 'Todos los estudiantes del reporte';
        renderRoster();
    }
    function renderRoster() {
        const q = normalize($('search').value.trim());
        visibleRoster = roster.filter(row => !q || normalize(row.student + ' ' + row.code).includes(q));
        $('list-count').textContent = q ? `${visibleRoster.length}/${roster.length}` : '';
        $('overview').setAttribute('aria-pressed', String(selected === null));
        $('student-list').innerHTML = visibleRoster.length ? visibleRoster.map(row => {
            const active = studentKey(row) === selected;
            return `<button class="sn-student-button" type="button" data-student="${esc(studentKey(row))}" aria-pressed="${active}" title="${esc(row.student)}"><span class="sn-avatar" aria-hidden="true">${esc(initials(row.student))}</span><span><strong>${esc(row.student)}</strong><small>${esc(groupLabel(row.group))} · Cód. ${esc(row.code)}</small></span>${active ? icon('chevron') : ''}</button>`;
        }).join('') : `<div class="sn-list-empty"><p>${records.length ? 'No encontramos estudiantes con esa búsqueda.' : 'No hay estudiantes en el PDF de referencia.'}</p><button class="sn-text-button" type="button" data-list-clear>${records.length ? 'Limpiar búsqueda' : 'Revisar vista'}</button></div>`;
    }
    function updateSubjectOptions() { setOptions('subject', unique(studentRows(), 'subject'), 'Todas las materias'); }
    function clearTableFilters() { $('subject').value = ''; $('status').value = 'all'; $('period').value = 'all'; page = 1; }
    function chooseStudent(value) {
        selected = value;
        alertPage = 1; missingPage = 1; reportPage = 1;
        clearTableFilters();
        updateSubjectOptions();
        renderRoster();
        render();
        save();
        if (mobile.matches) {
            setRosterOpen(false);
            const heading = $({consultation:'context-title',alerts:'alerts-title',reports:'reports-title',missing:'missing-title'}[mode]);
            heading.setAttribute('tabindex', '-1');
            heading.focus({ preventScroll: true });
        } else if (value !== null) {
            [...$('student-list').querySelectorAll('[data-student]')].find(button => button.dataset.student === value)?.focus({ preventScroll: true });
        }
    }
    function setRosterOpen(open) {
        document.querySelector('.sn-sidebar').dataset.open = String(open);
        $('roster-toggle').setAttribute('aria-expanded', String(open));
    }
    function filteredRows() {
        return studentRows().filter(row => (!$('subject').value || row.subject === $('subject').value) && ($('status').value === 'all' || calc(row).status === $('status').value))
            .sort((a, b) => (selected === null ? collator.compare(a.student, b.student) : 0) || collator.compare(a.subject, b.subject));
    }
    function renderContext() {
        const row = roster.find(item => studentKey(item) === selected);
        $('context-eyebrow').textContent = selected === null ? 'VISTA GENERAL' : 'CONSULTA POR ESTUDIANTE';
        $('context-avatar').innerHTML = row ? esc(initials(row.student)) : icon('users');
        $('context-title').textContent = row ? row.student : records.length ? ($('group').value ? groupLabel($('group').value) : 'Todos los estudiantes') : 'PDF de referencia sin notas';
        $('context-meta').textContent = row ? `Código ${row.code} · ${groupLabel(row.group)} · Año ${row.year}` : `${roster.length} estudiantes · ${$('year').value ? 'Año ' + $('year').value : 'Todos los años'}`;
        $('stat-records-label').textContent = selected === null ? 'Estudiante · materia' : 'Materias';
    }
    function renderSummary() {
        const results = current.map(calc), complete = results.filter(result => result.final !== null);
        const histories = current.filter(row => [row.p1, row.p2, row.p3].every(Number.isFinite));
        const averageHistory = histories.length ? histories.reduce((sum, row) => sum + (row.p1 + row.p2 + row.p3) / 3, 0) / histories.length : null;
        $('stat-records').textContent = current.length;
        $('stat-students').textContent = selected === null ? `${new Set(current.map(studentKey)).size} estudiantes en esta vista` : $('subject').value || $('status').value !== 'all' ? 'en la vista filtrada' : 'en el reporte del estudiante';
        $('stat-history').textContent = fmt(averageHistory);
        $('stat-history-hint').textContent = `${histories.length} con los 3 periodos completos`;
        $('stat-average').textContent = fmt(complete.length ? complete.reduce((sum, result) => sum + result.final, 0) / complete.length : null);
        $('stat-complete').textContent = complete.length ? `${complete.length === 1 ? '1 materia proyectada' : 'Promedio de ' + complete.length + ' proyecciones'}` : 'Completa P4 para calcular';
        $('stat-risk').textContent = complete.length ? results.filter(result => result.status === 'risk').length : '—';
        $('stat-risk-hint').textContent = `Meta final: ${fmt(goal)}`;
        const units = selected === null ? 'registros' : 'materias';
        $('progress-label').textContent = `${complete.length} de ${current.length} ${units}`;
        $('progress').max = current.length || 1;
        $('progress').value = complete.length;
        $('progress-hint').textContent = current.length && complete.length === current.length ? 'Todas las notas finales están calculadas.' : 'Completa las notas pendientes de P4.';
        $('rules-summary').textContent = weights.every(weight => weight === 25) ? `Cada periodo aporta el 25 % · Meta: ${fmt(goal)}` : `P1 ${weights[0]} % · P2 ${weights[1]} % · P3 ${weights[2]} % · P4 ${weights[3]} % · Meta: ${fmt(goal)}`;
        $('reset').disabled = !current.some(row => isEdited(row) || drafts.has(row.id));
        $('undo').disabled = !activeHistory().length;
        $('bulk-open').disabled = !current.length;
        $('bulk-open').title = 'Aplicar una nota simulada a las materias de esta vista';
        $('results-description').textContent = 'Edita P4 para simular; ↶ recupera la nota de consulta.';
        $('export').disabled = !current.length || current.some(row => drafts.has(row.id));
        updateSaveState();
        renderAnnualAlerts();
        renderMissingNotes();
        renderStudentReports();
    }
    function syncMode() {
        views.forEach(view => {
            $(view + '-panel').hidden = mode !== view;
            $(view + '-tab').setAttribute('aria-selected', String(mode === view));
            $(view + '-tab').tabIndex = mode === view ? 0 : -1;
        });
        $('export').hidden = mode !== 'consultation';
        const skip = document.querySelector('.sn-skip');
        skip.href = mode === 'consultation' ? '#sn-results' : '#'+mode+'-panel';
        skip.textContent = {consultation:'Ir a las notas',alerts:'Ir a las alertas',reports:'Ir a las fichas anuales',missing:'Ir a las notas faltantes'}[mode];
    }
    function switchMode(next) {
        if (mode === next) return;
        if (next !== 'consultation') { if(mode==='consultation')lastConsultStudent = selected; selected = null; }
        else if (selected === null && roster.some(row => studentKey(row) === lastConsultStudent)) selected = lastConsultStudent;
        mode = next; alertPage = 1; missingPage = 1; reportPage = 1; clearTableFilters(); updateSubjectOptions();
        syncMode(); renderRoster(); render(); save();
    }
    function reportMarkup(group, printing = false) {
        const summary = NotasModel.studentReport(group.rows.map(row => ({...row,p4:p4(row)})), weights, goal);
        const first = group.rows[0], partial = summary.complete < summary.total;
        const simulated = group.rows.some(isEdited), invalid = group.rows.some(row => drafts.has(row.id));
        const status = summary.risks ? `${summary.risks} ${summary.risks === 1 ? 'materia bajo la meta' : 'materias bajo la meta'}` : summary.pending ? `${summary.pending} ${summary.pending === 1 ? 'materia pendiente' : 'materias pendientes'}` : 'Todas las materias alcanzan la meta';
        const rows = summary.subjects.map(({record:row,result}) => `<tr><th scope="row">${esc(row.subject)}</th>${[1,2,3,4].map(period => `<td class="${row['p'+period] === null ? 'sn-report-missing' : row['p'+period] < goal ? 'sn-report-below' : ''}">${drafts.has(row.id) && period === 4 ? 'Revisar' : fmt(row['p'+period])}${period === 4 && isEdited(row) ? '<small>Simulada</small>' : period === 4 && row.p4_correction ? '<small>Actualizada</small>' : ''}</td>`).join('')}<td class="sn-report-final ${result.status}"><strong>${fmt(result.final)}</strong><small>${result.status === 'pass' ? 'Alcanza meta' : result.status === 'risk' ? 'Bajo la meta' : 'Pendiente'}</small></td></tr>`).join('');
        return `<article class="sn-report-card sn-panel" data-report-card="${esc(group.key)}">
            <div class="sn-report-band"><span>IER La Josefina · Seguimiento académico</span><span>Año ${esc(first.year)}</span></div>
            <header class="sn-report-card-heading"><div><div class="sn-eyebrow">FICHA ACADÉMICA ANUAL${simulated ? ' · SIMULACIÓN' : ''}</div><h3>${esc(first.student)}</h3><p>${esc(groupLabel(first.group))} · Código ${esc(first.code)}</p></div><div class="sn-report-average"><span>${partial ? 'Promedio parcial' : 'Promedio anual'}</span><strong>${fmt(summary.average)}</strong><small>${summary.complete} de ${summary.total} materias completas</small></div></header>
            <div class="sn-report-card-tools"><span class="sn-report-status ${summary.risks ? 'risk' : summary.pending ? 'pending' : 'pass'}">${esc(status)}</span>${printing ? '' : `<div><button class="sn-text-button" type="button" data-report-open="${esc(group.key)}">Ver notas ${icon('arrow')}</button><button class="sn-text-button" type="button" data-report-print="${esc(group.key)}" ${invalid ? 'disabled' : ''}>Imprimir ficha</button></div>`}</div>
            <div class="sn-report-matrix-wrap"><table class="sn-report-matrix"><caption class="sn-sr">Materias, periodos y promedio anual de ${esc(first.student)}</caption><thead><tr><th scope="col">Materia</th>${[1,2,3,4].map(period=>`<th scope="col">P${period}<small>${weights[period-1]} %</small></th>`).join('')}<th scope="col">Anual</th></tr></thead><tbody>${rows}</tbody><tfoot><tr><th scope="row">Promedio por período</th>${summary.periods.map(period=>`<td title="${period.count} notas registradas">${fmt(period.average)}</td>`).join('')}<td>${fmt(summary.average)}${partial ? '<small>Parcial</small>' : ''}</td></tr></tfoot></table></div>
            <footer class="sn-report-card-footer"><p><strong>Promedio general:</strong> media de ${summary.complete} ${summary.complete === 1 ? 'materia con final completa' : 'materias con final completa'}. ${summary.pending ? `${summary.pending} pendientes; el promedio es parcial. ` : ''}Las notas faltantes no se cuentan como cero. Meta por materia: ${fmt(goal)}.</p>${simulated ? '<p class="sn-report-simulation-note">Incluye notas simuladas de P4 de esta pestaña.</p>' : ''}<p class="sn-report-source">Fuente: ${esc(payload.data.source)} · incluye actualizaciones registradas. «—» indica una nota pendiente.</p><div class="sn-report-signatures"><span>Docente</span><span>Acudiente</span></div></footer>
        </article>`;
    }
    function renderStudentReports() {
        if (mode !== 'reports') return;
        const query = normalize($('reports-search').value.trim()), grouped = new Map();
        studentRows().filter(row => !query || normalize(row.student+' '+row.code).includes(query)).forEach(row => {
            const key = studentKey(row);
            if (!grouped.has(key)) grouped.set(key, {key, rows:[]});
            grouped.get(key).rows.push(row);
        });
        reportGroups = [...grouped.values()].sort((a,b)=>collator.compare(a.rows[0].student,b.rows[0].student)||collator.compare(a.rows[0].group,b.rows[0].group));
        reportGroups.forEach(group=>group.rows.sort((a,b)=>collator.compare(a.subject,b.subject)));
        const total = reportGroups.length, pages = Math.max(1,Math.ceil(total/4));
        reportPage = Math.min(Math.max(reportPage,1),pages);
        $('reports-scope').textContent = `${total} ${total === 1 ? 'ficha' : 'fichas'} · ${$('year').value ? 'Año '+$('year').value : 'Todos los años'} · ${$('group').value ? groupLabel($('group').value) : 'Todos los grupos'}`;
        $('reports-all').hidden = selected === null;
        const invalid = reportGroups.some(group=>group.rows.some(row=>drafts.has(row.id)));
        $('reports-print').disabled = !total || invalid;
        $('reports-print-hint').textContent = invalid ? 'Revisa las notas inválidas de P4 antes de imprimir.' : `La impresión incluye ${total === 1 ? 'la ficha seleccionada' : 'las '+total+' fichas filtradas'}, también las de otras páginas. Puedes guardar el resultado como PDF.`;
        $('reports-list').innerHTML = total ? reportGroups.slice((reportPage-1)*4,reportPage*4).map(group=>reportMarkup(group)).join('') : '<div class="sn-panel sn-risk-empty"><h3>No hay fichas con esta selección</h3><p>Cambia el estudiante, la búsqueda, el año o el grupo.</p></div>';
        $('reports-count').textContent = total ? `Mostrando ${(reportPage-1)*4+1}–${Math.min(reportPage*4,total)} de ${total} estudiantes · Página ${reportPage} de ${pages}` : 'Sin estudiantes para mostrar';
        $('reports-prev').disabled = reportPage <= 1; $('reports-next').disabled = reportPage >= pages;
    }
    function printReports(groups) {
        if (!groups.length || groups.some(group=>group.rows.some(row=>drafts.has(row.id)))) return;
        $('sn-print-cards').innerHTML = groups.map(group=>reportMarkup(group,true)).join('');
        document.body.classList.add('sn-printing-cards');
        window.print();
    }
    window.addEventListener('afterprint', () => {
        document.body.classList.remove('sn-printing-cards');
        $('sn-print-cards').replaceChildren();
    });
    $('reports-search').addEventListener('input', () => { reportPage=1; renderStudentReports(); });
    $('reports-all').addEventListener('click', () => { $('reports-search').value=''; chooseStudent(null); });
    $('reports-prev').addEventListener('click', () => { reportPage--; renderStudentReports(); });
    $('reports-next').addEventListener('click', () => { reportPage++; renderStudentReports(); $('reports-panel').scrollIntoView({block:'start'}); });
    $('reports-print').addEventListener('click', () => printReports(reportGroups));
    $('reports-list').addEventListener('click', event => {
        const print = event.target.closest('[data-report-print]'), open = event.target.closest('[data-report-open]');
        if (print) printReports(reportGroups.filter(group=>group.key===print.dataset.reportPrint));
        if (open) { mode='consultation'; syncMode(); chooseStudent(open.dataset.reportOpen); $('sn-results').scrollIntoView({block:'start'}); }
    });
    function alertGradeLabel(entry) {
        return entry.result.basis === 'history' ? 'Promedio P1–P3' : entry.result.basis === 'final' ? (isEdited(entry.row) ? 'Final simulada' : entry.row.p4_correction ? 'Final con P4 actualizada' : 'Final con P4 del PDF') : 'Faltan notas';
    }
    function alertDetailMarkup(entry) {
        const { row, result } = entry;
        const missing = result.missingPeriods.map(period => 'P' + period).join(', ');
        const difference = result.gap > 0 ? (result.gap < 0.01 ? 'Menos de 0,01 bajo la meta' : `${fmt(result.gap)} bajo la meta`) : '';
        const required = result.required === null ? '—' : fmt(Math.max(1, Math.ceil((result.required - 1e-9) * 100) / 100));
        return `<tr><td class="sn-identity"><button class="sn-risk-subject" type="button" data-alert-record="${row.id}" title="Abrir la simulación de esta materia">${esc(row.subject)}</button><small>${result.status === 'incomplete' ? (drafts.has(row.id) ? 'Revisa la nota ingresada en P4' : missing ? 'Falta ' + missing : 'No hay notas suficientes') : result.basis === 'history' ? 'P4 pendiente · el año aún está en curso' : 'Con P4 = ' + fmt(p4(row))}</small></td><td><span class="sn-mobile-label" aria-hidden="true">Nota actual</span><strong class="sn-risk-grade">${fmt(result.grade)}</strong><small>${alertGradeLabel(entry)}</small>${difference ? `<span class="sn-risk-gap">${difference}</span>` : ''}</td><td><span class="sn-mobile-label" aria-hidden="true">Acumulado P1–P3</span><strong class="sn-risk-accumulated">${fmt(result.accumulated)}</strong><small>${result.accumulated === null ? 'Datos pendientes' : result.historyWeight + ' % del año evaluado'}</small></td><td><span class="sn-mobile-label" aria-hidden="true">Necesita en P4</span><strong class="sn-risk-required ${result.required > 5 + 1e-9 ? 'sn-risk-impossible' : ''}">${required}</strong><small>${result.required === null ? 'Faltan notas para calcular' : result.required > 5 + 1e-9 ? 'Supera el máximo de 5,00' : 'para llegar a ' + fmt(goal)}</small></td></tr>`;
    }
    function renderAnnualAlerts() {
        const scope = scopeRows().map(row => ({ row, result: NotasModel.annualRisk(row, drafts.has(row.id) ? NaN : p4(row), weights, goal) }));
        const riskStudents = new Set(scope.filter(entry => entry.result.status === 'risk').map(entry => studentKey(entry.row)));
        $('alerts-badge').textContent = riskStudents.size;
        $('alerts-badge').title = `${riskStudents.size} estudiantes con materias en riesgo en el año y grupo elegidos`;
        if (mode !== 'alerts') return;
        setOptions('alerts-subject', unique(studentRows(), 'subject'), 'Todas las materias');
        const query = normalize($('alerts-search').value.trim());
        const entries = scope.filter(({ row }) => (selected === null || studentKey(row) === selected) && (!$('alerts-subject').value || row.subject === $('alerts-subject').value) && (!query || normalize(row.student + ' ' + row.code).includes(query)));
        const grouped = new Map();
        entries.forEach(entry => {
            const id = studentKey(entry.row);
            if (!grouped.has(id)) grouped.set(id, { row: entry.row, entries: [], risks: 0, incomplete: 0 });
            const group = grouped.get(id); group.entries.push(entry);
            if (entry.result.status === 'risk') group.risks++;
            if (entry.result.status === 'incomplete') group.incomplete++;
        });
        const groups = [...grouped.values()].sort((a, b) => b.risks - a.risks || collator.compare(a.row.student, b.row.student) || collator.compare(a.row.year, b.row.year));
        $('alerts-goal').textContent = fmt(goal);
        $('alerts-students').textContent = groups.filter(group => group.risks > 0).length;
        $('alerts-subjects').textContent = entries.filter(entry => entry.result.status === 'risk').length;
        $('alerts-incomplete').textContent = entries.filter(entry => entry.result.status === 'incomplete').length;
        $('alerts-scope').textContent = selected === null ? `${grouped.size} estudiantes · ${$('year').value ? 'Año ' + $('year').value : 'Todos los años'} · ${$('group').value ? groupLabel($('group').value) : 'Todos los grupos'}` : `Estudiante: ${roster.find(row => studentKey(row) === selected)?.student || ''}`;
        $('alerts-all').hidden = selected === null;
        const filter = $('alerts-filter').value;
        const visible = groups.filter(group => filter === 'all' || (filter === 'risk' ? group.risks > 0 : group.incomplete > 0));
        const details = group => group.entries.filter(entry => filter === 'incomplete' ? entry.result.status === 'incomplete' : filter === 'risk' ? entry.result.status === 'risk' : entry.result.status !== 'pass').sort((a, b) => (a.result.grade ?? 6) - (b.result.grade ?? 6) || collator.compare(a.row.subject, b.row.subject));
        alertExportRows = visible.flatMap(details);
        $('alerts-export').disabled = !alertExportRows.length || alertExportRows.some(entry => drafts.has(entry.row.id));
        const pages = Math.max(1, Math.ceil(visible.length / 8));
        alertPage = Math.min(Math.max(alertPage, 1), pages);
        $('alerts-list').innerHTML = visible.length ? visible.slice((alertPage - 1) * 8, alertPage * 8).map(group => {
            const rows = details(group);
            return `<article class="sn-panel sn-risk-student"><header><div class="sn-risk-student-name"><span class="sn-avatar" aria-hidden="true">${esc(initials(group.row.student))}</span><div><h3>${esc(group.row.student)}</h3><p>${esc(groupLabel(group.row.group))} · Año ${esc(group.row.year)} · Código ${esc(group.row.code)}</p></div></div><div class="sn-risk-student-actions"><span class="sn-risk-count ${group.risks ? '' : 'sn-risk-count-neutral'}">${group.risks ? group.risks + (group.risks === 1 ? ' materia en riesgo' : ' materias en riesgo') : group.incomplete ? 'Datos incompletos' : 'Sin materias en riesgo'}</span><button class="sn-text-button" type="button" data-alert-student="${esc(studentKey(group.row))}">Ver notas ${icon('arrow')}</button></div></header>${rows.length ? `<div class="sn-table-scroll"><table class="sn-table sn-risk-table"><caption class="sn-sr">Alertas de ${esc(group.row.student)}</caption><thead><tr><th scope="col">Materia</th><th scope="col">Nota actual</th><th scope="col">Acumulado P1–P3<small>Aporte al año</small></th><th scope="col">Necesita en P4<small>Meta ${fmt(goal)}</small></th></tr></thead><tbody>${rows.map(alertDetailMarkup).join('')}</tbody></table></div>` : `<p class="sn-risk-clear">${icon('check')}Las ${group.entries.length} materias revisadas están en la meta o por encima de ella.</p>`}${group.incomplete && filter === 'risk' ? `<p class="sn-risk-pending-note">Además tiene ${group.incomplete} ${group.incomplete === 1 ? 'materia con datos incompletos' : 'materias con datos incompletos'}. Revísalas con el filtro «Con datos incompletos».</p>` : ''}</article>`;
        }).join('') : `<div class="sn-panel sn-risk-empty">${icon(filter === 'risk' && entries.length ? 'check' : 'search')}<h3>${!records.length ? 'Aún no hay un reporte de notas' : filter === 'risk' && entries.length ? 'No hay materias en riesgo en esta selección' : 'No hay estudiantes con este filtro'}</h3><p>${!records.length ? 'Importa el reporte para revisar las alertas del año.' : filter === 'risk' && entries.some(entry => entry.result.status === 'incomplete') ? 'Hay notas incompletas. Selecciona «Con datos incompletos» para revisarlas.' : 'Puedes cambiar el estudiante, la materia o el filtro de resultados.'}</p></div>`;
        $('alerts-count').textContent = visible.length ? `Mostrando ${(alertPage - 1) * 8 + 1}–${Math.min(alertPage * 8, visible.length)} de ${visible.length} estudiantes · Página ${alertPage} de ${pages}` : 'Sin estudiantes para mostrar';
        $('alerts-prev').disabled = alertPage <= 1; $('alerts-next').disabled = alertPage >= pages;
    }
    function missingStatus(status) {
        return {missing:'Nota no registrada',unlinked:'Sin vincular',unverified:'Sin verificar',invalid:'Nota inválida'}[status] || status;
    }
    function missingStudentKey(row) {
        return row.source_only ? JSON.stringify([row.year,NotasModel.degree(row),normalize(row.student)]) : studentKey(row);
    }
    function renderMissingNotes() {
        const scope = NotasModel.missingNotes(records, payload.data).filter(({record:row}) =>
            (!$('year').value || row.year === $('year').value) &&
            (!$('group').value || (row.source_only ? NotasModel.degree(row) === NotasModel.degree({group:$('group').value}) : row.group === $('group').value)));
        const confirmed = scope.filter(entry => entry.status === 'missing').length;
        $('missing-badge').textContent = confirmed;
        $('missing-badge').title = `${confirmed} notas no registradas en las fuentes del año y grupo elegidos`;
        if (mode !== 'missing') return;
        const studentScope = scope.filter(({record:row}) => selected === null || (!row.source_only && studentKey(row) === selected));
        setOptions('missing-subject', unique(studentScope.map(entry => entry.record),'subject'), 'Todas las materias');
        const query = normalize($('missing-search').value.trim());
        const entries = studentScope.filter(({record:row,period}) =>
            (!query || normalize(row.student+' '+row.code).includes(query)) &&
            (!$('missing-subject').value || row.subject === $('missing-subject').value) &&
            (!$('missing-period').value || period === Number($('missing-period').value)));
        $('missing-scope').textContent = selected === null ? `${$('year').value ? 'Año '+$('year').value : 'Todos los años'} · ${$('group').value ? groupLabel($('group').value) : 'Todos los grupos'} · Fuentes originales` : `Estudiante: ${roster.find(row => studentKey(row) === selected)?.student || ''}`;
        $('missing-all').hidden = selected === null;
        $('missing-state').hidden = true;
        missingExportRows = entries.filter(entry => !$('missing-status').value || entry.status === $('missing-status').value)
            .sort((a,b) => collator.compare(a.record.student,b.record.student) || collator.compare(a.record.group,b.record.group) || collator.compare(a.record.subject,b.record.subject) || a.period-b.period);
        $('missing-confirmed').textContent = missingExportRows.filter(entry => entry.status === 'missing').length;
        $('missing-students').textContent = new Set(missingExportRows.map(entry => missingStudentKey(entry.record))).size;
        $('missing-review').textContent = missingExportRows.filter(entry => entry.status !== 'missing').length;
        const total = missingExportRows.length, pages = Math.max(1,Math.ceil(total/20));
        missingPage = Math.min(Math.max(1,missingPage),pages);
        $('missing-export').disabled = total === 0;
        $('missing-rows').innerHTML = total ? missingExportRows.slice((missingPage-1)*20,missingPage*20).map(entry => {
            const row = entry.record;
            return `<tr><td class="sn-identity"><strong class="sn-missing-name">${esc(row.student)}</strong><span class="sn-subject-name">${esc(row.subject)}</span><small>${esc(groupLabel(row.group))} · ${esc(row.year)}${row.code ? ' · Cód. '+esc(row.code) : ''}</small></td><td><span class="sn-mobile-label" aria-hidden="true">Periodo</span><span class="sn-period-badge">P${entry.period}</span></td><td class="sn-missing-source"><span class="sn-mobile-label" aria-hidden="true">Fuente</span>${esc(entry.source)}${entry.page ? `<small>Página ${entry.page}</small>` : ''}</td><td class="sn-missing-reason"><span class="sn-missing-status" data-status="${esc(entry.status)}">${esc(missingStatus(entry.status))}</span><small>${esc(entry.reason)}</small></td><td class="sn-missing-action"><button class="sn-text-button" type="button" data-missing-record="${esc(row.id)}" aria-label="Ver notas de ${esc(row.student)}, ${esc(row.subject)}">Ver notas ${icon('arrow')}</button></td></tr>`;
        }).join('') : `<tr><td colspan="5" class="sn-empty"><strong>${!records.length ? 'No hay notas en el PDF de referencia' : entries.length ? 'No hay notas con este estado' : 'No hay notas pendientes en esta selección'}</strong><p>${!records.length ? 'Necesitamos las materias y los periodos del reporte académico.' : 'Prueba con otro estudiante, materia, periodo o estado.'}</p></td></tr>`;
        $('missing-count').textContent = total ? `Mostrando ${(missingPage-1)*20+1}–${Math.min(missingPage*20,total)} de ${total} notas · Página ${missingPage} de ${pages}` : 'Sin notas para mostrar';
        $('missing-prev').disabled = missingPage <= 1;
        $('missing-next').disabled = missingPage >= pages;
    }
    function resultMarkup(row) {
        const result = calc(row);
        const badge = drafts.has(row.id) ? 'Revisa P4' : result.status === 'pass' ? 'Alcanza meta' : result.status === 'risk' ? 'Bajo la meta' : result.required === null ? 'Faltan notas' : 'Sin P4';
        return `<span class="sn-mobile-label" aria-hidden="true">Final proyectada</span><strong class="sn-final ${result.final === null ? 'sn-final-empty' : ''}">${fmt(result.final)}</strong><span class="sn-result-badge ${result.status}">${result.status === 'pass' ? icon('check') : ''}${badge}</span>`;
    }
    function requiredMarkup(row) {
        const result = calc(row), need = result.required;
        const text = need === null ? '—' : need > 5 + 1e-9 ? 'Más de 5,00' : fmt(Math.max(1, Math.ceil((need - 1e-9) * 100) / 100));
        const note = need === null ? 'Faltan notas P1–P3' : need > 5 + 1e-9 ? 'No alcanza la meta' : `para una final de ${fmt(goal)}`;
        return `<span class="sn-mobile-label" aria-hidden="true">Necesita en P4</span><strong class="sn-required ${need > 5 + 1e-9 ? 'sn-required-impossible' : ''}">${text}</strong><small class="sn-required-note">${note}</small>`;
    }
    function gradeHelp(row) {
        if (drafts.has(row.id)) return 'Nota de 1,00 a 5,00';
        return isEdited(row) ? `Simulada · Base: ${fmt(baselineP4(row))}` : row.p4_correction ? 'Actualizada · editable' : row.p4 === null ? 'Sin nota en el PDF · editable' : 'Del PDF · editable';
    }
    function p4OriginLabel(row) {
        return isEdited(row) ? 'Simulada sobre nota de consulta' : row.p4_correction ? 'Nota actualizada' : row.p4 === null ? 'Sin nota en el PDF' : 'PDF de SISGA';
    }
    function renderRows() {
        const historical = [1, 2, 3].filter(period => $('period').value === 'all' || $('period').value === String(period));
        table.classList.toggle('sn-overview-table', selected === null);
        table.dataset.period = $('period').value;
        $('head').innerHTML = `<tr><th scope="col">${selected === null ? 'Estudiante / Materia' : 'Materia'}</th>${historical.map(period => `<th scope="col">PERIODO ${period}<small>${weights[period - 1]} %</small></th>`).join('')}<th scope="col" class="sn-col-p4">P4 · EDITABLE ${icon('edit')}<small>Consulta · ${weights[3]} %</small></th><th scope="col">FINAL<small>simulada</small></th><th scope="col">NECESITA EN P4<small>para la meta</small></th></tr>`;
        const size = Number($('page-size').value), pages = Math.max(1, Math.ceil(current.length / size));
        page = Math.min(Math.max(page, 1), pages);
        $('rows').innerHTML = current.length ? current.slice((page - 1) * size, page * size).map(row => {
            const edited = isEdited(row), invalid = drafts.has(row.id), value = invalid ? drafts.get(row.id) : inputValue(p4(row));
            return `<tr data-row="${row.id}"><td class="sn-identity">${selected === null ? `<button class="sn-student-link" type="button" data-select-student="${esc(studentKey(row))}">${esc(row.student)}</button>` : ''}<span class="sn-subject-name">${esc(row.subject)}</span>${selected === null ? `<small>${esc(groupLabel(row.group))} · ${esc(row.year)}</small>` : ''}</td>${historical.map(period => `<td class="sn-grade ${row['p' + period] === null ? 'sn-grade-missing' : row['p' + period] < goal ? 'sn-grade-below' : ''}"><span class="sn-mobile-label" aria-hidden="true">Periodo ${period}</span>${fmt(row['p' + period])}</td>`).join('')}<td class="sn-col-p4"><span class="sn-mobile-label" aria-hidden="true">P4 · Editar</span><div class="sn-grade-editor"><input class="sn-grade-input" type="number" min="1" max="5" step="0.01" inputmode="decimal" autocomplete="off" data-id="${row.id}" value="${esc(value)}" placeholder="—" aria-label="P4 de ${esc(row.student)}, ${esc(row.subject)}" aria-invalid="${invalid}" aria-describedby="hint-${row.id}"><button class="sn-row-restore" type="button" data-restore="${row.id}" aria-label="Restaurar P4 de ${esc(row.subject)}" title="Restaurar la nota del reporte" ${edited || invalid ? '' : 'hidden'}>${icon('reset')}</button></div><small id="hint-${row.id}" class="sn-grade-help ${invalid ? 'sn-input-error' : ''}">${gradeHelp(row)}</small></td><td class="sn-col-final">${resultMarkup(row)}</td><td class="sn-col-required">${requiredMarkup(row)}</td></tr>`;
        }).join('') : `<tr><td class="sn-empty" colspan="${historical.length + 4}">${icon(records.length ? 'search' : 'file')}<strong>${records.length ? 'No hay materias en esta vista' : 'No hay notas en el PDF de referencia'}</strong><p>${records.length ? 'Cambia los filtros para volver a ver las materias y sus notas.' : 'Las notas se cargan desde el PDF de referencia.'}</p><button class="sn-btn" type="button" data-empty-action>${records.length ? 'Limpiar filtros' : 'Revisar vista'}</button></td></tr>`;
        table.dataset.source = 'pdf';
        $('rows').querySelectorAll('[data-id]').forEach(decorateP4);
        $('count').textContent = current.length ? `Mostrando ${(page - 1) * size + 1}–${Math.min(page * size, current.length)} de ${current.length} ${selected === null ? 'registros' : 'materias'}` : 'Sin resultados';
        $('page-label').textContent = `${page} / ${pages}`;
        $('prev').disabled = page <= 1; $('next').disabled = page >= pages;
        $('page-size').disabled = !current.length;
    }
    function render() {
        current = filteredRows();
        renderContext(); renderSummary(); renderRows();
        const filtered = !!$('subject').value || $('status').value !== 'all' || $('period').value !== 'all';
        $('clear').hidden = !filtered;
        $('filter-context').hidden = !filtered;
        $('filter-context').textContent = [current.length + ' resultados', $('subject').value, $('status').value !== 'all' ? $('status').selectedOptions[0].textContent : '', $('period').value !== 'all' ? 'La final utiliza todos los periodos, aunque algunos no estén visibles.' : ''].filter(Boolean).join(' · ');
    }
    function refreshEditedRow(input, summarize=true) {
        const row = byId.get(input.dataset.id), element = input.closest('tr');
        element.querySelector('.sn-col-final').innerHTML = resultMarkup(row);
        input.setAttribute('aria-invalid', String(drafts.has(row.id)));
        const hint = $('hint-' + row.id);
        hint.textContent = gradeHelp(row); hint.classList.toggle('sn-input-error', drafts.has(row.id));
        decorateP4(input);
        if(summarize)renderSummary();
    }
    function decorateP4(input) {
        const row = byId.get(input.dataset.id), edited = isEdited(row);
        input.classList.toggle('sn-grade-edited', edited);
        input.closest('td').classList.toggle('sn-cell-overridden', edited);
        input.title = `${row.p4_correction ? 'Nota actualizada' : 'Base PDF'}: ${fmt(row.p4)}${row.source_page ? ' · Página ' + row.source_page : ''}. Puedes editar para simular.`;
        const restore = input.closest('td').querySelector('[data-restore]');
        restore.hidden = !edited && !drafts.has(row.id);
        restore.title = 'Restaurar la nota de consulta';
    }
    function restoreRow(id) {
        pushUndo(); delete activeScenarios()[id]; drafts.delete(id); save();
        const input = [...$('rows').querySelectorAll('[data-id]')].find(node => node.dataset.id === id);
        if (input) { input.value = inputValue(p4(byId.get(id))); refreshEditedRow(input); }
        notify('Se restauró la nota P4 de consulta. Puedes deshacer el cambio.');
    }

    views.forEach(view => $(view + '-tab').addEventListener('click', () => switchMode(view)));
    document.querySelector('.sn-view-tabs').addEventListener('keydown', event => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        const next = event.key === 'Home' ? views[0] : event.key === 'End' ? views.at(-1) : views[(views.indexOf(mode)+(event.key==='ArrowRight'?1:views.length-1))%views.length];
        switchMode(next); $(next + '-tab').focus();
    });
    ['alerts-search', 'alerts-subject', 'alerts-filter'].forEach(id => $(id).addEventListener(id === 'alerts-search' ? 'input' : 'change', () => { alertPage = 1; renderAnnualAlerts(); }));
    $('alerts-all').addEventListener('click', () => chooseStudent(null));
    $('alerts-prev').addEventListener('click', () => { alertPage--; renderAnnualAlerts(); });
    $('alerts-next').addEventListener('click', () => { alertPage++; renderAnnualAlerts(); $('alerts-panel').scrollIntoView({ block: 'start' }); });
    $('alerts-list').addEventListener('click', event => {
        const recordButton = event.target.closest('[data-alert-record]'), studentButton = event.target.closest('[data-alert-student]');
        if (!recordButton && !studentButton) return;
        const row = recordButton ? byId.get(recordButton.dataset.alertRecord) : null;
        mode = 'consultation'; syncMode(); chooseStudent(row ? studentKey(row) : studentButton.dataset.alertStudent);
        if (row) {
            $('subject').value = row.subject; render();
            $('rows').querySelector('[data-id]')?.focus();
        } else $('consultation-panel').scrollIntoView({ block: 'start' });
    });
    $('alerts-export').addEventListener('click', () => {
        if (!alertExportRows.length || $('alerts-export').disabled) return;
        const rows = [['Año', 'Grupo', 'Código', 'Estudiante', 'Materia', 'Nota actual', 'Base del cálculo', 'Acumulado P1-P3', 'Peso P1-P3 %', 'Diferencia bajo la meta', 'Necesita en P4', 'Meta', 'Estado', 'Peso P1 %', 'Peso P2 %', 'Peso P3 %', 'Peso P4 %','Origen P4','Archivo P4','Página PDF','P4 de consulta']];
        const numeric = value => Number.isFinite(value) ? value.toFixed(4) : '';
        alertExportRows.forEach(entry => { const { row, result } = entry; const origin={file:payload.data.source,page:row.source_page,value:row.p4}; rows.push([row.year, row.group, row.code, row.student, row.subject, numeric(result.grade), alertGradeLabel(entry), numeric(result.accumulated), result.historyWeight, numeric(result.gap), numeric(result.required), goal, result.status === 'risk' ? 'En riesgo' : 'Datos incompletos', ...weights,p4OriginLabel(row),origin?.file||'',origin.page||'',origin?.value??'']); });
        downloadCsv(rows, 'alertas-del-ano.csv');
        notify(`Se exportaron ${alertExportRows.length} materias de las alertas filtradas.`);
    });
    ['missing-search','missing-subject','missing-period','missing-status'].forEach(id => $(id).addEventListener(id === 'missing-search' ? 'input' : 'change', () => { missingPage=1; renderMissingNotes(); }));
    $('missing-all').addEventListener('click', () => chooseStudent(null));
    $('missing-prev').addEventListener('click', () => { missingPage--; renderMissingNotes(); });
    $('missing-next').addEventListener('click', () => { missingPage++; renderMissingNotes(); $('missing-panel').scrollIntoView({block:'start'}); });
    $('missing-rows').addEventListener('click', event => {
        const button = event.target.closest('[data-missing-record]');
        if (!button) return;
        const row = byId.get(button.dataset.missingRecord);
        if (!row) return;
        mode='consultation'; syncMode(); chooseStudent(studentKey(row));
        $('subject').value=row.subject; render();
        $('sn-results').scrollIntoView({block:'start'});
        $('rows').querySelector('[data-id]')?.focus({preventScroll:true});
    });
    $('missing-export').addEventListener('click', () => {
        if (!missingExportRows.length) return;
        const rows=[['Año','Grupo','Código','Estudiante','Materia','Periodo','Estado','Fuente','Documento','Página PDF','Motivo']];
        missingExportRows.forEach(entry => {const row=entry.record; rows.push([row.year,row.group,row.code,row.student,row.subject,'P'+entry.period,missingStatus(entry.status),entry.source,'PDF',entry.page||'',entry.reason]);});
        downloadCsv(rows,'notas-faltantes.csv');
        notify(`Se exportaron ${missingExportRows.length} notas de la selección filtrada.`);
    });
    $('search').addEventListener('input', renderRoster);
    $('search').addEventListener('keydown', event => {
        if (event.key === 'Enter' && visibleRoster[0]) { event.preventDefault(); chooseStudent(studentKey(visibleRoster[0])); }
        if (event.key === 'Escape') { $('search').value = ''; renderRoster(); }
    });
    $('student-list').addEventListener('click', event => {
        const button = event.target.closest('[data-student]');
        if (button) chooseStudent(button.dataset.student);
        if (event.target.closest('[data-list-clear]')) { $('search').value = ''; renderRoster(); $('search').focus(); }
    });
    $('overview').addEventListener('click', () => chooseStudent(null));
    $('roster-toggle').addEventListener('click', () => setRosterOpen(document.querySelector('.sn-sidebar').dataset.open !== 'true'));
    ['year', 'group'].forEach(id => $(id).addEventListener('change', () => {
        alertPage = 1; missingPage = 1; reportPage = 1;
        $('search').value = ''; if (id === 'year') $('group').value = '';
        rebuildRoster(); clearTableFilters(); updateSubjectOptions(); render(); save();
    }));
    ['subject', 'period', 'status', 'page-size'].forEach(id => $(id).addEventListener('change', () => { page = 1; render(); }));
    $('clear').addEventListener('click', () => { clearTableFilters(); render(); });
    $('prev').addEventListener('click', () => { page--; render(); });
    $('next').addEventListener('click', () => { page++; render(); $('sn-results').scrollIntoView({ block: 'start', behavior: 'auto' }); });
    $('rows').addEventListener('focusin', event => {
        if (!event.target.matches('[data-id]')) return;
        editingId = event.target.dataset.id; editHasUndo = false; event.target.select();
    });
    $('rows').addEventListener('input', event => {
        const input = event.target; if (!input.matches('[data-id]')) return;
        if (editingId !== input.dataset.id || !editHasUndo) { pushUndo(); editHasUndo = true; editingId = input.dataset.id; }
        const parsed = input.validity.badInput ? {valid:false,value:null} : parseGrade(input.value);
        if (parsed.valid) { setGrade(byId.get(input.dataset.id), parsed.value); save(); }
        else drafts.set(input.dataset.id, input.value);
        refreshEditedRow(input);
    });
    $('rows').addEventListener('focusout', event => {
        const input = event.target; if (!input.matches('[data-id]')) return;
        if (!drafts.has(input.dataset.id)) input.value = inputValue(p4(byId.get(input.dataset.id)));
        editingId = null;
        if ($('status').value !== 'all') setTimeout(() => { if (!document.activeElement?.matches('[data-id]')) render(); }, 0);
    });
    $('rows').addEventListener('keydown', event => {
        if (!event.target.matches('[data-id]')) return;
        if (event.key === 'Enter') {
            event.preventDefault();
            if (drafts.has(event.target.dataset.id)) { notify('Revisa la nota: debe estar entre 1,00 y 5,00.'); return; }
            const inputs = [...$('rows').querySelectorAll('[data-id]')], next = inputs[inputs.indexOf(event.target) + (event.shiftKey ? -1 : 1)];
            if (next) next.focus(); else event.target.blur();
        }
        if (event.key === 'Escape') { event.preventDefault(); restoreRow(event.target.dataset.id); event.target.select(); }
    });
    $('rows').addEventListener('click', event => {
        const student = event.target.closest('[data-select-student]'), restore = event.target.closest('[data-restore]');
        if (student) chooseStudent(student.dataset.selectStudent);
        if (restore) restoreRow(restore.dataset.restore);
        if (event.target.closest('[data-empty-action]')) { clearTableFilters(); render(); }
    });
    $('undo').addEventListener('click', () => {
        const stack = activeHistory();
        if (!stack.length) return;
        scenarios = stack.pop();
        drafts.clear(); $('undo').disabled = !stack.length;
        save(); render(); notify('Se recuperó el escenario anterior.');
    });
    $('reset').addEventListener('click', () => {
        const changed = current.filter(row => isEdited(row) || drafts.has(row.id));
        if (!changed.length) return;
        pushUndo(); changed.forEach(row => { delete activeScenarios()[row.id]; drafts.delete(row.id); });
        save(); render(); notify(`Se restauraron ${changed.length} notas de esta vista. Puedes deshacer el cambio.`);
    });

    function openDialog(id) { $(id).showModal(); }
    document.querySelectorAll('[data-close]').forEach(button => button.addEventListener('click', () => button.closest('dialog').close()));
    document.querySelectorAll('.sn-dialog').forEach(dialog => dialog.addEventListener('click', event => {
        if (event.target !== dialog) return;
        const box = dialog.getBoundingClientRect();
        if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) dialog.close();
    }));
    $('help-open').addEventListener('click', () => openDialog('help-dialog'));
    function previewBulk() {
        const parsed = parseGrade($('bulk').value), valid = !$('bulk').validity.badInput && parsed.valid && parsed.value !== null;
        $('bulk-error').hidden = valid; $('bulk').setAttribute('aria-invalid', String(!valid)); $('apply').disabled = !valid;
        if (!valid) { $('bulk-preview').textContent = 'Corrige la nota para ver la proyección.'; return; }
        $('bulk-range').value = parsed.value;
        const results = bulkRows.map(row => NotasModel.calculate(row, parsed.value, weights, goal)).filter(result => result.final !== null);
        $('bulk-preview').innerHTML = `Promedio final con P4 = ${fmt(parsed.value)}<strong>${fmt(results.length ? results.reduce((sum, result) => sum + result.final, 0) / results.length : null)}</strong><small>${results.length} de ${bulkRows.length} materias con notas suficientes para proyectar.</small>`;
    }
    $('bulk-open').addEventListener('click', () => {
        bulkRows = [...current];
        $('bulk-scope').textContent = `Aplica una nota de P4 a ${bulkRows.length} ${selected === null ? 'registros de la selección actual' : 'materias de ' + (roster.find(row => studentKey(row) === selected)?.student || 'la selección')}.`;
        $('apply').textContent = `Aplicar a ${bulkRows.length} ${selected === null ? 'registros' : 'materias'}`;
        previewBulk(); openDialog('bulk-dialog');
    });
    $('bulk').addEventListener('input', previewBulk);
    $('bulk-range').addEventListener('input', () => { $('bulk').value = inputValue(Number($('bulk-range').value)); previewBulk(); });
    $('bulk-form').addEventListener('submit', event => {
        event.preventDefault(); const parsed = parseGrade($('bulk').value);
        if (!parsed.valid || parsed.value === null || !bulkRows.length) return;
        pushUndo(); bulkRows.forEach(row => setGrade(row, parsed.value));
        save(); render(); $('bulk-dialog').close();
        notify(`P4 = ${fmt(parsed.value)} aplicado a ${bulkRows.length} registros. Puedes deshacer el cambio.`);
    });
    function validateSettings() {
        const draftWeights = [1, 2, 3, 4].map(i => $('w' + i).value === '' ? NaN : Number($('w' + i).value));
        const draftGoal = $('target').value === '' ? NaN : Number($('target').value);
        const total = draftWeights.reduce((sum, weight) => sum + weight, 0);
        const valid = NotasModel.calculate({ p1: 3, p2: 3, p3: 3 }, 3, draftWeights, draftGoal).valid && $('settings-form').checkValidity();
        $('weight-total').textContent = `${Number.isFinite(total) ? total.toLocaleString('es-CO') : '—'} % de 100 %${valid ? ' · Distribución válida' : ' · Revisa los porcentajes y la meta'}`;
        $('weight-total').classList.toggle('sn-invalid', !valid); $('settings-save').disabled = !valid;
        return { valid, weights: draftWeights, goal: draftGoal };
    }
    $('settings-open').addEventListener('click', () => { weights.forEach((weight, i) => $('w' + (i + 1)).value = weight); $('target').value = goal; validateSettings(); openDialog('settings-dialog'); });
    $('settings-form').addEventListener('input', validateSettings);
    $('equal-weights').addEventListener('click', () => { [1, 2, 3, 4].forEach(i => $('w' + i).value = '25'); validateSettings(); });
    $('settings-form').addEventListener('submit', event => {
        event.preventDefault(); const draft = validateSettings(); if (!draft.valid) return;
        weights = draft.weights; goal = draft.goal; save(); render(); $('settings-dialog').close(); notify('Se actualizaron los porcentajes y la meta del escenario.');
    });
    function downloadCsv(rows, filename) {
        const csvCell = value => { let text = String(value ?? ''); if (/^\s*[=+@\-\t\r]/.test(text)) text = "'" + text; return '"' + text.replace(/"/g, '""') + '"'; };
        const url = URL.createObjectURL(new Blob(['\uFEFF' + rows.map(row => row.map(csvCell).join(';')).join('\r\n')], { type: 'text/csv;charset=utf-8' }));
        const anchor = document.createElement('a'); anchor.href = url; anchor.download = filename; anchor.click(); setTimeout(() => URL.revokeObjectURL(url), 1000);
    }
    $('export').addEventListener('click', () => {
        const statusText = { pass: 'Alcanza la meta', risk: 'Bajo la meta', pending: 'Sin proyección' };
        const rows = [['Año', 'Grupo', 'Código', 'Estudiante', 'Materia', 'P1', 'P2', 'P3', 'P4 usada en la simulación', 'Final proyectada', 'Necesita P4', 'Estado', 'Peso P1 %', 'Peso P2 %', 'Peso P3 %', 'Peso P4 %', 'Meta','Origen P4','Archivo P4','Página PDF','P4 de consulta']];
        const gradeCell = value => Number.isFinite(value) ? value.toFixed(2) : '';
        current.forEach(row => { const result = calc(row); const origin={file:payload.data.source,page:row.source_page,value:row.p4}; rows.push([row.year, row.group, row.code, row.student, row.subject, gradeCell(row.p1), gradeCell(row.p2), gradeCell(row.p3), gradeCell(p4(row)), result.final === null ? '' : result.final.toFixed(4), result.required === null ? '' : result.required.toFixed(4), statusText[result.status], ...weights, goal,p4OriginLabel(row),origin?.file||'',origin.page||'',origin?.value??'']); });
        downloadCsv(rows, 'simulacion-notas.csv');
        notify(`Se exportaron ${current.length} registros de la vista actual.`);
    });

    // Recover the last student in this tab; a first visit starts with one student.
    rebuildRoster(false);
    if (unique(records.filter(row => !$('year').value || row.year === $('year').value), 'group').includes(savedView.group)) { $('group').value = savedView.group; rebuildRoster(); }
    if (Object.hasOwn(savedView, 'student') && (savedView.student === null || roster.some(row => studentKey(row) === savedView.student))) selected = savedView.student;
    mode = views.includes(savedView.mode) ? savedView.mode : 'consultation';
    syncMode(); updateSubjectOptions(); renderRoster(); render();
    $('source').textContent = 'Reporte: ' + payload.data.source;
    $('source').title = payload.data.source;
    const reportDate = new Date(payload.data.report_date ? payload.data.report_date+'T12:00:00-05:00' : payload.data.imported_at);
    $('source-date').textContent = Number.isNaN(reportDate.getTime()) ? '' : 'Reporte del ' + reportDate.toLocaleDateString('es-CO', { timeZone:'America/Bogota',day:'numeric',month:'short',year:'numeric' });
})();
