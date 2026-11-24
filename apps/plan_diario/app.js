(() => {
  'use strict';
  const $ = id => document.getElementById(id);
  const config = window.dailyPlanner, today = config.today;
  const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
  const icon = (name, cls = '') => `<svg class="${cls}" aria-hidden="true"><use href="#i-${name}"/></svg>`;
  const fromDate = value => new Date(`${value}T12:00:00`);
  const iso = value => `${value.getFullYear()}-${String(value.getMonth()+1).padStart(2,'0')}-${String(value.getDate()).padStart(2,'0')}`;
  const addDays = (value, count) => { const day = fromDate(value); day.setDate(day.getDate()+count); return iso(day); };
  const allowedDate = value => /^\d{4}-\d{2}-\d{2}$/.test(value) && value >= '2000-01-01' && value <= '2100-12-31' && iso(fromDate(value)) === value;
  const requestedDate = new URL(location.href).searchParams.get('date');
  let date = allowedDate(requestedDate || '') ? requestedDate : today;
  let data = null, category = 'all', mode = 'edit', busy = false, focusDirty = false, confirmAction = null, toastTimer;
  const taskEdits = new Map(), categoryEdits = new Map(), newTaskDrafts = new Map();
  let complianceBusy = false;
  const hasEdits = () => taskEdits.size > 0 || categoryEdits.size > 0;
  const categoryName = c => categoryEdits.get(c.id) ?? c.name;
  const draftKey = id => `${date}:${id}`;
  const colorNames = {purple:'Violeta',blue:'Azul',green:'Verde',orange:'Naranja',pink:'Rosa'};
  const colorOptions = selected => Object.entries(colorNames).map(([value,label]) => `<option value="${value}"${value === selected ? ' selected' : ''}>${label}</option>`).join('');
  const categoryOptions = (selected, exclude) => data.categories.filter(c => c.id !== exclude).map(c => `<option value="${c.id}"${c.id === Number(selected) ? ' selected' : ''}>${escape(categoryName(c))}</option>`).join('');
  function setStatus(text, cls = '') {
    if (text === 'Todo guardado' && (hasEdits() || focusDirty)) text = 'Cambios sin guardar';
    $('save-status').textContent = text; $('save-status').className = `save-status ${cls}`;
  }
  function toast(message) { clearTimeout(toastTimer); $('toast').textContent = message; $('toast').hidden = false; toastTimer = setTimeout(() => { $('toast').hidden = true; },4500); }
  function showError(error) { $('error-banner').hidden = false; $('error-banner').querySelector('span').textContent = error.message; setStatus('Sin guardar','error'); }
  function controls() {
    const disabled = busy || !data;
    for (const id of ['new-task','copy-open','focus','nav-categories','side-add','today','prev-week','next-week','date-picker']) $(id).disabled = disabled;
    for (const item of document.querySelectorAll('button[type="submit"], [data-mutation], .week-day, [data-inline-task], [data-inline-category], [data-section-input]')) item.disabled = disabled || item.hasAttribute('data-edge');
    $('focus-save').disabled = disabled || !focusDirty;
    $('document-save').disabled = disabled || !hasEdits();
    $('document-save').hidden = mode !== 'edit';
    $('document-reset').hidden = !hasEdits(); $('document-reset').disabled = disabled;
    $('side-add').hidden = mode !== 'edit';
    $('task-list').setAttribute('aria-busy',String(busy));
    for (const id of ['view-edit','view-check','view-monitor']) $(id).disabled = disabled;
    for (const id of ['compliance-period','compliance-from','compliance-to','compliance-refresh']) $(id).disabled = busy;
    $('new-task').hidden = mode === 'monitor';
    $('nav-categories').hidden = mode === 'monitor';
  }
  async function request(method, body, targetDate = date, report = null) {
    const abort = new AbortController(), timeout = setTimeout(() => abort.abort(),30000);
    try {
      const params = new URLSearchParams({date:targetDate,...(report ? {report:'compliance',...report} : {})});
      const response = await fetch(`api.php?${params}`,{method,credentials:'same-origin',cache:'no-store',headers:body ? {'Content-Type':'application/json','X-CSRF-Token':config.csrf} : {},body:body ? JSON.stringify(body) : undefined,signal:abort.signal});
      let json;
      try { json = await response.json(); } catch { throw new Error('El servidor no devolvió una respuesta válida. Intenta nuevamente.'); }
      if (!response.ok || !json.ok) { const error = new Error(json.message || 'No se pudo guardar la hoja.'); error.status = response.status; throw error; }
      return json.data;
    } catch (error) {
      if (error.name === 'AbortError') throw new Error('La conexión tardó demasiado. Puedes volver a intentarlo; tus cambios siguen aquí.');
      if (error instanceof TypeError) throw new Error('No hay conexión con el servidor. Revisa la conexión e intenta nuevamente.');
      throw error;
    } finally { clearTimeout(timeout); }
  }
  function apply(next) {
    data = next; date = next.date;
    if (category !== 'all' && !data.categories.some(c => c.id === category)) category = 'all';
    render();
    if (!focusDirty) { $('focus').value = data.focus; $('focus-status').textContent = data.focus ? 'Nota guardada' : 'Una nota para tu día'; }
    const url = new URL(location.href); url.searchParams.set('date',date); history.replaceState(null,'',url);
  }
  async function load(target = date) {
    if (busy) return false;
    busy = true; controls(); setStatus('Abriendo la hoja…','saving');
    try {
      const next = await request('GET',null,target);
      if (target !== date) { category = 'all'; $('search').value = ''; $('filter').value = 'all'; }
      focusDirty = false; apply(next);
      $('error-banner').hidden = true; setStatus('Todo guardado'); return true;
    } catch (error) { showError(error); return false; }
    finally { busy = false; controls(); }
  }
  async function mutate(action, values = {}) {
    if (busy || !data) return null;
    busy = true; controls(); setStatus('Guardando…','saving');
    try {
      const next = await request('POST',{action,date,revision:data.revision,...values});
      apply(next); $('error-banner').hidden = true; setStatus('Todo guardado'); return next;
    } catch (error) {
      if (error.status === 409) { try { apply(await request('GET')); } catch { /* Preserve the local edits and show the original error. */ } }
      showError(error); throw error;
    } finally { busy = false; controls(); }
  }
  async function saveDocument() {
    if (!hasEdits()) return true;
    const tasks = Array.from(taskEdits,([id,title]) => ({id,title}));
    const categories = Array.from(categoryEdits,([id,name]) => ({id,name}));
    if (tasks.some(t => !t.title.trim()) || categories.some(c => !c.name.trim())) throw new Error('Escribe un texto en cada título y tarea antes de guardar.');
    const next = await mutate('edit_document',{tasks,categories}); if (!next) return false;
    for (const task of tasks) if (taskEdits.get(task.id) === task.title) taskEdits.delete(task.id);
    for (const c of categories) if (categoryEdits.get(c.id) === c.name) categoryEdits.delete(c.id);
    renderCategories(); renderTasks(); setStatus('Todo guardado'); controls(); return true;
  }
  async function saveFocus() {
    if (!focusDirty) return true;
    const next = await mutate('focus',{focus:$('focus').value}); if (!next) return false;
    focusDirty = false; $('focus').value = next.focus; $('focus-status').textContent = 'Nota guardada'; setStatus('Todo guardado'); controls(); return true;
  }
  async function saveAll() { return (await saveDocument()) && (await saveFocus()); }
  async function navigate(target) {
    if (busy || !allowedDate(target) || target === date) return;
    try { if (await saveAll()) await load(target); } catch (error) { showError(error); }
  }
  function renderWeek() {
    const selected = fromDate(date), monday = addDays(date,-((selected.getDay()+6)%7));
    $('month-label').textContent = selected.toLocaleDateString('es-CO',{month:'long',year:'numeric'}).replace(/^./,c => c.toUpperCase());
    $('date-picker').value = date;
    $('week-strip').innerHTML = Array.from({length:7},(_,i) => {
      const day = addDays(monday,i), value = fromDate(day), stats = data.week.find(w => w.plan_date === day);
      return `<button type="button" class="week-day${day === date ? ' selected' : ''}${day === today ? ' is-today' : ''}" data-date="${day}" aria-pressed="${day === date}" aria-label="${escape(value.toLocaleDateString('es-CO',{weekday:'long',day:'numeric',month:'long'}))}"${!allowedDate(day) ? ' disabled data-edge' : ''}><span>${value.toLocaleDateString('es-CO',{weekday:'short'}).replace('.','')}</span><strong>${value.getDate()}</strong><small>${stats ? `${stats.done}/${stats.total} hechas` : (day === today ? 'Hoy' : '')}</small></button>`;
    }).join('');
    $('day-caption').textContent = selected.toLocaleDateString('es-CO',{weekday:'long',day:'numeric',month:'long',year:'numeric'}).replace(/^./,c => c.toUpperCase());
  }
  function renderCategories() {
    const count = id => data.tasks.filter(t => t.category_id === id).length;
    $('category-tabs').innerHTML = `<button type="button" class="category-tab${category === 'all' ? ' selected' : ''}" role="tab" aria-selected="${category === 'all'}" data-category="all">Toda la hoja</button>` + data.categories.map(c => `<button type="button" role="tab" aria-selected="${category === c.id}" class="category-tab${category === c.id ? ' selected' : ''}" data-category="${c.id}">${escape(categoryName(c))}<small>${count(c.id)}</small></button>`).join('') + `<button class="category-tab manage" type="button" id="tabs-manage" aria-label="Administrar títulos y categorías">${icon('plus')}</button>`;
  }
  function visibleTasks() {
    const term = $('search').value.trim().toLocaleLowerCase('es'), filter = $('filter').value;
    return data.tasks.filter(t => (category === 'all' || t.category_id === category) && (!term || `${taskEdits.get(t.id) ?? t.title} ${t.description}`.toLocaleLowerCase('es').includes(term)) && (filter === 'all' || (filter === 'pending' && !t.done) || (filter === 'done' && t.done) || (filter === 'high' && t.priority === 'alta')));
  }
  function resizeText(input) { input.style.height = 'auto'; input.style.height = `${input.scrollHeight}px`; }
  function sectionHeading(c) {
    const editable = mode === 'edit' && !c.managed;
    const heading = editable ? `<input id="section-${c.id}" class="inline-heading${categoryEdits.has(c.id) ? ' has-edit' : ''}" data-inline-category="${c.id}" aria-label="${escape('Título de la sección '+c.name)}" value="${escape(categoryName(c))}" maxlength="100" title="Este título es una categoría y se usa en todos tus días.">` : `<span id="section-${c.id}">${escape(categoryName(c))}</span>`;
    const options = editable ? `<button type="button" class="icon-button section-manage" data-section-manage="${c.id}" aria-label="${escape('Opciones del título '+c.name)}">•••</button>` : '';
    const hours = (c.schedule_blocks || []).map(block => `${block.start}–${block.end}`).join(' · ');
    const caption = c.scheduled ? `Horario${hours ? ' · '+hours : ''}` : (c.saved_only ? 'Tareas guardadas para esta fecha' : '');
    return `<div class="outline-heading"><h2>${heading}</h2>${options}</div>${caption ? `<p class="section-schedule">${escape(caption)}</p>` : ''}`;
  }
  function taskActions(task, previous, next) {
    return `<div class="task-actions"><button type="button" class="text-button task-edit-button" data-edit="${task.id}" data-mutation aria-label="${escape('Editar tarea: '+task.title)}">${icon('edit')} Editar</button>${mode === 'edit' ? `<button type="button" class="icon-button order-button" data-swap="${task.id}" data-other="${previous || ''}" data-mutation aria-label="Subir tarea"${!previous ? ' disabled data-edge' : ''}>⌃</button><button type="button" class="icon-button order-button" data-swap="${task.id}" data-other="${next || ''}" data-mutation aria-label="Bajar tarea"${!next ? ' disabled data-edge' : ''}>⌄</button><button type="button" class="icon-button delete" data-delete="${task.id}" data-mutation aria-label="${escape('Eliminar: '+task.title)}">${icon('trash')}</button>` : ''}</div>`;
  }
  function renderTasks() {
    const tasks = visibleTasks(), filtered = $('search').value.trim() !== '' || $('filter').value !== 'all';
    const groups = data.categories.filter(c => (category === 'all' || c.id === category) && (!filtered || tasks.some(t => t.category_id === c.id)));
    $('view-check').setAttribute('aria-pressed',String(mode === 'check')); $('view-edit').setAttribute('aria-pressed',String(mode === 'edit'));
    $('view-monitor').setAttribute('aria-pressed',String(mode === 'monitor'));
    $('daily-sheet').hidden = mode === 'monitor'; $('compliance-sheet').hidden = mode !== 'monitor';
    document.querySelector('.date-controls').hidden = mode === 'monitor';
    document.querySelector('.calendar-panel').hidden = mode === 'monitor';
    document.querySelector('.workspace-hint').hidden = mode === 'monitor';
    $('mode-help').textContent = mode === 'edit' ? 'Edita el texto en la hoja y pulsa Guardar cambios. Usa Editar junto a una tarea para cambiar también su fecha y detalles.' : 'Marca las casillas al terminar. Usa Editar junto a una tarea para hacer cambios.';
    if (!groups.length) {
      $('task-list').innerHTML = '<div class="empty-state"><p>No hay tareas con estos filtros.</p><button class="button" id="clear-filters" type="button">Ver toda la hoja</button></div>';
    } else {
      $('task-list').innerHTML = groups.map(c => {
        const sectionTasks = tasks.filter(t => t.category_id === c.id);
        const rows = sectionTasks.map((t,index) => `<li class="outline-row${t.done ? ` done color-${t.highlight}` : ''}${taskEdits.has(t.id) ? ' has-edit' : ''}" data-task="${t.id}"><div class="task-line"><span class="list-bullet" aria-hidden="true">•</span><label class="task-check"><input type="checkbox" data-complete="${t.id}" data-mutation aria-label="${escape((t.done ? 'Marcar pendiente: ' : 'Completar: ')+t.title)}"${t.done ? ' checked' : ''}${mode === 'edit' ? ' disabled data-edge' : ''}><span>${icon('check')}</span></label><div class="task-content">${mode === 'edit' ? `<textarea class="inline-task" rows="1" maxlength="240" data-inline-task="${t.id}" aria-label="${escape('Texto de la tarea: '+t.title)}">${escape(taskEdits.get(t.id) ?? t.title)}</textarea>` : `<span class="task-title">${escape(t.title)}</span>`}${t.description ? `<details class="task-note"><summary>Notas</summary><p>${escape(t.description)}</p></details>` : ''}</div>${t.task_time || t.priority === 'alta' ? `<span class="task-properties">${t.task_time ? escape(t.task_time) : ''}${t.priority === 'alta' ? '<span title="Prioridad alta" aria-label="Prioridad alta">⚑</span>' : ''}</span>` : ''}${taskActions(t,sectionTasks[index-1]?.id,sectionTasks[index+1]?.id)}</div></li>`).join('');
        return `<section class="outline-section" aria-labelledby="section-${c.id}">${sectionHeading(c)}<ul class="outline-list">${rows}${!rows && mode === 'check' ? '<li class="empty-category">Sin tareas para este día.</li>' : ''}</ul>${mode === 'edit' ? `<form class="section-add-form" data-add-category="${c.id}"><span class="add-placeholder" aria-hidden="true">＋</span><input data-section-input="${c.id}" aria-label="${escape('Nueva tarea en '+categoryName(c))}" placeholder="Escribe una tarea y presiona Enter…" value="${escape(newTaskDrafts.get(draftKey(c.id)) || '')}" maxlength="240" required><button type="submit" class="text-button" aria-label="${escape('Añadir tarea en '+categoryName(c))}">Añadir</button></form>` : ''}</section>`;
      }).join('');
    }
    $('list-count').textContent = `${tasks.length} ${tasks.length === 1 ? 'tarea' : 'tareas'} en la hoja`;
    document.querySelectorAll('[data-inline-task]').forEach(resizeText); controls();
  }
  function renderProgress() {
    const done = data.tasks.filter(t => t.done).length;
    $('progress-summary').textContent = `${done} de ${data.tasks.length} completadas`;
  }
  function render() {
    renderWeek(); renderCategories(); renderTasks(); renderProgress();
    $('schedule-info').hidden = !data.schedule;
    $('schedule-info').textContent = data.schedule?.message || '';
  }
  const shortDate = value => fromDate(value).toLocaleDateString('es-CO',{day:'numeric',month:'short'});
  const fullDate = value => fromDate(value).toLocaleDateString('es-CO',{weekday:'short',day:'numeric',month:'short',year:'numeric'});
  function complianceRange() {
    const preset = $('compliance-period').value;
    let from, to;
    if (preset === 'week') {
      from = addDays(date,-((fromDate(date).getDay()+6)%7)); to = addDays(from,6);
    } else if (preset === 'month') {
      const selected = fromDate(date);
      from = iso(new Date(selected.getFullYear(),selected.getMonth(),1,12));
      to = iso(new Date(selected.getFullYear(),selected.getMonth()+1,0,12));
    } else return;
    $('compliance-from').value = from < '2000-01-01' ? '2000-01-01' : from;
    $('compliance-to').value = to > '2100-12-31' ? '2100-12-31' : to;
  }
  function complianceProgress(item, label) {
    return item.rate === null ? '<span class="compliance-muted">Sin tareas</span>' : `<div class="compliance-progress"><progress max="100" value="${item.rate}" aria-label="${escape(label)}"></progress><span>${item.rate}%</span></div>`;
  }
  function renderCompliance(report) {
    const s = report.summary;
    const metric = (label,value,detail,cls='') => `<div class="compliance-metric ${cls}"><span>${label}</span><strong>${value}</strong><small>${detail}</small></div>`;
    const cards = metric('Cumplimiento',s.rate === null ? '—' : s.rate+'%',s.total ? `${s.done} de ${s.total} ${s.total === 1 ? 'tarea' : 'tareas'}` : 'Aún sin tareas en el período')+
      metric('Completadas',s.done,'Tareas marcadas como hechas','is-complete')+
      metric('Pendientes',s.pending,'Tareas aún sin completar')+
      metric('De días anteriores',s.overdue,`Pendientes antes del ${escape(shortDate(report.today))}`,'is-overdue');
    const dayRows = report.days.map(day => `<tr${day.date === report.today ? ' class="is-today"' : ''}><th scope="row"><button class="text-button" type="button" data-report-date="${day.date}" aria-label="${escape('Abrir plan del '+fullDate(day.date))}">${escape(fullDate(day.date))}${day.date === report.today ? ' · Hoy' : ''}</button></th><td>${day.total ? `${day.done} / ${day.total}` : '—'}</td><td>${complianceProgress(day,'Cumplimiento del '+fullDate(day.date))}</td></tr>`).join('');
    const categoryRows = report.categories.map(c => `<li><div class="compliance-category-title"><strong>${escape(c.name)}</strong><span>${c.done} / ${c.total}</span></div>${complianceProgress(c,'Cumplimiento de '+c.name)}${c.overdue ? `<small class="compliance-late">${c.overdue} de días anteriores</small>` : ''}</li>`).join('');
    const pending = report.pending.map(t => `<li><div><strong>${escape(t.title)}</strong><p>${escape(t.category)} · ${escape(fullDate(t.plan_date))}${t.task_time ? ' · '+escape(t.task_time) : ''}</p><div class="compliance-tags">${t.overdue ? '<span class="compliance-late">De un día anterior</span>' : ''}${t.priority === 'alta' ? '<span>Prioridad alta</span>' : ''}</div></div><div class="compliance-task-actions"><button class="button" type="button" data-report-edit="${t.id}" data-report-task-date="${t.plan_date}" data-mutation aria-label="${escape('Editar tarea: '+t.title)}">${icon('edit')} Editar</button><button class="button" type="button" data-report-date="${t.plan_date}" aria-label="${escape('Abrir día de '+t.title)}">Abrir día</button></div></li>`).join('');
    $('compliance-content').innerHTML = `<p class="compliance-range-caption">${escape(fullDate(report.from))} — ${escape(fullDate(report.to))}</p><div class="compliance-metrics">${cards}</div>${!s.total ? `<div class="compliance-empty"><h2>Tu avance empieza con una tarea</h2><p>Aún no hay tareas guardadas en este período. Abre un día, escribe tus pendientes y márcalos en Seguimiento al terminarlos.</p><button class="button" type="button" data-report-date="${date}">Abrir mi plan del día</button></div>` : `<div class="compliance-grid"><section class="compliance-panel"><h2>Avance por día</h2><div class="compliance-table-wrap"><table class="compliance-table"><thead><tr><th scope="col">Fecha</th><th scope="col">Hechas / total</th><th scope="col">Avance</th></tr></thead><tbody>${dayRows}</tbody></table></div></section><section class="compliance-panel"><h2>Por materia o categoría</h2><ul class="compliance-categories">${categoryRows}</ul></section></div><section class="compliance-panel compliance-pending"><h2>Lo que queda por hacer <span>${s.pending}</span></h2><p class="compliance-muted">${s.pending > report.pending_limit ? `Mostrando las primeras ${report.pending_limit} de ${s.pending} pendientes, empezando por las fechas más antiguas.` : 'Primero las fechas más antiguas; abre un día para marcar sus tareas.'}</p>${pending ? `<ul>${pending}</ul>` : '<p class="compliance-finished">Todas las tareas de este período están completadas.</p>'}</section>`}`;
  }
  async function loadCompliance() {
    if (busy) return;
    const from = $('compliance-from').value, to = $('compliance-to').value;
    $('compliance-error').hidden = true;
    if (!allowedDate(from) || !allowedDate(to) || from > to) {
      $('compliance-content').hidden = true; $('compliance-status').textContent = '';
      $('compliance-error').textContent = 'Revisa las fechas: Desde debe ser anterior o igual a Hasta.'; $('compliance-error').hidden = false; return;
    }
    busy = true; complianceBusy = true; controls();
    $('compliance-content').hidden = true; $('compliance-content').setAttribute('aria-busy','true'); $('compliance-status').textContent = 'Consultando tu avance…';
    try {
      const report = await request('GET',null,date,{from,to});
      renderCompliance(report); $('compliance-content').hidden = false;
      $('compliance-status').textContent = 'Actualizado con tus tareas guardadas.';
    } catch (error) {
      $('compliance-status').textContent = ''; $('compliance-error').textContent = error.message; $('compliance-error').hidden = false;
    } finally { busy = false; complianceBusy = false; $('compliance-content').setAttribute('aria-busy','false'); controls(); }
  }
  async function changeView(nextMode) {
    if (!data || busy) return;
    try {
      if (nextMode === 'monitor' ? !(await saveAll()) : !(await saveDocument())) return;
      mode = nextMode; renderTasks();
      if (mode === 'monitor') { complianceRange(); await loadCompliance(); }
    } catch (error) { showError(error); }
  }
  async function openTask(id) {
    if (!data || busy) return;
    try { if (!(await saveDocument())) return; } catch (error) { showError(error); return; }
    const task = id ? data.tasks.find(t => t.id === id) : null, form = $('task-form');
    if (id && !task) { toast('Esta tarea ya no está disponible. Actualiza la lista e intenta nuevamente.'); return; }
    form.reset(); form.querySelector('.form-error').textContent = '';
    form.elements.category_id.innerHTML = categoryOptions(task?.category_id || (category === 'all' ? data.categories[0].id : category));
    for (const name of ['id','title','description','task_time']) form.elements[name].value = task?.[name] || '';
    form.elements.task_date.value = task?.plan_date || date; form.elements.priority.value = task?.priority || 'normal'; form.elements.highlight.value = task?.highlight || 'green';
    $('task-dialog-title').textContent = task ? 'Editar tarea' : 'Nueva tarea';
    form.querySelector('button[type="submit"]').textContent = task ? 'Guardar cambios' : 'Guardar tarea';
    $('task-edit-status').hidden = !task; $('task-edit-status').textContent = task ? (task.done ? 'Estado: completada' : 'Estado: pendiente') : '';
    $('task-dialog').showModal(); form.elements.title.focus();
  }
  function categoryEditor() {
    $('category-editor').innerHTML = data.categories.map(c => c.managed ? `<div class="category-managed-row"><span>${escape(c.name)}</span><small>${c.scheduled ? 'Del horario' : 'Automática'}</small></div>` : `<form class="category-edit-row" data-category-form="${c.id}"><input name="name" aria-label="${escape('Nombre de '+c.name)}" value="${escape(c.name)}" maxlength="100" required><select name="color" aria-label="${escape('Color de '+c.name)}">${colorOptions(c.color)}</select><button type="submit" class="icon-button" title="Guardar título" aria-label="${escape('Guardar '+c.name)}">${icon('check')}</button><button type="button" class="icon-button" data-delete-category="${c.id}" aria-label="${escape('Eliminar '+c.name)}"${data.categories.length < 2 ? ' disabled' : ''}>${icon('trash')}</button></form>`).join('');
    $('categories-dialog').querySelector('.dialog-intro').textContent = data.schedule ? 'Las materias se cargan desde tu horario y Personal está disponible todos los días. También puedes añadir un título adicional para la fecha seleccionada.' : 'Cada categoría es un título de tu hoja y una pestaña. Se usa en todos tus días.';
  }
  async function openCategories(id = null) {
    if (!data || busy) return;
    try { if (!(await saveDocument())) return; } catch (error) { showError(error); return; }
    categoryEditor(); $('categories-dialog').querySelector('.form-error').textContent = ''; $('categories-dialog').showModal();
    if (id) { const input = document.querySelector(`[data-category-form="${id}"] input[name="name"]`); if (input) { input.focus(); input.select(); } }
  }
  async function formAction(form, action, values, after) {
    form.closest('dialog').querySelector('.form-error').textContent = '';
    try { const next = await mutate(action,values); if (next && after) await after(next); }
    catch (error) { form.closest('dialog').querySelector('.form-error').textContent = error.message; }
  }
  document.addEventListener('click',async event => {
    const close = event.target.closest('[data-close]'); if (close) { if (!busy) $(close.dataset.close).close(); return; }
    const target = event.target.closest('button'); if (!target || busy || target.disabled) return;
    if (target.dataset.reportEdit) {
      const targetDate = target.dataset.reportTaskDate;
      if (allowedDate(targetDate) && await load(targetDate)) await openTask(Number(target.dataset.reportEdit));
      return;
    }
    if (target.dataset.reportDate) {
      const targetDate = target.dataset.reportDate;
      if (allowedDate(targetDate) && await load(targetDate)) {
        category = 'all'; $('search').value = ''; $('filter').value = 'all';
        mode = 'check'; renderCategories(); renderTasks(); $('tasks-heading').scrollIntoView({block:'start'});
      }
      return;
    }
    if (target.dataset.date) { await navigate(target.dataset.date); return; }
    if (target.dataset.category) { category = target.dataset.category === 'all' ? 'all' : Number(target.dataset.category); renderCategories(); renderTasks(); return; }
    if (target.id === 'tabs-manage') { await openCategories(); return; }
    if (target.dataset.sectionManage) { await openCategories(Number(target.dataset.sectionManage)); return; }
    if (target.id === 'clear-filters') { category = 'all'; $('search').value = ''; $('filter').value = 'all'; render(); return; }
    if (target.dataset.edit) { await openTask(Number(target.dataset.edit)); return; }
    if (target.dataset.swap && target.dataset.other) { try { await mutate('swap',{id:Number(target.dataset.swap),other_id:Number(target.dataset.other)}); } catch(error) { toast(error.message); } return; }
    if (target.dataset.delete) {
      const task = data.tasks.find(t => t.id === Number(target.dataset.delete));
      $('confirm-title').textContent = 'Eliminar tarea'; $('confirm-message').textContent = `Se eliminará «${task.title}» de este día. Esta acción no se puede deshacer.`; $('target-label').hidden = true;
      confirmAction = () => ({action:'delete_task',values:{id:task.id}});
    } else if (target.dataset.deleteCategory) {
      const c = data.categories.find(c => c.id === Number(target.dataset.deleteCategory));
      $('confirm-title').textContent = 'Eliminar título'; $('confirm-message').textContent = `Las tareas de «${c.name}», de todas las fechas, se conservarán en la categoría que elijas.`;
      $('target-label').hidden = false; $('target-category').innerHTML = categoryOptions(null,c.id);
      confirmAction = () => ({action:'delete_category',values:{id:c.id,target_id:Number($('target-category').value)}});
    } else return;
    $('confirm-dialog').querySelector('.form-error').textContent = ''; $('confirm-dialog').showModal();
  });
  $('task-list').addEventListener('input',event => {
    const input = event.target;
    if (input.dataset.inlineTask) {
      const id = Number(input.dataset.inlineTask), original = data.tasks.find(t => t.id === id);
      if (input.value === original.title) taskEdits.delete(id); else taskEdits.set(id,input.value);
      input.closest('.outline-row').classList.toggle('has-edit',taskEdits.has(id)); resizeText(input);
    } else if (input.dataset.inlineCategory) {
      const id = Number(input.dataset.inlineCategory), original = data.categories.find(c => c.id === id);
      if (input.value === original.name) categoryEdits.delete(id); else categoryEdits.set(id,input.value);
      input.classList.toggle('has-edit',categoryEdits.has(id)); renderCategories();
    } else if (input.dataset.sectionInput) {
      const key = draftKey(Number(input.dataset.sectionInput)); if (input.value) newTaskDrafts.set(key,input.value); else newTaskDrafts.delete(key);
    }
    controls(); if (!busy) setStatus('Todo guardado');
  });
  $('task-list').addEventListener('keydown',event => {
    if (event.key !== 'Enter' || event.shiftKey || (!event.target.dataset.inlineTask && !event.target.dataset.inlineCategory)) return;
    event.preventDefault();
    const section = event.target.closest('.outline-section'), fields = [...section.querySelectorAll('[data-inline-task], [data-section-input]')];
    const index = fields.indexOf(event.target), next = fields[index+1] || fields[0]; if (next) { next.focus(); if (next.select) next.select(); }
  });
  $('task-list').addEventListener('submit',async event => {
    const form = event.target.closest('[data-add-category]'); if (!form) return; event.preventDefault(); if (busy || !data) return;
    const id = Number(form.dataset.addCategory), key = draftKey(id), title = form.querySelector('input').value.trim(); if (!title) return;
    try {
      if (!(await saveDocument())) return;
      const next = await mutate('save_task',{title,category_id:id,task_date:date}); if (!next) return;
      newTaskDrafts.delete(key); renderTasks(); document.querySelector(`[data-section-input="${id}"]`)?.focus();
    } catch (error) { showError(error); }
  });
  $('task-list').addEventListener('change',async event => {
    const id = event.target.dataset.complete; if (!id || busy || mode !== 'check') return;
    try { await mutate('complete',{id:Number(id),done:event.target.checked}); } catch (error) { renderTasks(); toast(error.message); }
  });
  $('new-task').addEventListener('click',() => openTask());
  $('document-save').addEventListener('click',async () => { try { if (await saveAll()) toast('Cambios guardados.'); } catch (error) { showError(error); } });
  $('document-reset').addEventListener('click',() => { if (busy) return; taskEdits.clear(); categoryEdits.clear(); $('error-banner').hidden = true; renderCategories(); renderTasks(); setStatus('Todo guardado'); });
  $('view-check').addEventListener('click',() => changeView('check'));
  $('view-edit').addEventListener('click',() => changeView('edit'));
  $('view-monitor').addEventListener('click',() => changeView('monitor'));
  $('compliance-form').addEventListener('submit',event => { event.preventDefault(); loadCompliance(); });
  $('compliance-period').addEventListener('change',() => { if ($('compliance-period').value !== 'custom') { complianceRange(); loadCompliance(); } });
  for (const id of ['compliance-from','compliance-to']) $(id).addEventListener('input',() => {
    $('compliance-period').value = 'custom'; $('compliance-content').hidden = true; $('compliance-error').hidden = true;
    $('compliance-status').textContent = 'Fechas cambiadas. Pulsa Actualizar para consultar ese período.';
  });
  $('search').addEventListener('input',() => { if (data) renderTasks(); }); $('filter').addEventListener('change',() => { if (data) renderTasks(); });
  $('date-picker').addEventListener('change',event => { const target = event.target.value; event.target.value = date; navigate(target); });
  $('today').addEventListener('click',() => navigate(today)); $('prev-week').addEventListener('click',() => navigate(addDays(date,-7))); $('next-week').addEventListener('click',() => navigate(addDays(date,7)));
  $('nav-categories').addEventListener('click',() => openCategories());
  $('side-add').addEventListener('click',async () => { await openCategories(); if ($('categories-dialog').open) $('category-name').focus(); });
  $('retry').addEventListener('click',async () => { if (hasEdits() || focusDirty) { try { await saveAll(); } catch(error) { showError(error); } } else await load(); });
  $('focus').addEventListener('input',() => { focusDirty = true; $('focus-status').textContent = 'Nota sin guardar'; controls(); });
  $('focus-save').addEventListener('click',async () => { try { await saveFocus(); toast('Nota guardada.'); } catch (error) { showError(error); } });
  $('task-form').addEventListener('submit',event => {
    event.preventDefault(); const form = event.currentTarget, values = Object.fromEntries(new FormData(form)); values.category_id = Number(values.category_id);
    if (values.id) values.id = Number(values.id); else delete values.id;
    formAction(form,'save_task',values,async () => {
      $('task-dialog').close(); toast(values.id ? 'Cambios de la tarea guardados.' : 'Tarea guardada.');
      if (mode === 'monitor') await loadCompliance();
    });
  });
  $('category-form').addEventListener('submit',event => {
    event.preventDefault(); const form = event.currentTarget;
    const previousIds = new Set(data.categories.map(c => c.id));
    formAction(form,'save_category',Object.fromEntries(new FormData(form)),next => { form.reset(); $('categories-dialog').close(); mode = 'edit'; category = 'all'; render(); const c = next.categories.find(c => !c.managed && !previousIds.has(c.id)); if (c) document.querySelector(`[data-section-input="${c.id}"]`)?.focus(); toast('Título creado. Escribe tus tareas debajo.'); });
  });
  $('category-editor').addEventListener('submit',event => { event.preventDefault(); const form = event.target; formAction(form,'save_category',{...Object.fromEntries(new FormData(form)),id:Number(form.dataset.categoryForm)},() => { categoryEditor(); toast('Título guardado.'); }); });
  $('copy-open').addEventListener('click',async () => {
    if (!data || busy) return; try { if (!(await saveDocument())) return; } catch(error) { showError(error); return; }
    $('copy-form').reset(); $('copy-form').elements.source.value = addDays(date,-1); $('copy-dialog').querySelector('.form-error').textContent = ''; $('copy-dialog').showModal();
  });
  $('copy-form').addEventListener('submit',event => {
    event.preventDefault(); const form = event.currentTarget, count = data.tasks.length;
    formAction(form,'copy',{source:form.elements.source.value,pending_only:form.elements.scope.value === 'pending'},next => { $('copy-dialog').close(); const added = next.tasks.length-count; toast(added ? `${added} ${added === 1 ? 'tarea añadida' : 'tareas añadidas'} a tu día.` : 'No hay tareas nuevas para traer de esa fecha.'); });
  });
  $('confirm-form').addEventListener('submit',event => {
    event.preventDefault(); if (!confirmAction) return; const action = confirmAction();
    formAction(event.currentTarget,action.action,action.values,() => { if (action.action === 'delete_task') taskEdits.delete(action.values.id); $('confirm-dialog').close(); if ($('categories-dialog').open) categoryEditor(); renderCategories(); renderTasks(); setStatus('Todo guardado'); toast(action.action === 'delete_task' ? 'Tarea eliminada.' : 'Título eliminado. Tus tareas se conservaron.'); });
  });
  document.addEventListener('keydown',event => { if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') { event.preventDefault(); if (!busy && data) saveAll().then(saved => { if (saved) toast('Cambios guardados.'); }).catch(showError); } });
  window.addEventListener('beforeunload',event => { if (hasEdits() || focusDirty || (busy && !complianceBusy) || [...newTaskDrafts.values()].some(v => v.trim())) { event.preventDefault(); event.returnValue = ''; } });
  document.querySelectorAll('dialog').forEach(dialog => dialog.addEventListener('cancel',event => { if (busy) event.preventDefault(); }));
  controls(); load();
})();
