'use strict';
const $ = s => document.querySelector(s);
const uid = () => crypto.randomUUID();
const block = (type, text = '') => ({type, text, checked:false});
const makePage = (title='Sin título', icon='📄', parent=null, blocks=[]) => ({id:uid(), title, icon, parent, blocks, favorite:false, deleted:false, cover:0, updated:new Date().toISOString()});
let pages=[], version=0, active=null, timer, saving=false, dirty=false, blocked=false;
const types={text:'Texto',h1:'Título grande',h2:'Subtítulo',bullet:'Lista con viñetas',todo:'Lista de tareas',quote:'Cita',callout:'Nota destacada',divider:'Separador'};
const palettes=['linear-gradient(115deg,#e5e8dc,#d4ddcf 40%,#b9cdbf)','linear-gradient(115deg,#eee1d0,#dac7ac,#c6a987)','linear-gradient(115deg,#e3e5f1,#cdd7e5,#a8bdd2)','linear-gradient(115deg,#f0dee1,#e5cbd5,#ceb6cc)'];
function current(){return pages.find(p=>p.id===active)}
function el(tag, text, cls){const e=document.createElement(tag);if(text!==undefined)e.textContent=text;if(cls)e.className=cls;return e}
function status(text){$('#save-state').textContent=text}
let drag = null, suppressDragClick = false;
function bindDrag(grip, row, kind, key) {
 grip.addEventListener('pointerdown', e => {
  if (e.button !== 0 || drag) return;
  drag = {pointer:e.pointerId, x:e.clientX, y:e.clientY, kind, key, row, page:active, started:false, target:null};
  grip.setPointerCapture(e.pointerId);
 });
 grip.addEventListener('lostpointercapture', () => {if(drag)finishDrag(false)});
}
function clearDrop() {
 document.querySelectorAll('.drop-before,.drop-after,.drop-inside').forEach(e=>e.classList.remove('drop-before','drop-after','drop-inside'));
}
function updateDrag(x,y) {
 if(!drag?.started)return;
 drag.ghost.style.left=Math.min(x+15,innerWidth-160)+'px';
 drag.ghost.style.top=Math.min(y+15,innerHeight-45)+'px';
 clearDrop();drag.target=null;
 const hit=document.elementFromPoint(x,y);
 const target=hit?.closest(drag.kind==='block'?'[data-block-index]':'[data-page-id]');
 if(!target)return;
 const key=drag.kind==='block'?Number(target.dataset.blockIndex):target.dataset.pageId;
 if(key===drag.key)return;
 const r=target.getBoundingClientRect(), fraction=(y-r.top)/r.height;
 const favorites=!!target.closest('#favorites');
 const position=drag.kind==='page'&&!favorites&&fraction>.27&&fraction<.73?'inside':fraction<.5?'before':'after';
 if(drag.kind==='page') {
  // Validate on a copy so invalid descendant targets never become drop zones.
  if(!EspacioOrder.movePage(pages.map(p=>({...p})),drag.key,key,position,favorites))return;
 }
 target.classList.add('drop-'+position);
 drag.target={key,position,favorites};
}
function finishDrag(commit) {
 if(!drag)return;
 const state=drag;drag=null;clearDrop();state.row.classList.remove('dragging-source');state.ghost?.remove();
 document.body.classList.remove('is-dragging');cancelAnimationFrame(state.frame);
 if(!state.started)return;
 suppressDragClick=true;setTimeout(()=>{suppressDragClick=false},350);
 if(!commit||!state.target||state.page!==active)return;
 const {key,position,favorites}=state.target;
 const moved=state.kind==='block'?EspacioOrder.moveBlock(current().blocks,state.key,key,position==='after'):EspacioOrder.movePage(pages,state.key,key,position,favorites);
 if(!moved)return;
 changed();
 if(state.kind==='block')renderBlocks();else{navigation();renderChildren();if(!$('#listing').hidden)listing($('#breadcrumb').textContent==='Papelera');const p=current();if(p){const parent=pages.find(x=>x.id===p.parent);$('#parent-label').textContent=parent?'↳ '+parent.title:'';if(!$('#document').hidden)$('#breadcrumb').textContent=(parent?parent.title+' / ':'Privado / ')+(p.title||'Sin título')}}
 $('#drag-announcement').textContent=state.kind==='block'?'Bloque reordenado.':position==='inside'?'Página movida como subpágina.':'Página reordenada.';
}
document.addEventListener('pointermove',e=>{
 if(!drag||e.pointerId!==drag.pointer)return;
 drag.lastX=e.clientX;drag.lastY=e.clientY;
 if(!drag.started&&Math.hypot(e.clientX-drag.x,e.clientY-drag.y)<6)return;
 e.preventDefault();
 if(!drag.started){
  drag.started=true;$('#popover').hidden=true;
  drag.row.classList.add('dragging-source');document.body.classList.add('is-dragging');
  drag.ghost=el('div',drag.kind==='page'?pages.find(p=>p.id===drag.key)?.title||'Sin título':current().blocks[drag.key]?.text||'Bloque','drag-ghost');document.body.append(drag.ghost);
  const scroll=()=>{
   if(!drag)return;
   const sidebar=$('#sidebar');const rect=sidebar.getBoundingClientRect();
   const inSidebar=drag.kind==='page'&&drag.lastX>=rect.left&&drag.lastX<=rect.right&&getComputedStyle(sidebar).display!=='none';
   const scroller=inSidebar?sidebar:document.scrollingElement;
   const top=inSidebar?rect.top:0,bottom=inSidebar?rect.bottom:innerHeight;
   const delta=drag.lastY<top+65?-12:drag.lastY>bottom-65?12:0;
   if(delta){scroller.scrollTop+=delta;updateDrag(drag.lastX,drag.lastY)}
   drag.frame=requestAnimationFrame(scroll);
  };drag.frame=requestAnimationFrame(scroll);
 }
 updateDrag(e.clientX,e.clientY);
},{passive:false});
document.addEventListener('pointerup',e=>{if(drag&&drag.pointer===e.pointerId){if(drag.started)updateDrag(e.clientX,e.clientY);finishDrag(true)}});
document.addEventListener('pointercancel',()=>finishDrag(false));
window.addEventListener('blur',()=>finishDrag(false));
document.addEventListener('keydown',e=>{if(e.key==='Escape')finishDrag(false)});
document.addEventListener('click',e=>{if(suppressDragClick){e.preventDefault();e.stopImmediatePropagation();suppressDragClick=false}},true);
function changed(p=current()){dirty=true;if(p)p.updated=new Date().toISOString();status('Cambios sin guardar');clearTimeout(timer);timer=setTimeout(save,650)}
async function save(){
 if(saving||!dirty||blocked)return;
 saving=true;dirty=false;$('#save-button').disabled=true;$('#save-button').textContent='Guardando…';status('Guardando…');
 try{const r=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':window.espacioCsrf},body:JSON.stringify({version,pages})});const result=await r.json();if(!r.ok){if(r.status===409)blocked=true;throw Error(result.message||'No se pudo guardar')}version=result.version;status(dirty?'Guardando…':'Todos los cambios guardados');}
 catch(e){dirty=true;status('⚠ Sin guardar');if(!$('#save-state').title)alert(e.message+' Puedes exportar tus páginas desde el menú •••.');$('#save-state').title=e.message;}
 finally{saving=false;$('#save-button').disabled=false;$('#save-button').textContent='Guardar';if(dirty&&!blocked){clearTimeout(timer);timer=setTimeout(save,5000)}}
}
function manualSave(){
 if(blocked){alert('Este espacio cambió en otra pestaña. Exporta tus cambios desde ••• antes de recargar.');return}
 if(saving)return;
 clearTimeout(timer);
 if(!dirty){status('Todos los cambios guardados');return}
 save();
}
$('#save-button').onclick=manualSave;
function pageButton(p,depth=0){const row=el('div',undefined,'page-link'+(p.id===active?' active':''));row.style.paddingLeft=(8+depth*13)+'px';row.dataset.pageId=p.id;const grip=el('button','⠿','drag-grip');grip.title='Arrastra para ordenar; suelta en el centro de otra página para anidar';grip.setAttribute('aria-label','Mover '+(p.title||'página'));row.append(grip);bindDrag(grip,row,'page',p.id);const open=el('button',p.parent?'›':'⌄');open.title='Abrir página';open.style.opacity=1;open.onclick=()=>openPage(p.id);row.append(open,el('span',p.icon||'📄'));const label=el('a',p.title||'Sin título','label');label.href='#'+p.id;row.append(label);const add=el('button','＋');add.title='Añadir subpágina';add.onclick=()=>createPage('Sin título','📄',p.id);row.append(add);return row}
function navigation(){
 $('#pages').replaceChildren();$('#favorites').replaceChildren();
 function tree(parent,depth,seen=new Set()){for(const p of pages.filter(p=>!p.deleted&&(p.parent||null)===parent)){if(seen.has(p.id))continue;seen.add(p.id);$('#pages').append(pageButton(p,Math.min(depth,6)));tree(p.id,depth+1,seen)}}tree(null,0);
 pages.filter(p=>p.favorite&&!p.deleted).forEach(p=>$('#favorites').append(pageButton(p)));
 if(!$('#favorites').children.length)$('#favorites').append(el('div','Marca tus páginas con ☆','nav muted'));
}
function openPage(id){const p=pages.find(p=>p.id===id&&!p.deleted);if(!p)return;$('#reports').hidden=true;$('#reports-open').classList.remove('active');$('#favorite').hidden=false;active=id;history.replaceState(null,'','#'+id);$('#listing').hidden=true;$('#document').hidden=false;$('#cover').hidden=false;$('#title').value=p.title;resizeTitle();$('#page-icon').textContent=p.icon;$('#favorite').textContent=p.favorite?'★':'☆';$('#cover').style.background=palettes[p.cover||0];const parent=pages.find(x=>x.id===p.parent);$('#breadcrumb').textContent=(parent?(parent.title||'Sin título')+' / ':'Privado / ')+(p.title||'Sin título');$('#parent-label').textContent=parent?'↳ '+parent.title:'';renderBlocks();navigation();renderChildren();document.body.classList.remove('mobile-nav')}
function printDate(value, time=false){const date=new Date(value);return Number.isFinite(date.getTime())?date.toLocaleString('es-CO',{timeZone:'America/Bogota',year:'numeric',month:'long',day:'numeric',...(time?{hour:'2-digit',minute:'2-digit'}:{})}):'Sin fecha registrada'}
function printMasthead(kind){const head=el('div',undefined,'paper-masthead');head.append(el('strong','GUAGUA / ESPACIO'),el('span',kind));return head}
function printLink(value){let href='';try{href=taskUrl(value||'')}catch{}if(!href)return null;const link=el('a',href,'paper-link');link.href=href;return link}
function printBlocks(blocks){
 const content=el('div',undefined,'paper-content');
 blocks.forEach(b=>{
  let node;
  if(b.type==='divider'){node=el('hr')}
  else if(b.type==='h1'||b.type==='h2'){node=el(b.type==='h1'?'h2':'h3',b.text)}
  else if(b.type==='todo'){
   node=el('div',undefined,'paper-task'+(b.checked?' completed':''));node.style.marginLeft=EspacioOrder.level(b)*5+'mm';
   const box=el('span',b.checked?'✓':'','paper-check');box.setAttribute('aria-label',b.checked?'Completada':'Pendiente');
   const detail=el('div');detail.append(el('span',b.text||'Tarea sin título'));const link=printLink(b.link);if(link)detail.append(link);node.append(box,detail);
  }else if(b.type==='bullet'){node=el('div',undefined,'paper-bullet');node.append(el('span','•'),el('p',b.text))}
  else if(b.type==='quote'){node=el('blockquote',b.text)}
  else if(b.type==='callout'){node=el('aside',b.text,'paper-callout')}
  else node=el('p',b.text||'\u00a0');
  content.append(node);
 });return content;
}
function printReport(){
 const rows=EspacioReports.filter(EspacioReports.collect(pages),{page:$('#report-page').value,status:$('#report-status').value,query:$('#report-search').value,order:$('#report-order').value});
 const sheet=el('section',undefined,'print-sheet');sheet.append(printMasthead('REPORTE DE SEGUIMIENTO'),el('h1','Reporte de tareas','paper-title'),el('p','Generado el '+printDate(new Date(),true)+' · Hora de Colombia','paper-meta'));
 const pageLabel=$('#report-page').selectedOptions[0]?.textContent||'Todas las páginas';
 sheet.append(el('p','Página: '+pageLabel+' · Estado: '+$('#report-status').selectedOptions[0].textContent+' · Orden: '+$('#report-order').selectedOptions[0].textContent+($('#report-search').value?' · Búsqueda: '+$('#report-search').value:''),'paper-scope'));
 const stats=EspacioReports.summary(rows),metrics=el('div',undefined,'paper-metrics');
 [['Tareas',stats.total],['Completadas',stats.done],['Pendientes',stats.pending],['Avance',stats.percent+'%']].forEach(([label,value])=>{const metric=el('div');metric.append(el('strong',String(value)),el('span',label));metrics.append(metric)});sheet.append(metrics);
 sheet.append(el('h2','Avance por página'));
 const groups=new Map();rows.forEach(row=>{if(!groups.has(row.page.id))groups.set(row.page.id,{page:row.page,rows:[]});groups.get(row.page.id).rows.push(row)});
 for(const {page,rows} of groups.values()){const sum=EspacioReports.summary(rows);const line=el('div',undefined,'paper-progress-row');line.append(el('strong',page.title||'Sin título'),el('span',sum.done+' / '+sum.total+' completadas · '+sum.percent+'%'));sheet.append(line)}
 sheet.append(el('h2','Detalle de tareas'));
 const table=el('table',undefined,'paper-table'),thead=el('thead'),tr=el('tr');['Tarea y ubicación','Estado','Cumplimiento'].forEach(label=>{const th=el('th',label);th.scope='col';tr.append(th)});thead.append(tr);table.append(thead);const tbody=el('tbody');
 rows.forEach(({page,block,ancestors,completedAt})=>{
  const row=el('tr'),task=el('td');task.append(el('strong',block.text||'Tarea sin título'),el('small',(page.title||'Sin título')+(ancestors.length?' / '+ancestors.join(' / '):'')));
  const link=printLink(block.link);if(link)task.append(link);
  row.append(task,el('td',block.checked?'Completada':'Pendiente'),el('td',block.checked?(completedAt===null?'Sin fecha registrada':printDate(completedAt,true)):'—'));tbody.append(row);
 });table.append(tbody);sheet.append(table);
 if(!rows.length)sheet.append(el('p','No hay tareas que coincidan con los filtros.','paper-note'));
 sheet.append(el('p','Alcance: los totales y el avance corresponden a los filtros indicados. Cada tarea y subtarea cuenta por separado; se excluye la papelera. Se incluye la lista completa, aunque en pantalla haya más resultados por mostrar.','paper-note'));
 return [sheet];
}
function buildPrintDocument(){
 const scope=$('#print-scope').value;
 if(scope==='report')return printReport();
 const page=current();if(!page||page.deleted)return [];
 const group=scope==='project'?EspacioPrint.project(pages,page.id):[{page,depth:0}];
 return group.map(({page:p,depth},index)=>{
  const sheet=el('section',undefined,'print-sheet');sheet.append(printMasthead(scope==='project'?'PROYECTO · '+(index+1)+' / '+group.length:scope==='section'?'SECCIÓN DEL PROYECTO':'DOCUMENTO PERSONAL'));
  const section=scope==='section'?EspacioPrint.sections(p.blocks).find(x=>x.index===Number($('#print-section').value)):null;
  sheet.append(el('p',scope==='section'?p.title||'Sin título':depth?'Subpágina de '+(pages.find(x=>x.id===p.parent)?.title||'proyecto'):scope==='project'?'Proyecto y sus secciones':'Guagua Espacio','paper-eyebrow'));
  sheet.append(el('h1',section?.title||p.title||'Sin título','paper-title'));
  sheet.append(el('p','Emitido el '+printDate(new Date(),true)+(p.updated?' · Actualizado: '+printDate(p.updated):''),'paper-meta'));
  if(scope==='project'&&index===0&&group.length>1){const toc=el('div',undefined,'paper-index');toc.append(el('strong','CONTENIDO DEL PROYECTO'));group.slice(1).forEach(({page,depth})=>{const item=el('div',page.title||'Sin título');item.style.marginLeft=(depth-1)*4+'mm';toc.append(item)});sheet.append(toc)}
  const blocks=scope==='section'?EspacioPrint.section(p.blocks,Number($('#print-section').value)):p.blocks;
  // The selected heading is already displayed as the document title.
  sheet.append(printBlocks(section&&['h1','h2'].includes(blocks[0]?.type)?blocks.slice(1):blocks));
  if(!blocks.length)sheet.append(el('p','Esta página todavía no tiene contenido.','paper-note'));
  return sheet;
 });
}
function refreshPrintPreview(){
 $('#print-section-field').hidden=$('#print-scope').value!=='section';
 const sheets=buildPrintDocument();$('#print-preview').replaceChildren(...sheets);
 $('#print-output').replaceChildren(...sheets.map(sheet=>sheet.cloneNode(true)));
 $('#print-confirm').disabled=!sheets.length;
 $('#print-notice').textContent=($('#print-scope').value==='report'?'Se imprimen todos los resultados de los filtros, sin el límite de pantalla. ':$('#print-scope').value==='project'?'Cada subpágina empieza en una nueva hoja. ':'')+(dirty||saving?'Incluye tus cambios actuales; todavía hay cambios pendientes de guardar.':'');
}
function preparePrintScope(){
 const isPage=!$('#document').hidden&&!!current()&&!current().deleted;
 const sections=isPage?EspacioPrint.sections(current().blocks):[];
 for(const option of $('#print-scope').options)option.disabled=option.value==='section'?!sections.length:option.value!=='report'&&!isPage;
 $('#print-scope').value=isPage?'page':'report';$('#print-section').replaceChildren();
 sections.forEach(section=>{const option=el('option',section.title);option.value=String(section.index);$('#print-section').append(option)});
}
$('#print-open').onclick=()=>{preparePrintScope();refreshPrintPreview();$('#print-dialog').showModal()};
$('#print-scope').onchange=$('#print-section').onchange=refreshPrintPreview;
$('#print-close').onclick=()=>$('#print-dialog').close();
$('#print-confirm').onclick=()=>{refreshPrintPreview();window.print()};
let titleBeforePrint=null;
window.addEventListener('beforeprint',()=>{
 if(!$('#print-dialog').open)preparePrintScope();refreshPrintPreview();
 if(titleBeforePrint===null)titleBeforePrint=document.title;
 document.title=($('#print-output .paper-title')?.textContent||'Documento')+' · Guagua';
});
window.addEventListener('afterprint',()=>{if(titleBeforePrint!==null){document.title=titleBeforePrint;titleBeforePrint=null}});
function resizeTitle(){$('#title').style.height='auto';$('#title').style.height=$('#title').scrollHeight+'px'}
let reportLimit=50;
function showReports(){
 finishDrag(false);$('#popover').hidden=true;
 $('#document').hidden=true;$('#cover').hidden=true;$('#listing').hidden=true;$('#reports').hidden=false;$('#favorite').hidden=true;
 $('#reports-open').classList.add('active');$('#breadcrumb').textContent='Privado / Reportes de tareas';history.replaceState(null,'','#reportes');
 document.body.classList.remove('mobile-nav');
 const selection=$('#report-page').value;
 $('#report-page').replaceChildren(el('option','Todas las páginas'));
 $('#report-page').firstChild.value='';
 pages.filter(p=>!p.deleted&&p.blocks.some(b=>b.type==='todo')).forEach(p=>{const option=el('option',(p.icon||'📄')+' '+(p.title||'Sin título'));option.value=p.id;$('#report-page').append(option)});
 $('#report-page').value=selection;
 if($('#report-page').selectedIndex<0)$('#report-page').value='';
 reportLimit=50;renderReportSummary();renderReportTasks();window.scrollTo(0,0);$('#reports-title').focus({preventScroll:true});
}
function progressBar(done,total,label){const bar=el('progress');bar.max=total||1;bar.value=done;bar.setAttribute('aria-label',label);return bar}
function renderReportSummary(){
 const rows=EspacioReports.collect(pages),sum=EspacioReports.summary(rows),box=$('#report-summary');box.replaceChildren();
 for(const [label,value,style] of [['Tareas y subtareas',sum.total,''],['Completadas',sum.done,'complete'],['Pendientes',sum.pending,'pending'],['Avance general',sum.percent+'%','progress']]){
  const card=el('div',undefined,'report-stat '+style);card.append(el('span',label),el('strong',String(value)));if(style==='progress')card.append(progressBar(sum.done,sum.total,'Avance general'));box.append(card);
 }
 const byPage=$('#report-pages');byPage.replaceChildren();
 if(!rows.length)byPage.append(el('p','Todavía no hay tareas. Añade una lista de tareas en cualquiera de tus páginas para empezar.','report-empty'));
 const groups=new Map();rows.forEach(row=>{if(!groups.has(row.page.id))groups.set(row.page.id,{page:row.page,rows:[]});groups.get(row.page.id).rows.push(row)});
 Array.from(groups.values()).sort((a,b)=>a.page.title.localeCompare(b.page.title,'es')).forEach(({page,rows})=>{
  const stats=EspacioReports.summary(rows),card=el('button',undefined,'report-page-card');card.type='button';
  card.append(el('strong',(page.icon||'📄')+' '+(page.title||'Sin título')),el('span',stats.done+' de '+stats.total+' completadas · '+stats.percent+'%'),progressBar(stats.done,stats.total,'Avance de '+(page.title||'Sin título')));
  card.title='Ver tareas de esta página';card.onclick=()=>{$('#report-page').value=page.id;reportLimit=50;renderReportTasks();$('#report-page').focus();$('#report-page').scrollIntoView({block:'center',behavior:'smooth'})};byPage.append(card);
 });
}
function renderReportTasks(){
 const rows=EspacioReports.filter(EspacioReports.collect(pages),{page:$('#report-page').value,status:$('#report-status').value,query:$('#report-search').value,order:$('#report-order').value});
 $('#report-count').textContent=rows.length+' '+(rows.length===1?'tarea':'tareas')+' · mostrando '+Math.min(reportLimit,rows.length);
 const list=$('#report-task-list');list.replaceChildren();
 if(!rows.length)list.append(el('p','No hay tareas que coincidan con los filtros.','report-empty'));
 rows.slice(0,reportLimit).forEach(({page,block,index,level,ancestors,completedAt})=>{
  const row=el('div',undefined,'report-task'+(block.checked?' is-complete':''));
  const check=el('input');check.type='checkbox';check.checked=!!block.checked;check.setAttribute('aria-label',(block.checked?'Reabrir tarea: ':'Completar tarea: ')+(block.text||'Sin título'));
  check.onchange=()=>{EspacioReports.complete(block,check.checked);changed(page);renderReportSummary();renderReportTasks();$('#drag-announcement').textContent=block.checked?'Tarea completada y reporte actualizado.':'Tarea pendiente y reporte actualizado.'};
  const detail=el('div',undefined,'report-task-detail'),open=el('button',block.text||'Tarea sin título','report-task-title');open.type='button';
  open.onclick=()=>{openPage(page.id);const target=$('#blocks').children[index];target?.scrollIntoView({behavior:'smooth',block:'center'});target?.querySelector('.editable')?.focus({preventScroll:true})};
  detail.append(open,el('small',(page.icon||'📄')+' '+(page.title||'Sin título')+(level?' / '+ancestors.join(' / '):'')));
  const meta=el('div',undefined,'report-task-meta');meta.append(el('span',block.checked?'Completada':'Pendiente','report-badge '+(block.checked?'complete':'pending')));
  if(block.checked){const date=el('small',completedAt===null?'Sin fecha registrada':new Date(completedAt).toLocaleString('es-CO',{timeZone:'America/Bogota',year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}));meta.append(date)}
  let href='';try{href=taskUrl(block.link||'')}catch{}
  if(href){const link=el('a','↗ Documento','task-document-link');link.href=href;link.target='_blank';link.rel='noopener noreferrer';meta.append(link)}
  row.append(check,detail,meta);list.append(row);
 });
 $('#report-more').hidden=rows.length<=reportLimit;
}
$('#reports-open').onclick=showReports;
['#report-page','#report-status','#report-order'].forEach(selector=>{$(selector).onchange=()=>{reportLimit=50;renderReportTasks()}});
$('#report-search').oninput=()=>{reportLimit=50;renderReportTasks()};
$('#report-more').onclick=()=>{reportLimit+=50;renderReportTasks()};
let editingTaskLink = null;
function taskUrl(value) {
 const text=value.trim();
 if(!text)return '';
 // Accept full web addresses and explicit local paths; do not guess domains.
 if(!/^https?:\/\//i.test(text)&&!text.startsWith('/')&&!text.startsWith('./')&&!text.startsWith('../')&&!text.startsWith('#'))throw Error('Usa una dirección https://… o una ruta de Guagua como /guagua/…');
 const url=new URL(text,location.href);
 if(!['http:','https:'].includes(url.protocol)||url.username||url.password||url.href.length>2048)throw Error('Introduce un enlace HTTP o HTTPS válido, sin credenciales.');
 return url.href;
}
function taskLinkTools(b) {
 const tools=el('span',undefined,'task-link-tools');
 let href='';try{href=taskUrl(b.link||'')}catch{}
 if(href){const link=el('a','↗ Abrir','task-document-link');link.href=href;link.target='_blank';link.rel='noopener noreferrer';link.title=href;link.setAttribute('aria-label','Abrir documento de la tarea: '+(b.text||'Sin título'));tools.append(link)}
 const edit=el('button',href?'✎':'＋ Enlace','task-link-edit');edit.type='button';edit.title=href?'Editar enlace de la tarea':'Añadir enlace a la tarea';edit.setAttribute('aria-label',edit.title);edit.onclick=()=>{
  editingTaskLink={block:b,page:current()};$('#task-link-url').value=b.link||'';$('#task-link-error').textContent='';$('#task-link-remove').hidden=!b.link;$('#task-link-dialog').showModal();$('#task-link-url').focus();
 };tools.append(edit);if(EspacioOrder.level(b)<5){const sub=el('button','＋ Subtarea','task-link-edit');sub.type='button';sub.title='Añadir una subtarea';sub.onclick=()=>addSubtask(current().blocks.indexOf(b));tools.append(sub)}return tools;
}
$('#task-link-form').onsubmit=e=>{
 e.preventDefault();if(!editingTaskLink)return;
 try{const link=taskUrl($('#task-link-url').value);editingTaskLink.block.link=link;editingTaskLink.page.updated=new Date().toISOString();changed();$('#task-link-dialog').close();renderBlocks()}
 catch(error){$('#task-link-error').textContent=error.message;$('#task-link-url').focus()}
};
$('#task-link-remove').onclick=()=>{if(!editingTaskLink)return;delete editingTaskLink.block.link;changed();$('#task-link-dialog').close();renderBlocks()};
$('#task-link-cancel').onclick=()=>$('#task-link-dialog').close();
$('#task-link-dialog').addEventListener('close',()=>{editingTaskLink=null});
function renderChildren(){const box=$('#children');box.replaceChildren();pages.filter(p=>!p.deleted&&p.parent===active).forEach(p=>{const b=el('div',undefined,'child-card');b.setAttribute('role','link');b.tabIndex=0;b.dataset.pageId=p.id;const grip=el('button','⠿','drag-grip');grip.title='Arrastrar página';bindDrag(grip,b,'page',p.id);b.append(grip);b.onkeydown=e=>{if(e.target===b&&e.key==='Enter')openPage(p.id)};b.append(el('span',p.icon),el('strong',p.title||'Sin título'),el('small','Abrir página →'));b.onclick=()=>openPage(p.id);box.append(b)})}
function renderBlocks(focus=-1){const box=$('#blocks');EspacioOrder.normalize(current().blocks);box.replaceChildren();current().blocks.forEach((b,i)=>{
 const row=el('div',undefined,'block '+b.type+(b.checked?' done':''));const handle=el('button','⠿','handle');handle.title='Arrastra para mover · Clic para ver opciones';handle.classList.add('drag-grip');handle.setAttribute('aria-label','Mover bloque o abrir sus opciones');row.dataset.blockIndex=i;if(b.type==='todo'){row.style.marginLeft=(EspacioOrder.level(b)*24)+'px';row.setAttribute('aria-label',EspacioOrder.level(b)?'Subtarea de nivel '+EspacioOrder.level(b):'Tarea');}bindDrag(handle,row,'block',i);handle.onclick=()=>blockMenu(handle,i);row.append(handle);
 if(b.type==='todo'){const check=el('input');check.type='checkbox';check.checked=!!b.checked;check.setAttribute('aria-label','Completar tarea');check.onchange=()=>{EspacioReports.complete(b,check.checked);row.classList.toggle('done',b.checked);changed()};row.append(check)}
 if(b.type==='bullet')row.append(el('span','•'));if(b.type==='callout')row.append(el('span','💡'));
 if(b.type!=='divider'){const edit=el('div',b.text,'editable');edit.contentEditable='true';edit.setAttribute('role','textbox');edit.setAttribute('aria-label',types[b.type]);edit.dataset.placeholder='Escribe algo o pulsa / para añadir un bloque…';edit.oninput=()=>{b.text=edit.textContent;changed()};edit.onpaste=e=>{e.preventDefault();const text=e.clipboardData.getData('text/plain');const sel=window.getSelection();if(!sel.rangeCount)return;const range=sel.getRangeAt(0);range.deleteContents();const node=document.createTextNode(text);range.insertNode(node);range.setStartAfter(node);range.collapse(true);sel.removeAllRanges();sel.addRange(range);b.text=edit.textContent;changed()};edit.onkeydown=e=>{
 if(e.key==='Tab'&&b.type==='todo'){e.preventDefault();changeTaskLevel(i,e.shiftKey?-1:1);return}
 if(e.key==='/'&&!edit.textContent){e.preventDefault();blockMenu(edit,i)}
 if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();if(b.type==='todo'&&!edit.textContent&&EspacioOrder.level(b)>0){changeTaskLevel(i,-1);return}const next=block(['todo','bullet'].includes(b.type)?b.type:'text');if(b.type==='todo')next.indent=EspacioOrder.level(b);const index=EspacioOrder.end(current().blocks,i);current().blocks.splice(index,0,next);changed();renderBlocks(index);return}
 if(e.key==='Backspace'&&!edit.textContent&&current().blocks.length>1){e.preventDefault();if(b.type==='todo'&&EspacioOrder.level(b)>0){changeTaskLevel(i,-1);return}removeTaskBlock(i);renderBlocks(Math.max(0,i-1))}
 };row.append(edit)}if(b.type==='todo')row.append(taskLinkTools(b));box.append(row);
 });if(focus>=0){const row=box.children[focus];row?.querySelector('.editable')?.focus()}}
function menu(anchor,items){const pop=$('#popover');pop.replaceChildren();items.forEach(([label,fn])=>{const b=el('button',label);b.onclick=()=>{pop.hidden=true;fn()};pop.append(b)});pop.hidden=false;const r=anchor.getBoundingClientRect();pop.style.left=Math.max(8,Math.min(r.left,innerWidth-260))+'px';pop.style.top=Math.max(8,Math.min(r.bottom+5,innerHeight-pop.offsetHeight-10))+'px'}
function changeTaskLevel(i,delta){if(EspacioOrder.indentTask(current().blocks,i,delta)){changed();renderBlocks(i)}else{$('#drag-announcement').textContent=delta>0?'La subtarea necesita una tarea anterior y admite hasta cinco niveles.':'La tarea ya está en el nivel principal.'}}
function addSubtask(i){const a=current().blocks,level=EspacioOrder.level(a[i]);if(level>=5)return;const index=EspacioOrder.end(a,i);a.splice(index,0,{...block('todo'),indent:level+1});changed();renderBlocks(index)}
function removeTaskBlock(i){const a=current().blocks,end=EspacioOrder.end(a,i);for(let j=i+1;j<end;j++)a[j].indent=Math.max(0,EspacioOrder.level(a[j])-1);a.splice(i,1);EspacioOrder.normalize(a);changed();renderBlocks()}
function blockMenu(anchor,i){
 const a=current().blocks,b=a[i];
 const taskActions=b.type==='todo'?[['＋ Añadir subtarea',()=>addSubtask(i)],['→ Aumentar nivel · Tab',()=>changeTaskLevel(i,1)],['← Reducir nivel · Shift+Tab',()=>changeTaskLevel(i,-1)]]:[];
 menu(anchor,[...taskActions,...Object.entries(types).map(([type,label])=>[label,()=>{a[i].type=type;EspacioOrder.normalize(a);changed();renderBlocks(i)}]),
 ['↑ Mover arriba',()=>{let previous=i-1;while(previous>=0&&EspacioOrder.level(a[previous])>EspacioOrder.level(b))previous--;if(previous>=0&&EspacioOrder.moveBlock(a,i,previous,false)){changed();renderBlocks(previous)}}],
 ['↓ Mover abajo',()=>{const next=EspacioOrder.end(a,i);if(next<a.length&&EspacioOrder.moveBlock(a,i,next,true)){changed();renderBlocks()}}],
 ['Duplicar bloque',()=>{const end=EspacioOrder.end(a,i);a.splice(end,0,...structuredClone(a.slice(i,end)));changed();renderBlocks(end)}],['Eliminar bloque',()=>removeTaskBlock(i)]]);
}
function duplicateProject(id){
 try{
  const copies=EspacioOrder.duplicateProject(pages,id,uid);if(!copies.length)return;
  if(pages.length+copies.length>500)throw Error('La copia supera el límite de 500 páginas. Reduce el número de páginas antes de duplicar este proyecto.');
  const candidate=pages.slice(),position=candidate.findIndex(p=>p.id===id)+1;
  candidate.splice(position,0,...copies);
  if(new TextEncoder().encode(JSON.stringify({version,pages:candidate})).length>2000000)throw Error('La copia supera el límite de 2 MB del espacio. Exporta y reduce contenido antes de duplicar.');
  pages=candidate;openPage(copies[0].id);changed();
  $('#drag-announcement').textContent='Proyecto duplicado con '+(copies.length-1)+' subpáginas. Puedes cambiar el nombre de la copia.';
 }catch(error){alert(error.message)}
}
function createPage(title='Sin título',icon='📄',parent=null,blocks=[block('text')]){const p=makePage(title,icon,parent,blocks);pages.push(p);openPage(p.id);changed();if(title==='Sin título')$('#title').focus();return p}
function listing(trash=false){$('#reports').hidden=true;$('#reports-open').classList.remove('active');$('#favorite').hidden=true;$('#document').hidden=true;$('#cover').hidden=true;$('#listing').hidden=false;$('#listing').replaceChildren(el('h1',trash?'Papelera':'Todas las páginas'),el('p',trash?'Puedes restaurar tus páginas cuando quieras.':'Un lugar para todo lo que estás construyendo.'));$('#breadcrumb').textContent=trash?'Papelera':'Tu espacio';const list=pages.filter(p=>!!p.deleted===trash);if(!list.length)$('#listing').append(el('p','No hay páginas aquí.','muted'));list.forEach(p=>{const row=el('div',undefined,'listing-row');const b=el('button',(p.icon||'📄')+'  '+(p.title||'Sin título'));b.onclick=()=>{if(!trash)openPage(p.id)};row.append(b);if(trash){const restore=el('button','Restaurar');restore.onclick=()=>{p.deleted=false;if(!pages.some(x=>x.id===p.parent&&!x.deleted))p.parent=null;changed();navigation();listing(true)};row.append(restore)}else row.append(el('small',new Date(p.updated).toLocaleDateString('es-CO')));$('#listing').append(row)});document.body.classList.remove('mobile-nav')}
function downloadProjects(projectPages,name){const blob=new Blob([JSON.stringify({format:'guagua-espacio',schemaVersion:1,pages:projectPages},null,2)],{type:'application/json'});const a=el('a');a.href=URL.createObjectURL(blob);a.download=name+'.json';a.click();setTimeout(()=>URL.revokeObjectURL(a.href),1000)}
function exportAll(){downloadProjects(pages,'guagua-espacio')}
function exportProject(id){const group=EspacioPrint.project(pages,id);if(!group.length)return;const projectPages=group.map(({page},index)=>({...page,...(index===0?{parent:null}:{})}));downloadProjects(projectPages,'guagua-proyecto-'+(group[0].page.title||'sin-titulo').replace(/[<>:"/\\|?*\x00-\x1F]/g,'-').slice(0,100))}
let importData=null,importRead=0;
function selectedImports(){return Array.from(document.querySelectorAll('#import-projects input:checked'),input=>input.value)}
function importSelection(){
 $('#import-confirm').disabled=true;$('#import-error').textContent='';
 if(!importData)return;
 try{
  if(blocked)throw Error('Hay cambios en otra pestaña. Exporta tus cambios actuales y recarga antes de importar.');
  const copies=EspacioImport.prepare(importData,selectedImports(),pages,uid,version);
  const tasks=copies.reduce((sum,p)=>sum+p.blocks.filter(b=>b.type==='todo').length,0);
  $('#import-summary').textContent=selectedImports().length+' proyectos · '+copies.length+' páginas · '+tasks+' tareas y subtareas.'+(importData.skipped?' Se excluyen '+importData.skipped+' páginas de la papelera.':'')+(importData.orphans?' Las páginas sin su contenedora original se importan en el nivel principal.':'');
  $('#import-confirm').disabled=false;
 }catch(error){$('#import-summary').textContent='';$('#import-error').textContent=error.message}
}
$('#import-open').onclick=()=>{importRead++;importData=null;$('#import-file').value='';$('#import-error').textContent='';$('#import-preview').hidden=true;$('#import-confirm').disabled=true;$('#import-projects').replaceChildren();$('#import-dialog').showModal()};
$('#import-file').onchange=async()=>{
 const read=++importRead,file=$('#import-file').files[0];importData=null;$('#import-preview').hidden=true;$('#import-confirm').disabled=true;$('#import-error').textContent='';if(!file)return;
 try{
  if(!/\.json$/i.test(file.name))throw Error('Selecciona un archivo con extensión .json exportado por Guagua Espacio.');
  if(file.size>10000000)throw Error('El archivo supera el límite de 10 MB para importación.');
  const text=await file.text();if(read!==importRead||!$('#import-dialog').open)return;
  importData=EspacioImport.read(text);const box=$('#import-projects');box.replaceChildren();
  importData.roots.forEach(root=>{const label=el('label',undefined,'import-project-option'),check=el('input');check.type='checkbox';check.value=root.id;check.checked=true;check.onchange=importSelection;const content=el('span');content.append(el('strong',(root.icon||'📄')+' '+(root.title||'Sin título')),el('small',EspacioPrint.project(importData.pages,root.id).length+' páginas, incluidas sus secciones'));label.append(check,content);box.append(label)});
  $('#import-preview').hidden=false;importSelection();
 }catch(error){if(read===importRead)$('#import-error').textContent=error.message}
};
$('#import-confirm').onclick=()=>{
 try{
  if(!importData)return;if(blocked)throw Error('Recarga el espacio después de exportar tus cambios actuales antes de importar.');
  const copies=EspacioImport.prepare(importData,selectedImports(),pages,uid,version);
  pages.push(...copies);$('#import-dialog').close();openPage(copies[0].id);changed();$('#drag-announcement').textContent='Importación completada: '+copies.length+' páginas añadidas. Se guardarán automáticamente.';
 }catch(error){$('#import-error').textContent=error.message}
};
$('#import-cancel').onclick=()=>$('#import-dialog').close();
$('#import-dialog').addEventListener('close',()=>{importRead++;importData=null});
$('#title').oninput=()=>{current().title=$('#title').value;resizeTitle();navigation();$('#breadcrumb').textContent='Privado / '+(current().title||'Sin título');changed()};
$('#add-block').onclick=()=>{current().blocks.push(block('text'));changed();renderBlocks(current().blocks.length-1)};
$('#favorite').onclick=()=>{if(!current())return;current().favorite=!current().favorite;$('#favorite').textContent=current().favorite?'★':'☆';navigation();changed()};
$('#page-icon').onclick=()=>menu($('#page-icon'),['🌿','📓','📚','🎯','💡','🗓️','✏️','🚀','🏡','📄'].map(icon=>[icon,()=>{current().icon=icon;$('#page-icon').textContent=icon;navigation();changed()}]));
$('#change-cover').onclick=()=>{current().cover=((current().cover||0)+1)%palettes.length;$('#cover').style.background=palettes[current().cover];changed()};
$('#new-page').onclick=$('#new-bottom').onclick=()=>createPage();
$('#home').onclick=()=>listing();$('#trash').onclick=()=>listing(true);
$('#toggle-sidebar').onclick=()=>{if(innerWidth<=600)document.body.classList.toggle('mobile-nav');else document.body.classList.toggle('sidebar-hidden')};
$('#more').onclick=()=>{const p=current();menu($('#more'),[...(p&&!p.deleted&&!$('#document').hidden?[
 ['＋ Añadir subpágina',()=>createPage('Sin título','📄',p.id)],['Duplicar proyecto con subpáginas',()=>duplicateProject(p.id)],['↓ Exportar este proyecto',()=>exportProject(p.id)],['Duplicar solo esta página',()=>createPage(p.title+' (copia)',p.icon,p.parent,structuredClone(p.blocks))],['Mover a…',()=>menu($('#more'),[['Nivel principal',()=>{p.parent=null;changed();navigation()}],...pages.filter(x=>!x.deleted&&x.id!==p.id).filter(x=>{let a=x;const seen=new Set();while(a){if(a.id===p.id||seen.has(a.id))return false;seen.add(a.id);a=pages.find(y=>y.id===a.parent)}return true}).map(x=>[x.icon+' '+x.title,()=>{p.parent=x.id;changed();openPage(p.id)}])])],['Mover a la papelera',()=>{const ids=new Set([p.id]);let previous;do{previous=ids.size;pages.forEach(x=>{if(ids.has(x.parent))ids.add(x.id)})}while(ids.size!==previous);pages.forEach(x=>{if(ids.has(x.id))x.deleted=true});changed();navigation();listing(true)}]]:[]),['↓ Exportar todo como JSON',exportAll],['Reintentar guardado',()=>{if(blocked){alert('Hay cambios en otra pestaña. Exporta tus cambios antes de recargar.');return}save()}]])};
function search(){const q=$('#search-input').value.toLocaleLowerCase();const results=$('#search-results');results.replaceChildren();const matches=pages.filter(p=>!p.deleted&&(p.title+' '+p.blocks.map(b=>b.text).join(' ')).toLocaleLowerCase().includes(q));matches.forEach(p=>{const b=el('button',p.icon+'  '+(p.title||'Sin título'));b.onclick=()=>{$('#search-dialog').close();openPage(p.id)};results.append(b)});if(!matches.length)results.append(el('p','No encontramos páginas con ese texto.','muted'))}
$('#search-open').onclick=()=>{$('#search-dialog').showModal();search();$('#search-input').focus()};$('#search-input').oninput=search;$('#search-close').onclick=()=>$('#search-dialog').close();
const templates=[['📓','Diario docente','Reflexiona sobre tu día y prepara el siguiente.',[block('callout','Cada día es una oportunidad para aprender algo nuevo.'),block('h2','Hoy en el aula'),block('text',''),block('h2','Lo que funcionó'),block('bullet',''),block('h2','Para mañana'),block('todo','Preparar materiales')]],['🎯','Proyecto','De la primera idea a los siguientes pasos.',[block('h2','El propósito'),block('text','¿Qué queremos lograr?'),block('h2','Próximos pasos'),block('todo','Definir el objetivo'),block('todo','Organizar recursos'),block('todo','Revisar avances')]],['🗓️','Plan semanal','Una semana con intención y claridad.',[block('callout','Mi prioridad esta semana es…'),...['Lunes','Martes','Miércoles','Jueves','Viernes'].flatMap(d=>[block('h2',d),block('todo','')])]]];
templates.forEach(([icon,title,desc,blocks])=>{const b=el('button',icon+'  '+title);b.append(el('small',desc));b.onclick=()=>{$('#template-dialog').close();createPage(title,icon,null,structuredClone(blocks))};$('#template-options').append(b)});
$('#templates').onclick=()=>$('#template-dialog').showModal();$('#template-close').onclick=()=>$('#template-dialog').close();
document.addEventListener('click',e=>{if(!e.target.closest('#popover')&&!e.target.closest('button')&&!e.target.closest('.editable'))$('#popover').hidden=true});
document.addEventListener('keydown',e=>{if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='k'){e.preventDefault();$('#search-open').click()}if((e.ctrlKey||e.metaKey)&&e.key==='s'){e.preventDefault();if(!$('#save-button').disabled)manualSave()}if(e.key==='Escape')$('#popover').hidden=true});
window.addEventListener('hashchange',()=>{if(location.hash==='#reportes')showReports();else openPage(location.hash.slice(1))});
window.addEventListener('beforeunload',e=>{if(dirty||saving){e.preventDefault();e.returnValue=''}});
async function init(){try{const r=await fetch('api.php');if(!r.ok)throw Error('No se pudo abrir el espacio. Comprueba tu sesión.');const data=await r.json();version=data.version;pages=data.pages||[];if(data.pages===null){const welcome=makePage('Mi espacio','🌿',null,[block('callout','Un lugar para tus ideas, tus proyectos y todo lo que quieres hacer posible.'),block('h1','Todo empieza con una idea.'),block('text','Este es tu rincón dentro de Guagua. Reúne lo importante, organiza tus clases y dale espacio a lo que viene.'),block('h2','Hazlo tuyo'),block('todo','Escribe tu primera idea'),block('todo','Crea una página para tu próximo proyecto'),block('todo','Explora las plantillas del espacio'),block('divider'),block('h2','Un poco de inspiración'),block('quote','Las grandes cosas se construyen con pequeños pasos, todos los días.')]);welcome.favorite=true;pages=[welcome,makePage('Mis proyectos','🎯',welcome.id,[block('h1','Ideas en movimiento'),block('todo','Definir mi próximo proyecto')]),makePage('Diario docente','📓',welcome.id,structuredClone(templates[0][3])),makePage('Recursos e ideas','💡',null,[block('h2','Para volver a encontrar'),block('bullet','Guarda aquí ideas, referencias y recursos para tus clases.')])];dirty=true}const target=pages.find(p=>p.id===location.hash.slice(1)&&!p.deleted)||pages.find(p=>!p.deleted);if(location.hash==='#reportes'){navigation();showReports()}else if(target)openPage(target.id);else{navigation();listing()}status('Todos los cambios guardados');$('#save-button').disabled=false;$('#print-open').disabled=false;$('#import-open').disabled=false;if(dirty)save()}catch(e){status('No se pudo cargar');$('#blocks').append(el('p',e.message));document.querySelectorAll('button,textarea').forEach(b=>b.disabled=true)}}init();

