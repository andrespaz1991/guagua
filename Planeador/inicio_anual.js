(() => {
  'use strict';
  const $ = id => document.getElementById(id);
  const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const steps = [
    ['El punto de partida','Año e institución','Revisa la institución seleccionada y decide si quieres usar la estructura de otro año.'],
    ['El calendario escolar','Períodos académicos','Define las fechas y el período con el que empezará el año.'],
    ['La oferta académica','Grados y asignaturas','Elige los elementos del catálogo o agrega los que necesitas para este año.'],
    ['El equipo y sus cursos','Asignaciones docentes','Relaciona cada grado y asignatura con su docente.'],
    ['El ritmo de la semana','Horarios','Programa los bloques de clase. También puedes completar los horarios después de instalar.'],
    ['Cada estudiante en su lugar','Inscripciones','Selecciona estudiantes y su grado de destino. Se inscribirán en las asignaturas que preparaste para ese grado.'],
    ['Todo conectado','Archivos y opciones','Revisa las rutas de asistencia, los reportes y las opciones institucionales.'],
    ['Un último vistazo','Revisión e instalación','Comprueba el resumen y decide cuándo activar el nuevo año escolar.']
  ];
  const state = {catalog:null, files:null, draft:null, step:0, busy:false, dirty:false};
  const key = prefix => `${prefix}_${Date.now()}_${Math.random().toString(36).slice(2,8)}`;
  const plan = () => state.draft.plan;
  const option = (value,label,current) => `<option value="${esc(value)}" ${String(value)===String(current)?'selected':''}>${esc(label)}</option>`;
  const field = (path,label,value,type='text',attrs='') => `<label class="field">${esc(label)}<input type="${type}" data-path="${esc(path)}" value="${esc(value)}" ${attrs}></label>`;
  const select = (path,label,value,rows) => `<label class="field">${esc(label)}<select data-path="${esc(path)}">${option('','Selecciona…',value)}${rows.map(r=>option(r.id,r.nombre,value)).join('')}</select></label>`;
  const remove = (type,index,label='Quitar') => `<button type="button" class="text-button danger" data-action="remove" data-list="${type}" data-index="${index}" aria-label="${esc(label)}">Quitar <span aria-hidden="true">×</span></button>`;
  const note = text => `<p class="form-note">${esc(text)}</p>`;
  const empty = text => `<div class="inline-empty">${esc(text)}</div>`;
  const find = (items,id) => items.find(item=>item.key===id);
  const courseName = a => `${find(plan().grados,a.grado)?.nombre || 'Grado pendiente'} · ${find(plan().materias,a.materia)?.nombre || 'Asignatura pendiente'} · ${state.catalog.docentes.find(d=>String(d.id)===String(a.docente))?.nombre || 'Docente pendiente'}`;
  const readonly = () => state.draft?.estado==='instalado';

  async function api(action,data=null) {
    const url = new URL(location.href);url.searchParams.set('action',action);
    const opts={cache:'no-store',headers:{Accept:'application/json'}};
    if (['bootstrap','load','copy','files'].includes(action)) {
      for (const [name,value] of Object.entries(data || {})) url.searchParams.set(name,value);
    } else {
      opts.method='POST';opts.headers['Content-Type']='application/json';
      opts.headers['X-CSRF-Token']=document.querySelector('meta[name="csrf-token"]').content;
      opts.body=JSON.stringify(data);
    }
    const response=await fetch(url,opts);
    let result;
    try { result=await response.json(); } catch { throw Error('La sesión o la respuesta no están disponibles. Actualiza la página para continuar.'); }
    if (!response.ok || !result.ok) throw Error(result.message || 'No fue posible completar la operación.');
    return result;
  }
  function message(text,type='info') {
    const box=$('annual-message');box.className=`annual-message ${type}`;
    if (Array.isArray(text)) box.innerHTML=`<strong>Revisa estos puntos antes de continuar:</strong><ul>${text.map(t=>`<li>${esc(t)}</li>`).join('')}</ul>`;
    else box.textContent=text;
    box.hidden=!text || (Array.isArray(text)&&!text.length);
    if (type==='error') {box.setAttribute('tabindex','-1');box.focus();}
  }
  function actions() {
    $('annual-app').setAttribute('aria-busy',String(state.busy));
    $('open-year').disabled=state.busy;
    $('previous-step').disabled=state.busy || state.step===0;
    $('save-draft').hidden=readonly();$('next-step').hidden=state.step===7 || readonly();
    $('save-draft').disabled=state.busy;$('next-step').disabled=state.busy;
    $('save-status').textContent=state.busy?'Guardando…':readonly()?'Instalación terminada':state.dirty?'Cambios por guardar':state.draft?.revision===0?'Aún sin guardar':'Borrador guardado';
  }
  async function run(work) {
    if (state.busy) return;
    state.busy=true;actions();
    try { await work(); } catch (error) {message(error.message,'error');}
    finally {state.busy=false;actions();}
  }
  function nav() {
    $('step-count').textContent=`${state.step+1} de ${steps.length}`;
    $('step-progress').style.width=`${(state.step+1)/steps.length*100}%`;
    $('annual-steps').innerHTML=steps.map((s,i)=>`<button type="button" data-step="${i}" ${i===state.step?'aria-current="step"':''}><span>${String(i+1).padStart(2,'0')}</span><div>${esc(s[1])}<small>${i===state.step?'Estás aquí':i<state.step?'Volver a este paso':'Por completar'}</small></div>${i===state.step?'<b aria-hidden="true">→</b>':''}</button>`).join('');
  }
  function render() {
    if (!state.draft) return;
    const p=plan(), s=steps[state.step];
    $('annual-empty').hidden=true;$('annual-workspace').hidden=false;
    $('step-eyebrow').textContent=`PASO ${String(state.step+1).padStart(2,'0')} / ${s[0].toUpperCase()}`;
    $('step-title').textContent=s[1];$('step-description').textContent=s[2];
    $('draft-badge').textContent=readonly()?`Año ${p.ano} instalado`:state.draft.estado==='preparado'?`Preparado · ${p.ano}`:`Borrador · ${p.ano}`;
    let html='';
    if (state.step===0) {
      html=`<div class="callout"><strong>Preparando el año ${esc(p.ano)}</strong><p>${esc(state.catalog.institucion.nombre_institucion)} · ${esc(state.catalog.docentes.find(d=>String(d.id)===state.catalog.usuario)?.nombre || 'Sesión de gestión académica')}</p></div>
      ${field('descripcion','Descripción del año',p.descripcion,'text','placeholder="Calendario, jornada o una nota para este ciclo"')}
      <div class="section-divider"><h3>Usar una estructura existente</h3><p>Trae períodos, grados, asignaturas, docentes y horarios del año elegido. Revisa sus fechas; los estudiantes se seleccionan en su propio paso.</p><div class="inline-controls"><label class="field grow">Año de referencia<select id="base-year">${option('','Selecciona un año','')}${state.catalog.anos.filter(a=>Number(a.nombre_ano_lectivo)!==Number(p.ano)).map(a=>option(a.id_ano_lectivo,`${a.nombre_ano_lectivo}${a.estado==='Activo'?' · En uso':''}`,'')).join('')}</select></label><button class="button secondary" type="button" data-action="copy">Usar esta estructura</button></div></div>
      <div class="section-divider"><h3>Datos de la institución</h3><div class="field-grid">${field('institucion.nombre_institucion','Nombre',p.institucion.nombre_institucion)}${field('institucion.telefono_institucion','Teléfono',p.institucion.telefono_institucion)}${field('institucion.direccion_institucion','Dirección',p.institucion.direccion_institucion)}${field('institucion.correo_institucion','Correo',p.institucion.correo_institucion,'email')}</div>${note('Los cambios institucionales se aplican cuando instales y actives el año. Las opciones son compartidas por esta institución.')}</div>`;
    }
    if (state.step===1) {
      html=`<div class="section-toolbar"><h3>${p.periodos.length} períodos preparados</h3><button type="button" class="button secondary" data-action="add-period">+ Agregar período</button></div>${p.periodos.length?'':empty('Agrega los períodos que utiliza tu institución. Tú defines cuántos son y sus fechas.')}
      <div class="repeat-list">${p.periodos.map((item,i)=>`<article class="repeat-card"><div class="row-heading"><span class="number-label">PERÍODO ${i+1}</span>${remove('periodos',i,'Quitar período '+(i+1))}</div><div class="field-grid three">${field(`periodos.${i}.nombre`,'Número del período',item.nombre,'text','inputmode="numeric" placeholder="Ej.: 1"')}${field(`periodos.${i}.inicio`,'Fecha inicial',item.inicio,'date',`min="${p.ano}-01-01" max="${p.ano}-12-31"`)}${field(`periodos.${i}.fin`,'Fecha final',item.fin,'date',`min="${p.ano}-01-01" max="${p.ano}-12-31"`)}</div>${field(`periodos.${i}.descripcion`,'Descripción o proyecto transversal',item.descripcion)}<label class="check-line"><input type="radio" name="active-period" data-period="${i}" ${item.activo?'checked':''}> Comenzar el año con este período activo</label></article>`).join('')}</div>${note('Las fechas deben pertenecer al año de trabajo y cada período debe tener su propio rango.')}`;
    }
    if (state.step===2) {
      html=`<div class="catalog-grid">${['grados','materias'].map(type=>`<section><div class="section-toolbar"><h3>${type==='grados'?'Grados':'Asignaturas'}</h3><span class="count-bubble">${p[type].length}</span></div><div class="catalog-list">${state.catalog[type].map(item=>`<label class="catalog-item"><input type="checkbox" data-catalog="${type}" data-id="${esc(item.id)}" ${p[type].some(x=>Number(x.id)===Number(item.id))?'checked':''}><span>${esc(item.nombre)}</span></label>`).join('') || empty('El catálogo está vacío. Agrega el primer elemento.')}</div>${p[type].filter(item=>!item.id).map(item=>`<div class="new-catalog-item"><span>${esc(item.nombre)} <small>Nuevo</small></span><button type="button" class="text-button danger" data-action="remove-catalog" data-list="${type}" data-key="${esc(item.key)}">Quitar</button></div>`).join('')}
      <div class="catalog-add"><label class="field">${type==='grados'?'Nombre del nuevo grado':'Nombre de la nueva asignatura'}<input id="new-${type}" placeholder="${type==='grados'?'Ej.: Sexto B':'Ej.: Robótica'}"></label>${type==='grados'?`<label class="field">Usar el nivel educativo de<select id="new-grade-level">${option('','Selecciona un grado de referencia','')}${state.catalog.grados.map(g=>option(g.nivel,g.nombre,'')).join('')}</select></label>`:''}<button type="button" class="button secondary" data-action="add-catalog" data-list="${type}">+ Agregar ${type==='grados'?'grado':'asignatura'}</button></div></section>`).join('')}</div>`;
    }
    if (state.step===3) {
      const teacherValue=state.catalog.docentes.some(d=>String(d.id)===state.catalog.usuario)?state.catalog.usuario:'';
      const teachers=state.catalog.puede_activar?state.catalog.docentes:state.catalog.docentes.filter(d=>String(d.id)===state.catalog.usuario);
      html=`<div class="builder"><h3>Armar las asignaciones</h3><p>Selecciona los grados y las asignaturas que dictará un docente. Puedes repetir este paso para cada integrante del equipo.</p><div class="field-grid">${select('','Docente',teacherValue,teachers).replace('data-path=""','id="assignment-teacher"')}<div></div><label class="field">Grados<select multiple id="assignment-grades" size="${Math.max(3,Math.min(6,p.grados.length))}">${p.grados.map(g=>option(g.key,g.nombre,'')).join('')}</select></label><label class="field">Asignaturas<select multiple id="assignment-subjects" size="${Math.max(3,Math.min(6,p.materias.length))}">${p.materias.map(m=>option(m.key,m.nombre,'')).join('')}</select></label></div><button type="button" class="button secondary" data-action="add-assignments">+ Agregar combinaciones seleccionadas</button></div>
      <div class="section-divider"><div class="section-toolbar"><h3>Asignaciones preparadas</h3><span class="count-bubble">${p.asignaciones.length}</span></div>${p.asignaciones.length?`<div class="table-wrap"><table><thead><tr><th>Grado y asignatura</th><th>Docente</th><th>Disponible</th><th></th></tr></thead><tbody>${p.asignaciones.map((a,i)=>`<tr><td><strong>${esc(find(p.grados,a.grado)?.nombre || 'Grado quitado')}</strong><span class="cell-secondary">${esc(find(p.materias,a.materia)?.nombre || 'Asignatura quitada')}</span></td><td>${select(`asignaciones.${i}.docente`,'Docente',a.docente,teachers)}</td><td><label class="check-line"><input type="checkbox" data-path="asignaciones.${i}.visible" ${a.visible?'checked':''}> Visible</label></td><td>${remove('asignaciones',i,'Quitar asignación')}</td></tr>`).join('')}</tbody></table></div>`:empty('Aún no has agregado asignaciones.')}</div>`;
    }
    if (state.step===4) {
      html=`<div class="section-toolbar"><h3>Bloques de clase <span class="count-bubble">${p.horarios.length}</span></h3><button type="button" class="button secondary" data-action="add-schedule">+ Agregar bloque</button></div>${p.horarios.length?'':empty('Puedes agregar los horarios ahora o instalarlos después desde el módulo Horarios.')}
      <div class="repeat-list">${p.horarios.map((h,i)=>`<article class="repeat-card"><div class="row-heading"><span class="number-label">BLOQUE ${i+1}</span>${remove('horarios',i,'Quitar bloque de horario')}</div>${select(`horarios.${i}.asignacion`,'Asignación',h.asignacion,p.asignaciones.map(a=>({id:a.key,nombre:courseName(a)})))}<div class="field-grid three">${select(`horarios.${i}.dia`,'Día',h.dia,['lunes','martes','miercoles','jueves','viernes','sabado','domingo'].map((d,index)=>({id:d,nombre:['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'][index]})))}${field(`horarios.${i}.inicio`,'Desde',h.inicio,'time')}${field(`horarios.${i}.fin`,'Hasta',h.fin,'time')}</div><div class="field-grid">${field(`horarios.${i}.desde`,'Vigente desde',h.desde,'date')}${field(`horarios.${i}.hasta`,'Vigente hasta',h.hasta,'date')}</div></article>`).join('')}</div>`;
    }
    if (state.step===5) {
      html=`<div class="callout"><strong>El grado de destino lo eliges tú.</strong><p>Seleccionar un estudiante creará sus inscripciones en los cursos de ese grado. Las inscripciones históricas y el perfil del estudiante se conservan.</p></div><div class="field-grid">${field('fecha_inscripcion','Fecha de inscripción',p.fecha_inscripcion,'date')}<label class="field">Grado al seleccionar estudiantes<select id="enrollment-grade">${option('','Elige el grado de destino','')}${p.grados.map(g=>option(g.key,g.nombre,'')).join('')}</select></label></div><div class="section-toolbar"><h3><span id="enrollment-count">${p.estudiantes.length}</span> estudiantes seleccionados</h3><label class="field compact">Buscar estudiante<input type="search" id="student-search" placeholder="Nombre o documento"></label></div><div class="table-wrap" id="students-list"></div>${note('Este paso es opcional. Puedes importar o inscribir más estudiantes después de instalar el año.')}`;
    }
    if (state.step===6) {
      html=`<div class="section-toolbar"><h3>Archivos del año ${p.ano}</h3><button type="button" class="text-button" data-action="refresh-files">Leer rutas guardadas</button></div>
      ${field('archivos.asistencia','Archivo de asistencia (ruta completa)',p.archivos.asistencia,'text',p.archivos.asistencia_entorno?'readonly':'')}${p.archivos.asistencia_entorno?note('Esta ruta está definida en la configuración del servidor.'):note('Guagua utilizará esta ruta para leer la asistencia al activar el año.')}
      ${(p.archivos.reportes || []).map((r,i)=>`<div class="section-divider"><h3>Reporte · ${esc(r.nombre)}</h3>${field(`archivos.reportes.${i}.local`,'Ruta del archivo local',r.local)}${field(`archivos.reportes.${i}.web`,'Enlace del archivo en la web (opcional)',r.web,'url')}</div>`).join('') || note('Todavía no hay grupos de reportes. Puedes crearlos en Configuración de reportes IA.')}
      <div class="section-divider"><h3>Opciones de la institución</h3><div class="field-grid">${select('institucion.autoregistrarse','Permitir autorregistro',p.institucion.autoregistrarse,[{id:'SI',nombre:'Sí'},{id:'NO',nombre:'No'}])}${select('institucion.plantilla','Plantilla de la institución',p.institucion.plantilla,state.catalog.plantillas || [{id:1,nombre:'Plantilla principal de Guagua'}])}${field('institucion.formatos_no_permitidos','Extensiones de archivo no permitidas',p.institucion.formatos_no_permitidos,'text','placeholder="Ej.: exe, bat"')}${field('institucion.tamano_maximo_adjunto','Tamaño máximo de adjuntos (valor institucional)',p.institucion.tamano_maximo_adjunto)}</div>${note('Las rutas y estas opciones se aplican con la activación final. Si instalas sin activar, quedan preparadas en este borrador.')}</div>`;
    }
    if (state.step===7) html=review();
    $('step-content').innerHTML=`<fieldset ${readonly()?'disabled':''}>${html}</fieldset>`;
    if (state.step===5) students();
    if (state.step===7 && !readonly()) $('activate-year')?.addEventListener('change',()=>{$('install-year').textContent=$('activate-year').checked?'Instalar y activar año':'Instalar año sin activar';});
    nav();actions();
  }
  function students() {
    const term=($('student-search')?.value || '').toLocaleLowerCase('es');
    const data=state.catalog.estudiantes.filter(e=>`${e.nombre} ${e.id}`.toLocaleLowerCase('es').includes(term));
    $('students-list').innerHTML=`<table><thead><tr><th>Seleccionar</th><th>Estudiante</th><th>Grado en el perfil</th><th>Grado de destino</th></tr></thead><tbody>${data.map(e=>{const i=plan().estudiantes.findIndex(s=>String(s.id)===String(e.id));return `<tr><td><input type="checkbox" aria-label="Inscribir a ${esc(e.nombre)}" data-student="${esc(e.id)}" ${i>=0?'checked':''} ${readonly()?'disabled':''}></td><td><strong>${esc(e.nombre)}</strong><span class="cell-secondary">${esc(e.id)}</span></td><td>${esc(e.grado || 'Sin indicar')}</td><td>${i>=0?select(`estudiantes.${i}.grado`,'Grado de destino',plan().estudiantes[i].grado,plan().grados.map(g=>({id:g.key,nombre:g.nombre}))):'—'}</td></tr>`;}).join('') || '<tr><td colspan="4">No hay estudiantes que coincidan.</td></tr>'}</tbody></table>`;
    $('enrollment-count').textContent=plan().estudiantes.length;
  }
  function review() {
    const p=plan(), result=state.draft.resultado;
    const active=p.periodos.find(x=>x.activo), enrollments=p.estudiantes.reduce((total,e)=>total+p.asignaciones.filter(a=>a.grado===e.grado).length,0);
    const metrics=[['Períodos',p.periodos.length],['Grados',p.grados.length],['Asignaturas',p.materias.length],['Asignaciones',p.asignaciones.length],['Bloques de clase',p.horarios.length],['Estudiantes',p.estudiantes.length]];
    return `${result?`<div class="success-banner"><span aria-hidden="true">✓</span><div><h3>Año ${esc(p.ano)} instalado${result.activado?' y activo':''}.</h3><p>Se crearon ${result.asignaciones} asignaciones, ${result.horarios} bloques y ${result.inscripciones} inscripciones nuevas.</p></div></div>`:''}
    <div class="review-year"><div><p class="eyebrow">AÑO ESCOLAR</p><strong>${esc(p.ano)}</strong></div><p>${esc(p.institucion.nombre_institucion)}<br><span>Período inicial: ${esc(active?.nombre || 'Pendiente')}</span></p></div>
    <div class="review-metrics">${metrics.map(([label,n])=>`<div><strong>${n}</strong><span>${label}</span></div>`).join('')}</div>
    <div class="review-section"><h3>Calendario preparado</h3>${p.periodos.length?`<div class="table-wrap"><table><thead><tr><th>Período</th><th>Inicio</th><th>Fin</th><th></th></tr></thead><tbody>${p.periodos.map(x=>`<tr><td>${esc(x.nombre)}</td><td>${esc(x.inicio)}</td><td>${esc(x.fin)}</td><td>${x.activo?'<span class="tag">Inicial</span>':''}</td></tr>`).join('')}</tbody></table></div>`:empty('Falta configurar los períodos.')}</div>
    <div class="review-section"><h3>Qué se instalará</h3><ul class="review-checks"><li>Año lectivo y períodos con las fechas que revisaste.</li><li>Grados y asignaturas nuevos; las coincidencias existentes se reutilizan.</li><li>Asignaciones y horarios para esta institución y año.</li><li>${enrollments} inscripciones previstas para los estudiantes seleccionados.</li><li>La identidad institucional y las rutas se aplican al activar el año.</li></ul></div>
    ${(!p.horarios.length || !p.estudiantes.length)?`<div class="callout warm"><strong>Elementos para completar después</strong><p>${!p.horarios.length?'Horarios de clase. ':''}${!p.estudiantes.length?'Inscripciones de estudiantes.':''}</p></div>`:''}
    ${!readonly()?`<div class="install-panel"><h3>${result?'Puedes continuar preparando o activar el año':'¿Listo para instalar?'}</h3><button type="button" class="button secondary" data-action="validate">Comprobar todos los pasos</button>${state.catalog.puede_activar?'<label class="check-line activation"><input type="checkbox" id="activate-year"> Activar este año y su período inicial al terminar</label><p>La activación cambia el año y el período vigentes de toda la plataforma.</p>':note('Con perfil docente puedes instalar tus cursos sin activar el año. La activación general la realiza un administrador o directivo.')}
    <div class="inline-controls">${field('','Escribe el año para confirmar','','text',`id="confirm-year" inputmode="numeric" placeholder="${p.ano}"`).replace('data-path=""','')}<button type="button" class="button primary" id="install-year" data-action="install">Instalar año sin activar</button></div></div>`:''}
    <div class="next-links"><h3>Continuar con la gestión académica</h3><a href="admin_malla.php" target="_blank" rel="noopener">Malla curricular ↗</a><a href="inscripes.php" target="_blank" rel="noopener">Inscripciones ↗</a><a href="calendario.php" target="_blank" rel="noopener">Calendario de planes ↗</a><a href="../ia/config_reporte.php" target="_blank" rel="noopener">Reportes ↗</a></div>`;
  }
  function dirty() {state.dirty=true;actions();}
  async function save(next=false,validateAll=false) {
    if (readonly()) return;
    const target=next?Math.min(7,state.step+1):state.step;
    const result=await api('save',{plan:plan(),revision:state.draft.revision,paso:target,validar:next||validateAll,validar_hasta:validateAll?7:state.step});
    state.draft=result.borrador;state.dirty=false;
    if (result.errores?.length) {message(result.errores,'error');return false;}
    if (next) {state.step=target;message('');render();$('annual-workspace').scrollIntoView({behavior:'smooth',block:'start'});}
    else message(validateAll?'Todos los pasos están listos para instalar.':'Borrador guardado. Puedes retomarlo desde este año.','success');
    return true;
  }
  function removeCatalog(type,itemKey) {
    const used=plan().asignaciones.some(a=>a[type==='grados'?'grado':'materia']===itemKey);
    if (used) throw Error('Este elemento tiene asignaciones preparadas. Quita primero esas asignaciones.');
    if (type==='grados' && plan().estudiantes.some(e=>e.grado===itemKey)) throw Error('Este grado tiene estudiantes seleccionados. Cambia su grado de destino primero.');
    plan()[type]=plan()[type].filter(x=>x.key!==itemKey);
  }
  $('annual-form').addEventListener('submit',e=>e.preventDefault());
  $('step-content').addEventListener('input',e=>{
    if (readonly()) return;
    if (e.target.id==='student-search') {students();return;}
    const path=e.target.dataset.path;
    if (!path) return;
    const parts=path.split('.');let item=plan();for(const part of parts.slice(0,-1))item=item[part];
    item[parts.at(-1)]=e.target.type==='checkbox'?e.target.checked:e.target.type==='number'&&e.target.value!==''?Number(e.target.value):e.target.value;
    dirty();
  });
  $('step-content').addEventListener('change',e=>{
    if (readonly()) return;
    try {
      if (e.target.dataset.period!==undefined) {plan().periodos.forEach((p,i)=>p.activo=i===Number(e.target.dataset.period));dirty();}
      if (e.target.dataset.catalog) {
        const type=e.target.dataset.catalog,item=state.catalog[type].find(x=>String(x.id)===e.target.dataset.id);
        if (e.target.checked) plan()[type].push({...item,key:(type==='grados'?'g':'m')+item.id,id:Number(item.id),...(type==='grados'?{nivel:Number(item.nivel)}:{})});
        else removeCatalog(type,(type==='grados'?'g':'m')+item.id);
        dirty();render();
      }
      if (e.target.dataset.student) {
        const id=e.target.dataset.student;
        if (e.target.checked) {
          const grade=$('enrollment-grade').value;
          if (!grade) {e.target.checked=false;throw Error('Elige el grado de destino antes de seleccionar estudiantes.');}
          plan().estudiantes.push({id,grado:grade});
        } else plan().estudiantes=plan().estudiantes.filter(x=>String(x.id)!==id);
        dirty();students();
      }
    } catch(error) {message(error.message,'error');render();}
  });
  $('step-content').addEventListener('click',e=>{
    const button=e.target.closest('[data-action]');if (!button || readonly() || state.busy) return;
    e.preventDefault();const action=button.dataset.action;
    if (['copy','refresh-files','validate','install'].includes(action)) {run(async()=>{
      if (action==='copy') {
        const base=$('base-year').value;if(!base)throw Error('Selecciona un año de referencia.');
        if ((plan().asignaciones.length || plan().periodos.length) && !confirm('¿Sustituir los períodos y las asignaciones de este borrador por la estructura del año elegido?'))return;
        const result=await api('copy',{ano:plan().ano,base});state.draft.plan=result.plan;dirty();message('Estructura copiada al borrador. Revisa las fechas y asignaciones.','success');render();
      }
      if (action==='refresh-files') {const r=await api('files',{ano:plan().ano});plan().archivos=r.archivos;dirty();render();message('Rutas actualizadas desde la configuración guardada.');}
      if (action==='validate') await save(false,true);
      if (action==='install') {
        const confirmation=$('confirm-year').value.trim(),activate=$('activate-year')?.checked || false;
        if(confirmation!==String(plan().ano))throw Error('Escribe el año que estás instalando para confirmar.');
        if (!await save(false,true))return;
        const r=await api('install',{ano:plan().ano,revision:state.draft.revision,activar:activate,confirmacion:confirmation});
        const loaded=await api('load',{ano:plan().ano});state.draft=loaded.borrador;state.step=7;state.dirty=false;render();
        message(`Instalación terminada.${r.resultado.activado?' El año y el período inicial ya están activos.':' El año queda preparado sin cambiar el año en uso.'}`,'success');
        if(r.resultado.activado)$('current-context').textContent=`Año en uso: ${plan().ano} · Período ${plan().periodos.find(x=>x.activo)?.nombre || ''}`;
      }
    });return;}
    try {
      if(action==='add-period')plan().periodos.push({nombre:'',descripcion:'',inicio:'',fin:'',activo:!plan().periodos.length});
      if(action==='remove') {
        const type=button.dataset.list,index=Number(button.dataset.index),item=plan()[type][index];
        if(type==='asignaciones' && plan().horarios.some(h=>h.asignacion===item.key))throw Error('Quita primero los bloques de horario de esta asignación.');
        plan()[type].splice(index,1);
      }
      if(action==='remove-catalog')removeCatalog(button.dataset.list,button.dataset.key);
      if(action==='add-catalog') {
        const type=button.dataset.list,name=$(`new-${type}`).value.trim();if(!name)throw Error('Escribe un nombre para agregarlo.');
        if(plan()[type].some(x=>x.nombre.toLocaleLowerCase('es')===name.toLocaleLowerCase('es')))throw Error('Este nombre ya está seleccionado.');
        const row={key:key(type),id:0,nombre:name};
        if(type==='grados'){if($('new-grade-level').value==='')throw Error('Elige un grado de referencia para el nivel educativo.');row.nivel=Number($('new-grade-level').value);}
        plan()[type].push(row);
      }
      if(action==='add-assignments') {
        const teacher=$('assignment-teacher').value,grades=[...$('assignment-grades').selectedOptions].map(x=>x.value),subjects=[...$('assignment-subjects').selectedOptions].map(x=>x.value);
        if(!teacher||!grades.length||!subjects.length)throw Error('Selecciona docente, grados y asignaturas.');
        for(const g of grades)for(const m of subjects)if(!plan().asignaciones.some(a=>a.grado===g&&a.materia===m&&String(a.docente)===teacher))plan().asignaciones.push({key:key('a'),grado:g,materia:m,docente:teacher,visible:true});
      }
      if(action==='add-schedule') {
        if(!plan().asignaciones.length)throw Error('Prepara las asignaciones antes de crear horarios.');
        const periods=[...plan().periodos].sort((a,b)=>a.inicio.localeCompare(b.inicio));
        plan().horarios.push({asignacion:'',dia:'',inicio:'',fin:'',desde:periods[0]?.inicio || '',hasta:periods.map(p=>p.fin).sort().at(-1) || ''});
      }
      dirty();render();message('');
    }catch(error){message(error.message,'error');}
  });
  $('annual-steps').addEventListener('click',e=>{const b=e.target.closest('[data-step]');if(!b||state.busy)return;run(async()=>{if(!readonly())await save(false);state.step=Number(b.dataset.step);render();message('');});});
  $('previous-step').addEventListener('click',()=>run(async()=>{if(!readonly())await save(false);state.step=Math.max(0,state.step-1);render();message('');}));
  $('next-step').addEventListener('click',()=>run(()=>save(true)));
  $('save-draft').addEventListener('click',()=>run(()=>save(false)));
  async function openYear() {
    const year=Number($('target-year').value);if(!Number.isInteger(year)||year<2000||year>2100)throw Error('Escribe un año entre 2000 y 2100.');
    if(state.dirty&&!confirm('Hay cambios sin guardar. ¿Abrir el otro año y descartar esos cambios?'))return;
    const r=await api('load',{ano:year});state.draft=r.borrador;state.step=state.draft.paso;state.dirty=false;render();message('');
    const url=new URL(location.href);url.searchParams.set('ano',year);history.replaceState(null,'',url);
  }
  $('open-year').addEventListener('click',()=>run(openYear));
  $('saved-drafts').addEventListener('change',()=>{if($('saved-drafts').value){$('target-year').value=$('saved-drafts').value;run(openYear);}});
  window.addEventListener('beforeunload',e=>{if(state.dirty){e.preventDefault();e.returnValue='';}});
  run(async()=>{
    const r=await api('bootstrap');state.catalog=r.catalogo;state.files=r.archivos;
    $('current-context').textContent=state.catalog.activo?`Año en uso: ${state.catalog.activo.nombre_ano_lectivo} · Período ${state.catalog.periodo?.nombre_periodo || 'sin configurar'}`:'Todavía no hay un año activo';
    if(state.catalog.activo)$('target-year').value=Number(state.catalog.activo.nombre_ano_lectivo)+1;
    $('saved-drafts').innerHTML=option('','Continuar un año guardado…','')+state.catalog.borradores.map(d=>option(d.ano,`${d.ano} · ${d.estado==='instalado'?'Instalado':`Paso ${Number(d.paso)+1}`}`,'')).join('');
    const requested=new URLSearchParams(location.search).get('ano');if(requested){$('target-year').value=requested;await openYear();}
  });
})();
