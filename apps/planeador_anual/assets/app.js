const state = { data:null, activePlanId:null, phase:'', status:'' };
const $ = s => document.querySelector(s);
const $$ = s => [...document.querySelectorAll(s)];
const months = ['ENE','FEB','MAR','ABR','MAY','JUN','JUL','AGO','SEP','OCT','NOV','DIC'];
const labels = {pendiente:'Pendiente',en_progreso:'En progreso',completado:'Completado',bloqueado:'Bloqueado'};

async function api(action, data, method='POST') {
  const opts={method,headers:{'Content-Type':'application/json'}};
  let url=`api.php?action=${action}`;
  if(method==='GET') url += data ? `&${new URLSearchParams(data)}` : '';
  else opts.body=JSON.stringify(data||{});
  const res=await fetch(url,opts); const json=await res.json();
  if(!json.success) throw new Error(json.message||'No fue posible completar la acción.');
  return json;
}
function escapeHtml(value=''){return String(value).replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));}
function toast(message){const t=$('#toast');t.textContent=message;t.classList.add('show');clearTimeout(window.toastTimer);window.toastTimer=setTimeout(()=>t.classList.remove('show'),2800)}
function dateObj(date){return new Date(`${date}T12:00:00`)}
function formatDate(date){return dateObj(date).toLocaleDateString('es-CO',{day:'2-digit',month:'short'}).replace('.','')}
function daysTo(date){return Math.ceil((dateObj(date)-new Date(new Date().toDateString()))/86400000)}
function statusClass(status){return `status-${status}`}
function filteredItems(){let items=state.data?.items||[];if(state.phase)items=items.filter(i=>i.phase===state.phase);if(state.status)items=items.filter(i=>i.status===state.status);return items}

async function load(planId=state.activePlanId){
  try { const result=await api('dashboard',planId?{plan_id:planId}:null,'GET'); state.data=result.data; state.activePlanId=Number(result.data.active_plan.id); render(); }
  catch(e){toast(e.message); $('#timeline').innerHTML='<p class="empty">No fue posible cargar el planeador.</p>'}
}
function render(){
  const d=state.data,p=d.active_plan,s=d.stats;
  $('#sidePlanName').textContent=p.name;$('#sidePlanYear').textContent=`Año lectivo ${p.academic_year}`;$('#heroYear').textContent=p.academic_year;$('#heroTitle').textContent=p.name;$('#heroDescription').textContent=p.description||'Organiza entregas, evidencias y puntos de control durante el año.';
  const reportParams=new URLSearchParams({plan_id:state.activePlanId});if(state.phase)reportParams.set('phase',state.phase);$$('.report-button').forEach(link=>link.href=`reporte.php?${reportParams}`);
  $('#planSwitcher').innerHTML=d.plans.map(plan=>`<option value="${plan.id}" ${Number(plan.id)===state.activePlanId?'selected':''}>${escapeHtml(plan.name)} · ${plan.academic_year}</option>`).join('');
  $('#heroProgress').textContent=`${s.progress}%`;$('#heroProgressBar').style.width=`${s.progress}%`;$('#heroProgressText').textContent=`${s.done} de ${s.total} elementos completados`;
  $('#totalMetric').textContent=s.total;$('#doneMetric').textContent=s.done;$('#lateMetric').textContent=s.late;$('#blockedMetric').textContent=s.blocked;$('#doneMetricNote').textContent=s.total?`${Math.round(s.done/s.total*100)}% del plan`:'sin actividades';$('#lateMetricNote').textContent=s.late?'necesitan atención':'todo al día';
  renderFilters();renderTimeline();renderPending();renderPhaseChart();renderList();renderAnalytics();renderLiveReport();
}
function renderFilters(){
  const phases=(state.data.phases||[]).map(p=>p.name);
  $('#phaseFilter').innerHTML='<option value="">Todos los procesos</option>'+phases.map(p=>`<option ${state.phase===p?'selected':''} value="${escapeHtml(p)}">${escapeHtml(p)}</option>`).join('');
  $('#statusFilter').value=state.status;
  $('#phases').innerHTML=phases.map(p=>`<option value="${escapeHtml(p)}">`).join('');
}
function renderTimeline(){
  const plan=state.data.active_plan, year=Number(plan.academic_year), items=filteredItems();
  const orderedItems=[...items].sort((a,b)=>a.start_date.localeCompare(b.start_date)||a.due_date.localeCompare(b.due_date));
  const lanes=orderedItems.map(item=>`<div class="timeline-lane item-lane">${timelineItem(item,year)}</div>`).join('')||'<div class="timeline-lane"></div>';
  const now=new Date(), todayPos=now.getFullYear()===year?((now-new Date(year,0,1))/(new Date(year+1,0,1)-new Date(year,0,1))*100):null;
  const bodyHeight=Math.max(360,orderedItems.length*79+22);
  $('#timeline').innerHTML=`<div class="month-row" data-section="CALENDARIO ${year}">${months.map(m=>`<span>${m}</span>`).join('')}</div><div class="timeline-body" style="min-height:${bodyHeight+4}px"><div class="timeline-lanes">${lanes}</div>${todayPos!==null?`<span class="today-line" style="left:${todayPos}%"></span>`:''}</div>`;
  renderCalendarFlow();
  $$('.timeline-item').forEach(el=>el.addEventListener('click',()=>openItem(Number(el.dataset.id))));
  $$('.process-cell').forEach(el=>{const apply=()=>{state.phase=state.phase===el.dataset.phase?'':el.dataset.phase;render()};el.addEventListener('click',apply);el.addEventListener('keydown',event=>{if(event.key==='Enter'||event.key===' '){event.preventDefault();apply()}})});
  $$('.add-process-item').forEach(el=>el.addEventListener('click',event=>{event.stopPropagation();openItem(0,el.dataset.phase)}));
}
function renderCalendarFlow(){
  const phases=state.data.phases||[];
  $('#calendarFlow').innerHTML=phases.map((phase,index)=>`<button class="calendar-flow-step ${state.phase===phase.name?'selected':''}" data-phase="${escapeHtml(phase.name)}" style="--step-color:${escapeHtml(phase.color)}"><b>${index+1}</b>${escapeHtml(phase.name)}</button>${index<phases.length-1?'<i>→</i>':''}`).join('');
  $$('.calendar-flow-step').forEach(el=>el.onclick=()=>{state.phase=state.phase===el.dataset.phase?'':el.dataset.phase;render()});
}
function buildStageHeader(phases, allItems, year){
  const yearStart=new Date(year,0,1),yearEnd=new Date(year+1,0,1),phaseRows=[];
  const stageData=phases.map(name=>{const phase=state.data.phases.find(p=>p.name===name)||{},items=allItems.filter(i=>i.phase===name);let start=items.length?Math.min(...items.map(i=>dateObj(i.start_date).getTime())):yearStart.getTime(),end=items.length?Math.max(...items.map(i=>dateObj(i.due_date).getTime())):new Date(year,0,31).getTime();let startMonth=new Date(start).getMonth(),endMonth=new Date(end).getMonth();let left=startMonth/12*100,right=(endMonth+1)/12*100;const minSpan=200/12;if(right-left<minSpan){right=left+minSpan;}if(right>100){right=100;left=Math.max(0,right-minSpan);}return {name,color:phase.color||'#635BFF',left,right};}).sort((a,b)=>a.left-b.left||b.right-a.right);
  stageData.forEach(stage=>{let row=phaseRows.findIndex(end=>end<=stage.left);if(row<0){row=phaseRows.length;phaseRows.push(0)}phaseRows[row]=stage.right;stage.row=row});
  const height=Math.max(48,phaseRows.length*42+8);
  return {height,html:`<div class="process-header ${state.phase?'is-filtering':''}" data-section="HITOS PRINCIPALES" style="height:${height}px">${stageData.map(stage=>`<div class="process-cell ${state.phase===stage.name?'selected':''}" data-phase="${escapeHtml(stage.name)}" role="button" tabindex="0" title="${state.phase===stage.name?'Quitar filtro de':'Mostrar elementos de'} ${escapeHtml(stage.name)}" style="left:${stage.left}%;width:calc(${stage.right-stage.left}% - 3px);top:${stage.row*42+5}px;background:${escapeHtml(stage.color)}"><span>${escapeHtml(stage.name)}</span><em>${state.phase===stage.name?'Mostrando elementos':'Ver elementos'}</em><button class="add-process-item" data-phase="${escapeHtml(stage.name)}" title="Agregar elemento a ${escapeHtml(stage.name)}">＋</button></div>`).join('')}</div>`};
}
function timelineItem(item,year){
  const start=Math.max(0,Math.min(100,(dateObj(item.start_date)-new Date(year,0,1))/(new Date(year+1,0,1)-new Date(year,0,1))*100));
  const end=Math.max(start+1,Math.min(100,(dateObj(item.due_date)-new Date(year,0,1))/(new Date(year+1,0,1)-new Date(year,0,1))*100));
  const width=Math.max(3,end-start); const status=item.status==='completado'?'completed':item.status==='bloqueado'?'blocked':'';
  const detail=`${item.title} · ${item.phase} · ${formatDate(item.start_date)} a ${formatDate(item.due_date)} · ${labels[item.status]} · ${item.progress}% de avance`;
  return `<button class="timeline-item ${status}" data-id="${item.id}" title="${escapeHtml(detail)}" aria-label="${escapeHtml(detail)}" style="left:${start}%;width:calc(${width}% - 5px);min-width:130px;max-width:calc(100% - ${start}% - 4px);background:${escapeHtml(item.color||'#635bff')}"><span class="item-title">${escapeHtml(item.title)}</span><small><b>${labels[item.status]}</b> · ${item.progress}% · hasta ${formatDate(item.due_date)}</small></button>`;
}
function renderPending(){
  const its=(state.data.items||[]).filter(i=>i.status!=='completado').sort((a,b)=>a.due_date.localeCompare(b.due_date)).slice(0,5);
  $('#pendingList').innerHTML=its.length?its.map(i=>{let distance=daysTo(i.due_date),due=distance<0?`Venció hace ${Math.abs(distance)} día(s)`:distance===0?'Vence hoy':`Vence en ${distance} día(s)`;return `<div class="pending-item" data-item="${i.id}"><i class="pending-dot" style="background:${escapeHtml(i.color)}"></i><div class="pending-info"><b>${escapeHtml(i.title)}</b><small>${due} · ${escapeHtml(i.phase)}</small></div><span class="status-pill ${statusClass(i.status)}">${labels[i.status]}</span></div>`}).join(''):'<div class="empty">No hay actividades pendientes. ¡Excelente!</div>';
  $$('.pending-item').forEach(el=>el.addEventListener('click',()=>openItem(Number(el.dataset.item))));
}
function renderPhaseChart(){
  const groups={};(state.data.items||[]).forEach(i=>{groups[i.phase]=groups[i.phase]||[];groups[i.phase].push(i)});const total=state.data.items.length||1;
  $('#phaseChart').innerHTML=Object.entries(groups).map(([phase,items],idx)=>{const pct=Math.round(items.length/total*100),colors=['#635bff','#22c55e','#f59e0b','#ec4899','#3b82f6'];return `<div class="phase-row" style="--phase-color:${colors[idx%colors.length]}"><span>${escapeHtml(phase)}</span><i><b style="width:${pct}%"></b></i><em>${items.length}</em></div>`}).join('')||'<p class="empty">Sin datos</p>';
}
function renderList(){
  const statuses=['pendiente','en_progreso','bloqueado','completado'];const items=state.data.items||[];
  $('#listBoard').innerHTML=statuses.map(status=>{const cols=items.filter(i=>i.status===status);return `<article class="list-column"><header><h3>${labels[status]}</h3><span>${cols.length}</span></header>${cols.map(card).join('')||'<p class="empty">Sin elementos</p>'}</article>`}).join('');
  $$('.list-card').forEach(el=>el.addEventListener('click',()=>openItem(Number(el.dataset.id))));
  $$('.quick-check').forEach(el=>el.addEventListener('click',async event=>{event.preventDefault();event.stopPropagation();try{await api('toggle_check',{id:Number(el.dataset.checkId),is_done:!el.classList.contains('done')});toast('Checklist actualizado.');load()}catch(error){toast(error.message)}}));
}
function card(i){const checks=i.checklist||[],checked=checks.filter(c=>Number(c.is_done)).length,total=checks.length,next=checks.find(c=>!Number(c.is_done));return `<div class="list-card" data-id="${i.id}"><div class="card-top"><b>${escapeHtml(i.title)}</b><span class="tag">${escapeHtml(i.phase)}</span></div><p>${i.description?escapeHtml(i.description):'Sin descripción registrada'}</p>${next?`<button class="quick-check" data-check-id="${next.id}" title="Marcar como realizado"><i></i>${escapeHtml(next.title)}</button>`:''}<div class="card-footer"><span>◷ ${formatDate(i.due_date)}</span><span>${total?`☑ ${checked}/${total}`:`◉ ${i.progress}%`}</span>${i.resource_url?`<a href="${escapeHtml(i.resource_url)}" target="_blank" rel="noopener" title="Abrir recurso" onclick="event.stopPropagation()">↗</a>`:''}</div></div>`}
function renderAnalytics(){
  const groups={};(state.data.items||[]).forEach(i=>{groups[i.phase]=groups[i.phase]||[];groups[i.phase].push(i)});
  const colors=['#635bff','#22c55e','#f59e0b','#ec4899','#3b82f6'];
  $('#progressByPhase').innerHTML=Object.entries(groups).map(([phase,items],idx)=>{const p=Math.round(items.reduce((a,b)=>a+Number(b.progress),0)/items.length);return `<div class="progress-phase-row"><div><b>${escapeHtml(phase)}</b><small>${p}% · ${items.length} elementos</small></div><i><b style="width:${p}%;background:${colors[idx%colors.length]}"></b></i></div>`}).join('')||'<p class="empty">Agrega elementos para ver los indicadores.</p>';
  const stats=state.data.stats,total=stats.total||1,done=Math.round(stats.done/total*100),going=Math.round((state.data.items.filter(i=>i.status==='en_progreso').length)/total*100);
  $('#statusDonut').innerHTML=`<div class="donut" style="--done:${done}%;--going:${going}%"><div><b>${stats.progress}%</b><span>avance global</span></div></div><div class="legend"><span><i style="background:#20ad6b"></i>Completado</span><span><i style="background:#f2ac35"></i>En curso</span><span><i style="background:#e9eaf0"></i>Pendiente</span></div>`;
  const upcoming=(state.data.items||[]).filter(i=>i.status!=='completado'&&daysTo(i.due_date)>=0).sort((a,b)=>a.due_date.localeCompare(b.due_date))[0];
  $('#insights').innerHTML=`<div class="insight"><span>✓</span><div><b>${stats.done} elementos finalizados</b><small>${stats.total?Math.round(stats.done/stats.total*100):0}% de cumplimiento</small></div></div><div class="insight"><span>◷</span><div><b>${upcoming?escapeHtml(upcoming.title):'No hay próximos vencimientos'}</b><small>${upcoming?`Próximo: ${formatDate(upcoming.due_date)}`:'Planifica tu siguiente elemento'}</small></div></div><div class="insight"><span>!</span><div><b>${stats.late} actividades vencidas</b><small>${stats.late?'Prioriza una revisión de estado':'No hay retrasos detectados'}</small></div></div>`;
}
function renderLiveReport(){
  const plan=state.data.active_plan,items=[...filteredItems()],phases=(state.data.phases||[]),groups={};
  items.forEach(item=>(groups[item.phase]??=[]).push(item));
  $('#liveReportTitle').textContent=plan.name;$('#liveReportSubtitle').textContent=state.phase?`Vista del proceso: ${state.phase}`:`Año lectivo ${plan.academic_year} · ${items.length} elemento(s) visibles`;
  $('#liveReportProgress').textContent=`${state.data.stats.progress}%`;
  const visiblePhases=phases.filter(phase=>groups[phase.name]);
  $('#liveFlowSteps').innerHTML=visiblePhases.length?visiblePhases.map((phase,index)=>`<button class="live-flow-step" data-phase="${escapeHtml(phase.name)}" style="--step-color:${escapeHtml(phase.color)}"><b>${index+1}</b>${escapeHtml(phase.name)}</button>${index<visiblePhases.length-1?'<i>→</i>':''}`).join(''):'<span class="empty">No hay procesos para este filtro.</span>';
  $('#liveReportContent').innerHTML=visiblePhases.map((phase,index)=>`<section class="report-process"><div class="report-process-heading"><span class="report-process-number" style="background:${escapeHtml(phase.color)}">${index+1}</span><div><h3>${escapeHtml(phase.name)}</h3><p>${groups[phase.name].length} elemento(s) para ejecutar</p></div><button class="text-button report-filter-phase" data-phase="${escapeHtml(phase.name)}">Ver en ruta</button></div><p class="reorder-hint">⠿ Arrastra los elementos para cambiar el orden de la ruta.</p><ol type="a" class="report-order-list" data-phase="${escapeHtml(phase.name)}">${groups[phase.name].map(reportItem).join('')}</ol></section>`).join('')||'<div class="empty">No hay elementos registrados con este filtro.</div>';
  $$('.live-flow-step,.report-filter-phase').forEach(el=>el.onclick=()=>{state.phase=el.dataset.phase;render();showView('timeline')});
  $$('.report-item').forEach(el=>el.onclick=()=>openItem(Number(el.dataset.id)));
  if(!state.status) enableReportReorder();
}
function reportItem(item){const checks=item.checklist||[],done=checks.filter(check=>Number(check.is_done)).length;return `<li class="report-item" draggable="true" data-id="${item.id}"><span class="drag-handle" title="Arrastra para ordenar">⠿</span><div class="report-item-body"><div class="report-item-head"><strong>${escapeHtml(item.title)}</strong><span class="status-pill ${statusClass(item.status)}">${labels[item.status]}</span></div><p class="report-item-meta">${formatDate(item.start_date)} — ${formatDate(item.due_date)} <b>·</b> ${item.progress}% de avance ${checks.length?`<b>·</b> ${done}/${checks.length} verificaciones`:''}</p>${item.description?`<p class="report-item-description">${escapeHtml(item.description)}</p>`:''}${checks.length?`<ul class="report-checks">${checks.map(check=>`<li class="${Number(check.is_done)?'done':''}">${Number(check.is_done)?'✓':'□'} ${escapeHtml(check.title)}</li>`).join('')}</ul>`:''}</div></li>`}
function enableReportReorder(){
  let dragged=null;
  $$('.report-item').forEach(item=>{item.addEventListener('dragstart',event=>{dragged=item;item.classList.add('dragging');event.dataTransfer.effectAllowed='move'});item.addEventListener('dragend',()=>{item.classList.remove('dragging');dragged=null});item.addEventListener('dragover',event=>{event.preventDefault();if(!dragged||dragged===item)return;const box=item.getBoundingClientRect();if(event.clientY<box.top+box.height/2)item.before(dragged);else item.after(dragged)});});
  $$('.report-order-list').forEach(list=>{list.addEventListener('dragover',event=>event.preventDefault());list.addEventListener('drop',async event=>{event.preventDefault();const ids=[...list.querySelectorAll('.report-item')].map(item=>Number(item.dataset.id));try{await api('reorder_items',{plan_id:state.activePlanId,ids});toast('Orden de elementos actualizado.');await load()}catch(error){toast(error.message)}})});
}
function openModal(id){$(id).classList.add('open')}function closeModal(id){$(id).classList.remove('open')}
function openItem(id, defaultPhase=''){
  const item=id?state.data.items.find(i=>Number(i.id)===Number(id)):null,form=$('#itemForm'); form.reset();$('#checkEditor').innerHTML='';$('#deleteItemBtn').style.visibility=item?'visible':'hidden';$('#itemModalTitle').textContent=item?'Editar elemento':'Nuevo elemento';$('#itemPlanId').value=state.activePlanId;
  if(item){Object.entries(item).forEach(([key,val])=>{if(form.elements[key]&&typeof val!=='object')form.elements[key].value=val});(item.checklist||[]).forEach(c=>addCheck(c.title,Number(c.is_done)));}
  else {let y=state.data.active_plan.academic_year;form.elements.start_date.value=`${y}-01-01`;form.elements.due_date.value=`${y}-01-02`;form.elements.color.value=state.data.active_plan.color;form.elements.phase.value=defaultPhase;form.elements.progress.value=0;}
  $('#rangeValue').textContent=`${form.elements.progress.value||0}%`;openModal('#itemModal');
}
function addCheck(title='',done=0){const row=document.createElement('div');row.className='check-edit';row.innerHTML=`<input type="text" value="${escapeHtml(title)}" placeholder="Ej. Validar documentos"><label title="Completado"><input type="checkbox" ${done?'checked':''}></label><button type="button" title="Eliminar">×</button>`;row.querySelector('button').onclick=()=>row.remove();$('#checkEditor').append(row)}
async function saveItem(event){event.preventDefault();const form=event.currentTarget,data=Object.fromEntries(new FormData(form));data.checklist=$$('#checkEditor .check-edit').map(row=>({title:row.querySelector('input[type=text]').value,is_done:row.querySelector('input[type=checkbox]').checked}));try{await api('save_item',data);closeModal('#itemModal');toast('Elemento guardado correctamente.');load()}catch(e){toast(e.message)}}
function openPlan(){const p=state.data.active_plan,f=$('#planForm');$('#planModalTitle').textContent='Plan anual';Object.entries(p).forEach(([k,v])=>{if(f.elements[k])f.elements[k].value=v});$('#deletePlanBtn').style.visibility=state.data.plans.length>1?'visible':'hidden';openModal('#planModal')}
function openNewPlan(){const f=$('#planForm');f.reset();f.elements.id.value='';f.elements.academic_year.value=state.data.academic_year;f.elements.color.value='#635BFF';$('#planModalTitle').textContent='Crear plan anual';$('#deletePlanBtn').style.visibility='hidden';openModal('#planModal')}
function openPhases(){const f=$('#phaseForm');f.reset();f.elements.id.value='';f.elements.plan_id.value=state.activePlanId;f.elements.color.value=state.data.active_plan.color;f.elements.sort_order.value=(state.data.phases||[]).length;renderPhaseManager();openModal('#phasesModal')}
function renderPhaseManager(){
  const phases=state.data.phases||[];
  $('#phaseManagerList').innerHTML=phases.length?phases.map(phase=>`<article class="phase-manager-row"><i style="background:${escapeHtml(phase.color)}"></i><div><b>${escapeHtml(phase.name)}</b><small>${phase.item_count} elemento${Number(phase.item_count)!==1?'s':''}</small></div><button class="phase-add" data-name="${escapeHtml(phase.name)}">＋ Elemento</button><button class="phase-edit" data-id="${phase.id}">Editar</button><button class="phase-delete" data-id="${phase.id}" title="Eliminar proceso">×</button></article>`).join(''):'<p class="empty">Aún no hay procesos. Crea el primero para organizar el plan.</p>';
  $$('.phase-add').forEach(el=>el.onclick=()=>{closeModal('#phasesModal');openItem(0,el.dataset.name)});
  $$('.phase-edit').forEach(el=>el.onclick=()=>{const phase=phases.find(p=>Number(p.id)===Number(el.dataset.id)),f=$('#phaseForm');Object.entries(phase).forEach(([k,v])=>{if(f.elements[k])f.elements[k].value=v});f.elements.plan_id.value=state.activePlanId;});
  $$('.phase-delete').forEach(el=>el.onclick=()=>deletePhase(Number(el.dataset.id)));
}
async function savePhase(event){event.preventDefault();try{await api('save_phase',Object.fromEntries(new FormData(event.currentTarget)));event.currentTarget.reset();event.currentTarget.elements.plan_id.value=state.activePlanId;toast('Proceso guardado.');await load();renderPhaseManager()}catch(error){toast(error.message)}}
async function deletePhase(id){if(!confirm('¿Eliminar este proceso? Sus elementos se trasladarán a "General".'))return;try{await api('delete_phase',{id});toast('Proceso eliminado.');await load();renderPhaseManager()}catch(error){toast(error.message)}}
async function savePlan(e){e.preventDefault();const data=Object.fromEntries(new FormData(e.currentTarget));try{const r=await api('save_plan',data);state.activePlanId=Number(r.id);closeModal('#planModal');toast('Plan actualizado.');load()}catch(err){toast(err.message)}}
async function deleteItem(){const id=Number($('#itemForm').elements.id.value);if(!id||!confirm('¿Eliminar este elemento y su checklist?'))return;try{await api('delete_item',{id});closeModal('#itemModal');toast('Elemento eliminado.');load()}catch(e){toast(e.message)}}
async function deletePlan(){const id=state.activePlanId;if(!confirm('¿Eliminar el plan y todos sus elementos? Esta acción no se puede deshacer.'))return;try{await api('delete_plan',{id});closeModal('#planModal');toast('Plan eliminado.');state.activePlanId=null;load()}catch(e){toast(e.message)}}

document.addEventListener('DOMContentLoaded',()=>{
  $('.nav-item[data-view="timeline"]').onclick=()=>showView('timeline');$('.nav-item[data-view="list"]').onclick=()=>showView('list');$('.nav-item[data-view="metrics"]').onclick=()=>showView('metrics');$('.nav-item[data-view="report"]').onclick=()=>showView('report');
  $('#newItemBtn').onclick=()=>openItem();$('#newItemListBtn').onclick=()=>openItem();$('#addCheckBtn').onclick=()=>addCheck();$('#itemForm').onsubmit=saveItem;$('#deleteItemBtn').onclick=deleteItem;$('#itemForm').elements.progress.oninput=e=>$('#rangeValue').textContent=`${e.target.value}%`;
  $$('.close-modal').forEach(e=>e.onclick=()=>closeModal('#itemModal'));$('.close-plan').onclick=()=>closeModal('#planModal');$$('.close-phases').forEach(e=>e.onclick=()=>closeModal('#phasesModal'));$('#settingsBtn').onclick=openPlan;$('#createPlanBtn').onclick=openNewPlan;$('#newPlanBtn').onclick=openPlan;$('#managePhasesBtn').onclick=openPhases;$('#openOrderingBtn').onclick=()=>{showView('report');setTimeout(()=>document.querySelector('.reorder-banner')?.scrollIntoView({behavior:'smooth',block:'center'}),0)};$('#planSwitcher').onchange=e=>load(Number(e.target.value));$('#planForm').onsubmit=savePlan;$('#phaseForm').onsubmit=savePhase;$('#cancelPhaseEdit').onclick=()=>{const f=$('#phaseForm');f.reset();f.elements.plan_id.value=state.activePlanId;f.elements.sort_order.value=(state.data.phases||[]).length};$('#deletePlanBtn').onclick=deletePlan;
  $('#phaseFilter').onchange=e=>{state.phase=e.target.value;render()};$('#statusFilter').onchange=e=>{state.status=e.target.value;render()};$('#goToday').onclick=()=>$('#timeline').scrollIntoView({behavior:'smooth',block:'center'});$('#todayBtn').onclick=()=>$('#timeline').scrollIntoView({behavior:'smooth',block:'center'});
  load();
});
function showView(view){$$('.view').forEach(el=>el.classList.toggle('active',el.id===`${view}View`));$$('.nav-item[data-view]').forEach(el=>el.classList.toggle('active',el.dataset.view===view));$('#pageTitle').textContent={timeline:'Ruta anual',list:'Elementos de ruta',metrics:'Métricas y seguimiento',report:'Reporte de flujo'}[view]}
