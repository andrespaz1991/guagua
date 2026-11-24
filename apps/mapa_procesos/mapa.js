(() => {
  'use strict';
  const root = document.getElementById('guagua-map');
  if (!root || !window.GUAGUA_MAPA) return;
  const data = window.GUAGUA_MAPA;
  const $ = id => root.querySelector('#gm-' + id);
  const byId = Object.assign(Object.create(null),Object.fromEntries(data.processes.map(p => [p.id, p])));
  const branches = Object.assign(Object.create(null),Object.fromEntries(data.branches.map(b => [b.id, b])));
  const modules = Object.create(null);
  data.processes.forEach(p=>p.links.forEach(([name,path,available],index)=>{
    const id='modulo:'+p.id+':'+index;
    modules[id]={id,name,path,available:available!==false,process:p.id,branch:p.branch};
  }));
  const labels = {core:'Fundamental',merge:'A unificar',gap:'Brecha del flujo',optional:'Complementario'};
  const targetLabels = {core:'Conservar y conectar',merge:'Unificar',gap:'Incorporar o corregir',optional:'Apoyo opcional'};
  const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const normalize = value => String(value).normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();
  const basePath = location.pathname.includes('/apps/mapa_procesos/') ? location.pathname.split('/apps/mapa_procesos/')[0] + '/' : root.dataset.base;
  const base = new URL(basePath || '/guagua/', location.protocol === 'file:' ? 'http://localhost/' : location.origin);
  const moduleUrl = path => new URL(path, base).href;
  let state = {mode:'actual',branch:null,selected:'root',filter:'all',openBranches:new Set(),openProcesses:new Set()};
  let view = {scale:1,x:0,y:0,width:650,height:680};
  let positions = new Map();
  let treeChildren = new Map();
  let drag = null;
  let nodeClickTimer = null;

  function mapNodeUrl(id){
    const url=new URL(location.href),q=new URLSearchParams({vista:'actual'});
    const module=modules[id],process=module?byId[module.process]:byId[id];
    const branch=process?.branch||(branches[id]?id:null);
    const openBranches=new Set(state.openBranches),openProcesses=new Set(state.openProcesses);
    if(branch){q.set('rama',branch);openBranches.add(branch);}
    if(id!=='root')q.set('proceso',id);
    if(process?.links.length)openProcesses.add(process.id);
    q.set('abiertas',[...openBranches].join(','));
    if(openProcesses.size)q.set('desplegados',[...openProcesses].join(','));
    url.hash=q.toString();
    return url.href;
  }
  function nodeDestination(id){
    const module=modules[id];
    if(module?.available)return {url:moduleUrl(module.path),name:module.name};
    const process=byId[id],link=process?.links.find(l=>l[2]!==false);
    if(link)return {url:moduleUrl(link[1]),name:link[0]};
    return {url:mapNodeUrl(id),name:process?.name||branches[id]?.name||'Guagua'};
  }
  function cancelNodeClick(){clearTimeout(nodeClickTimer);nodeClickTimer=null;}
  function openNodeWindow(id){
    if(id!=='root'&&!byId[id]&&!branches[id]&&!modules[id])return;
    cancelNodeClick();
    const destination=nodeDestination(id);
    window.open(destination.url,'_blank','popup=yes,width=1280,height=860,resizable=yes,scrollbars=yes,noopener,noreferrer');
    announce('Abrir en ventana nueva: '+destination.name);
  }
  function activateNode(id){
    if(id==='root')overview();
    else if(branches[id])openBranch(id);
    else select(id);
  }

  function readLocation() {
    const q = new URLSearchParams(location.hash.slice(1));
    const mode = q.get('vista'), branch = q.get('rama'), selected = q.get('proceso'), filter = q.get('condicion');
    const openBranches=new Set((q.get('abiertas')||'').split(',').filter(id=>branches[id]));
    const openProcesses=new Set((q.get('desplegados')||'').split(',').filter(id=>byId[id]));
    const validSelected=byId[selected]||branches[selected]||modules[selected];
    state = {
      mode:['actual','propuesto','modulos'].includes(mode) ? mode : 'actual',
      branch:branches[branch] ? branch : null,
      selected:validSelected ? selected : 'root',
      filter:['core','merge','gap','optional'].includes(filter) ? filter : 'all',
      openBranches,openProcesses
    };
    if (state.mode === 'actual') {
      const owner=modules[state.selected] ? byId[modules[state.selected].process] : byId[state.selected];
      if(owner)state.branch=owner.branch;
      if(state.branch && !q.has('abiertas'))state.openBranches.add(state.branch);
      if(owner)state.openBranches.add(owner.branch);
      if(modules[state.selected])state.openProcesses.add(owner.id);
    }
  }
  function save() {
    const q = new URLSearchParams({vista:state.mode});
    if (state.branch) q.set('rama',state.branch);
    if (state.selected !== 'root') q.set('proceso',state.selected);
    if (state.filter !== 'all') q.set('condicion',state.filter);
    q.set('abiertas',[...state.openBranches].join(','));
    if(state.openProcesses.size)q.set('desplegados',[...state.openProcesses].join(','));
    history.replaceState(null,'','#' + q.toString());
  }
  function announce(text) { $('announcement').textContent = text; }
  function badge(kind, target = false) { return `<span class="gm-badge ${kind}"><i class="gm-dot ${kind}"></i>${escape((target ? targetLabels : labels)[kind])}</span>`; }
  function children(branch) { return data.processes.filter(p => p.branch === branch && (state.filter === 'all' || p.kind === state.filter)); }

  function render(options={}) {
    root.querySelectorAll('[data-mode]').forEach(b => b.setAttribute('aria-pressed',String(b.dataset.mode === state.mode)));
    root.querySelectorAll('[data-filter]').forEach(b => b.setAttribute('aria-pressed',String(b.dataset.filter === state.filter)));
    $('canvas').hidden = state.mode === 'modulos';
    $('caption').hidden = state.mode === 'modulos';
    $('directory').hidden = state.mode !== 'modulos';
    $('filters').hidden = state.mode === 'propuesto';
    if (state.mode === 'modulos') renderDirectory();
    else renderGraph(options);
    renderDetail();
  }

  function route(id) {
    const m=modules[id],p=m?byId[m.process]:byId[id];
    const branch=p?.branch||(branches[id]?id:null);
    const path=[{id:'root',name:'Guagua'}];
    if(branch)path.push({id:branch,name:branches[branch].short});
    if(p)path.push({id:p.id,name:p.name});
    if(m)path.push({id:m.id,name:m.name});
    return path;
  }
  function renderBreadcrumb() {
    $('breadcrumb').innerHTML=route(state.selected).map((part,index,parts)=>{
      const action=part.id==='root'?'data-overview':branches[part.id]?`data-branch="${part.id}"`:modules[part.id]?`data-module="${part.id}"`:`data-select="${part.id}"`;
      return `${index?'<span class="gm-divider" aria-hidden="true">/</span>':''}<button type="button" ${action}${index===parts.length-1?' aria-current="page"':''}>${escape(part.name)}</button>`;
    }).join('');
  }

  function node(id,x,y,title,meta,options={}) {
    const p = byId[id],m=modules[id];
    const width = options.width || (options.root ? 205 : (options.branch ? 285 : (options.flow ? 240 : 285)));
    const kind = p?.kind || (m?'module':'');
    const b = document.createElement('button');
    b.type = 'button';b.className = `gm-node ${kind} ${options.root ? 'is-root' : ''} ${options.branch ? 'is-branch' : ''} ${id === state.selected ? 'is-selected' : ''}`;
    b.dataset.node = id;b.style.left = x + 'px';b.style.top = y + 'px';b.style.width = width + 'px';
    b.id='gm-node-'+id.replace(/[^a-z0-9_-]/gi,'-');
    b.setAttribute('aria-label',title + '. ' + meta + (options.expandable ? '. Desplegar hijos.' : '. Ver detalle.'));
    b.setAttribute('aria-pressed',String(id === state.selected));
    b.title='Un clic: desplegar o seleccionar. Doble clic: abrir '+nodeDestination(id).name+' en una ventana nueva.';
    b.setAttribute('aria-keyshortcuts','Shift+Enter');
    if(options.expandable)b.setAttribute('aria-expanded',String(options.expanded));
    b.innerHTML = `<span class="gm-node-top"><span class="gm-node-name">${escape(title)}</span></span><span class="gm-node-meta">${p ? `<i class="gm-dot ${p.kind}"></i>` : ''}${escape(meta)}</span>${options.sub ? `<span class="gm-node-sub">${escape(options.sub)}</span>` : ''}`;
    $('nodes').append(b);
    let toggle=null;
    if(options.expandable){
      toggle=document.createElement('button');toggle.type='button';toggle.className='gm-node-toggle';toggle.dataset.toggle=id;
      toggle.textContent=options.expanded?'−':'+';
      toggle.setAttribute('aria-label',(options.expanded?'Contraer ':'Desplegar ')+title);
      toggle.setAttribute('aria-expanded',String(options.expanded));
      $('nodes').append(toggle);
    }
    positions.set(id,{x,y,width,height:b.offsetHeight,button:b,toggle});
    placeNode(id,x,y);
  }

  function placeNode(id,x,y){
    const pos=positions.get(id);pos.x=x;pos.y=y;
    pos.button.style.left=x+'px';pos.button.style.top=y+'px';
    if(pos.toggle){pos.toggle.style.left=(x+pos.width-33)+'px';pos.toggle.style.top=(y+10)+'px';}
  }

  function renderTree(){
    const mobile=$('canvas').clientWidth<520;
    const visibleBranches=data.branches.filter(b=>children(b.id).length);
    const branchTrees=visibleBranches.map(b=>({id:b.id,kids:state.openBranches.has(b.id)?children(b.id).map(p=>({id:p.id,kids:state.openProcesses.has(p.id)?Object.values(modules).filter(m=>m.process===p.id).map(m=>({id:m.id,kids:[]})):[]})):[]}));
    const tree={id:'root',kids:branchTrees};
    treeChildren=new Map();
    function materialize(t,depth=0){
      treeChildren.set(t.id,t.kids.map(c=>c.id));
      const p=byId[t.id],b=branches[t.id],m=modules[t.id];
      const gaps=b?data.processes.filter(p=>p.branch===b.id&&p.kind==='gap').length:0;
      const title=p?.name||b?.name||m?.name||'Guagua';
      const meta=p?labels[p.kind]:b?`${children(b.id).length} procesos · ${gaps} ${gaps===1?'brecha':'brechas'}`:m?(m.available?'Pantalla existente':'Ruta por revisar'):'Sistema de gestión escolar';
      const expandable=!!b||!!(p&&p.links.length);
      const expanded=b?state.openBranches.has(b.id):!!(p&&state.openProcesses.has(p.id));
      const width=mobile?Math.max(180,$('canvas').clientWidth-38-depth*15):(depth===0?205:285);
      node(t.id,0,0,title,meta,{root:depth===0,branch:!!b,width,expandable,expanded});
      t.kids.forEach(c=>materialize(c,depth+1));
    }
    materialize(tree);
    const edges=[];
    if(mobile){
      let y=22;
      const layout=(t,depth=0)=>{placeNode(t.id,18+depth*15,y);y+=positions.get(t.id).height+22;t.kids.forEach(c=>{edges.push([t.id,c.id]);layout(c,depth+1);});};
      layout(tree);view.width=$('canvas').clientWidth;view.height=y+20;
      $('canvas').style.height=(view.height+65)+'px';
    }else{
      const measure=t=>{const size=positions.get(t.id);t.extent=Math.max(size.height,t.kids.reduce((n,c)=>n+measure(c),0)+Math.max(0,t.kids.length-1)*20);return t.extent;};
      measure(tree);let maxX=0;
      const layout=(t,depth=0,top=22)=>{
        const pos=positions.get(t.id),x=depth===0?28:330+(depth-1)*340;
        placeNode(t.id,x,top+(t.extent-pos.height)/2);maxX=Math.max(maxX,x+pos.width);
        const childHeight=t.kids.reduce((sum,c)=>sum+c.extent,0)+Math.max(0,t.kids.length-1)*20;
        let y=top+(t.extent-childHeight)/2;
        t.kids.forEach(c=>{edges.push([t.id,c.id]);layout(c,depth+1,y);y+=c.extent+20;});
      };
      layout(tree);view.width=maxX+28;view.height=tree.extent+44;$('canvas').style.height='';
    }
    renderBreadcrumb();
    $('count').textContent=visibleBranches.length+' ramas · '+(positions.size-1-visibleBranches.length)+' nodos desplegados';
    $('empty').hidden=visibleBranches.length>0;
    $('caption').innerHTML='<span>Un clic despliega; doble clic abre una ventana nueva.</span><span>Usa − para contraer y las migas para volver.</span>';
    return edges;
  }

  function renderGraph(options={}) {
    const previousMode=$('world').dataset.mode;
    const focused=document.activeElement?.dataset;
    positions = new Map();$('nodes').replaceChildren();$('edges').replaceChildren();
    let edgeList = [];
    if (state.mode === 'propuesto') {
      $('breadcrumb').innerHTML = '<span>Flujo recomendado</span><span class="gm-divider">/</span><span>Ciclo escolar completo</span>';
      $('count').textContent = data.flow.length + ' etapas';
      const narrow=$('canvas').clientWidth<520;
      data.flow.forEach((s,i) => node(s.id,narrow?34:s.x,narrow?22+i*142:s.y,s.title,targetLabels[byId[s.id].kind],{flow:true,sub:s.sub,width:narrow?$('canvas').clientWidth-68:240}));
      view.width=narrow?$('canvas').clientWidth:856;view.height=narrow?data.flow.length*142+25:670;
      $('canvas').style.height=narrow?(view.height+85)+'px':'';
      edgeList=narrow?data.flow.slice(1).map((s,i)=>[data.flow[i].id,s.id]):data.flowEdges;
      $('caption').innerHTML = '<span>Las flechas muestran dependencias propuestas; personas y oferta se preparan en paralelo.</span><span>Selecciona una etapa para explorarla.</span>';
    } else {
      edgeList=renderTree();
    }
    if (state.mode === 'propuesto') $('empty').hidden = true;
    $('world').style.width = view.width + 'px';$('world').style.height = view.height + 'px';
    $('edges').setAttribute('width',view.width);$('edges').setAttribute('height',view.height);
    drawEdges(edgeList);
    $('world').dataset.mode=state.mode;
    if(focused?.node)positions.get(focused.node)?.button.focus({preventScroll:true});
    if(focused?.toggle)positions.get(focused.toggle)?.toggle?.focus({preventScroll:true});
    if(state.mode==='actual'&&(options.focusId||state.openBranches.size))requestAnimationFrame(()=>reveal(options.focusId||state.selected));
    else if(previousMode!==state.mode||options.fitAll||!previousMode)requestAnimationFrame(fit);
    else transform();
  }

  function reveal(id){
    if(state.mode!=='actual')return;
    const pos=positions.get(id)||positions.get('root');if(!pos)return;
    const c=$('canvas');
    if(c.clientWidth<520){fit();return;}
    const immediate=(treeChildren.get(id)||[]).map(k=>positions.get(k));
    const family=[pos,...immediate];
    const parent=route(id).at(-2),parentPos=parent&&positions.get(parent.id);
    if(parentPos)family.push(parentPos);
    const ancestors=route(id).map(p=>positions.get(p.id)).filter(Boolean);
    const pathFamily=[...family,...ancestors];
    const pathWidth=Math.max(...pathFamily.map(p=>p.x+p.width))-Math.min(...pathFamily.map(p=>p.x));
    if((c.clientWidth-48)/pathWidth>=.72)family.push(...ancestors);
    const minX=Math.min(...family.map(p=>p.x)),maxX=Math.max(...family.map(p=>p.x+p.width));
    const scaleToFit=(c.clientWidth-48)/(maxX-minX);
    view.scale=Math.min(Math.max(view.scale,.82),Math.max(.72,Math.min(1,scaleToFit)));
    view.x=scaleToFit>=.72?(c.clientWidth-(maxX-minX)*view.scale)/2-minX*view.scale:30-pos.x*view.scale;
    view.y=(c.clientHeight-40)/2-(pos.y+pos.height/2)*view.scale;
    transform();
  }

  function drawEdges(edges) {
    const ns = 'http://www.w3.org/2000/svg';
    $('edges').innerHTML = '<defs><marker id="gm-arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse"><path d="M 0 1 L 9 5 L 0 9" fill="none" stroke="#819aa9" stroke-width="1.5"/></marker></defs>';
    for (const [from,to] of edges) {
      const a = positions.get(from), b = positions.get(to);if (!a || !b) continue;
      let d;
      if (state.mode === 'propuesto') {
        if (a.y === b.y && b.x-a.x < 350 && b.x > a.x) {
          d = `M${a.x+a.width},${a.y+52} H${b.x-7}`;
        } else {
          const sx = a.x+a.width/2, sy = a.y+a.height, ex = b.x+b.width/2;
          const mid = a.y===b.y ? sy+26 : sy+(b.y-sy)/2;
          const end = a.y===b.y ? b.y+b.height+5 : b.y-7;
          d = `M${sx},${sy} V${mid} H${ex} V${end}`;
        }
      } else if ($('canvas').clientWidth < 520) {
        const sx = a.x+8, sy = a.y+a.height;
        d = `M${sx},${sy} V${b.y+b.height/2} H${b.x}`;
      } else {
        const sx=a.x+a.width, sy=a.y+a.height/2, ex=b.x, ey=b.y+b.height/2, mid=sx+(ex-sx)/2;
        d=`M${sx},${sy} C${mid},${sy} ${mid},${ey} ${ex},${ey}`;
      }
      const path = document.createElementNS(ns,'path');
      path.dataset.to=to;
      path.setAttribute('d',d);
      path.setAttribute('class','gm-edge' + ((byId[to]?.kind === 'gap' || byId[from]?.kind === 'gap') && state.mode==='actual' ? ' gm-missing' : '') + (to===state.selected ? ' gm-highlight' : ''));
      if(state.mode==='propuesto') path.setAttribute('marker-end','url(#gm-arrow)');
      $('edges').append(path);
    }
  }

  function fit() {
    if (state.mode === 'modulos') return;
    const c=$('canvas');const padding=c.clientWidth<520?12:32;
    view.scale=Math.min(1.12,(c.clientWidth-padding*2)/view.width,(c.clientHeight-60)/view.height);
    view.x=(c.clientWidth-view.width*view.scale)/2;view.y=(c.clientHeight-36-view.height*view.scale)/2;
    transform();
  }
  function transform() {
    $('world').style.transform=`translate(${view.x}px,${view.y}px) scale(${view.scale})`;
    $('zoom-label').value=Math.round(view.scale*100)+' %';
  }
  function zoom(delta) {
    const next=Math.max(.35,Math.min(2,view.scale+delta)), c=$('canvas');
    const ratio=next/view.scale;view.x=c.clientWidth/2-(c.clientWidth/2-view.x)*ratio;view.y=c.clientHeight/2-(c.clientHeight/2-view.y)*ratio;view.scale=next;transform();
  }

  function renderDetail() {
    const d=$('detail'), selected=byId[state.selected], branch=branches[state.selected];
    const module=modules[state.selected];
    if(module){
      const owner=byId[module.process];
      d.innerHTML=`<button type="button" class="gm-button gm-mobile-back" data-back-map>Volver al mapa</button><div class="gm-eyebrow">MÓDULO DEL PROCESO</div><h2>${escape(module.name)}</h2><span class="gm-badge ${module.available?'':'gap'}">${module.available?'Pantalla existente':'Ruta por revisar'}</span><h3>Forma parte de</h3><div class="gm-related"><button type="button" data-select="${owner.id}">${escape(owner.name)}</button></div><h3>Situación del proceso</h3><p>${escape(owner.current)}</p><h3>Cambio recomendado</h3><p>${escape(owner.target)}</p>${module.available?`<h3>Abrir en Guagua</h3><div class="gm-module-links"><a class="gm-button gm-primary" href="${escape(moduleUrl(module.path))}" target="_blank" rel="noopener noreferrer">Abrir ${escape(module.name)}</a></div><p class="gm-note">La pantalla se abre en otra pestaña con los permisos de Guagua.</p>`:''}<h3>Evidencia del diagnóstico</h3><div class="gm-evidence">${owner.refs.map(r=>`<span>${escape(r)}</span>`).join('')}</div>`;
      return;
    }
    if (selected) {
      const incoming=data.dependencies.filter(e=>e[1]===selected.id), outgoing=data.dependencies.filter(e=>e[0]===selected.id);
      const related=(edges,index) => edges.map(e=>`<button type="button" data-select="${e[index]}"><span>${escape(byId[e[index]].name)}<small>${escape(e[2])}</small></span></button>`).join('');
      const validLinks=selected.links.filter(l=>l[2]!==false);
      d.innerHTML=`<button type="button" class="gm-button gm-mobile-back" data-back-map>Volver al mapa</button><div class="gm-eyebrow">${escape(branches[selected.branch].short)}</div><h2>${escape(selected.name)}</h2>${badge(selected.kind,state.mode==='propuesto')}<p class="gm-intro">${escape(selected.purpose)}</p><div class="gm-owner"><strong>Responsable</strong><span>${escape(selected.owner)}</span>${selected.priority ? `<strong>· ${selected.priority}</strong>`:''}</div><h3>Situación actual</h3><p>${escape(selected.current)}</p><h3>Cambio recomendado</h3><p>${escape(selected.target)}</p><h3>Pantallas actuales de referencia</h3><div class="gm-module-links">${validLinks.map(([name,path])=>`<a class="gm-button" href="${escape(moduleUrl(path))}" target="_blank" rel="noopener noreferrer"><span>${escape(name)}<small>Abre en otra pestaña</small></span></a>`).join('')}</div>${!validLinks.length?'<p>No hay una pantalla integral identificada para este proceso. La conexión está propuesta.</p>':''}${selected.links.some(l=>l[2]===false)?'<p class="gm-note">Alguna ruta de referencia no tiene una entrada disponible en el proyecto local.</p>':''}${incoming.length?`<h3>Necesita o se apoya en</h3><div class="gm-related">${related(incoming,0)}</div>`:''}${outgoing.length?`<h3>Conecta con</h3><div class="gm-related">${related(outgoing,1)}</div>`:''}<h3>Evidencia del diagnóstico</h3><div class="gm-evidence">${selected.refs.map(r=>`<span>${escape(r)}</span>`).join('')}</div><div style="margin-top:20px"><button type="button" class="gm-button" data-branch="${selected.branch}">Ver toda esta rama</button></div>`;
      return;
    }
    if (branch) {
      const items=data.processes.filter(p=>p.branch===branch.id);
      d.innerHTML=`<div class="gm-eyebrow">RAMA DEL SISTEMA</div><h2>${escape(branch.name)}</h2><p>${escape(branch.summary)}</p><div class="gm-owner"><strong>Responsable</strong><span>${escape(branch.owner)}</span></div><h3>Organización recomendada</h3><p>${escape(branch.target)}</p><h3>Procesos de la rama</h3><div class="gm-overview-list">${items.map(p=>`<button type="button" data-select="${p.id}"><span>${escape(p.name)}</span><i class="gm-dot ${p.kind}" aria-label="${labels[p.kind]}"></i></button>`).join('')}</div>`;
      return;
    }
    const gaps=data.processes.filter(p=>p.kind==='gap').length, merges=data.processes.filter(p=>p.kind==='merge').length;
    d.innerHTML=`<div class="gm-eyebrow">LECTURA DEL SISTEMA</div><h2>Un ciclo escolar completo</h2><p>Empieza por una rama del mapa. Cada proceso reúne su función, las pantallas existentes y sus conexiones.</p><h3>Lo que muestra el diagnóstico</h3><p><strong>${gaps} procesos con brechas</strong> y <strong>${merges} por unificar</strong> dentro del modelo revisado. Son condiciones del diseño, no mediciones de uso.</p><h3>Por dónde empezar</h3><div class="gm-overview-list"><button type="button" data-select="instalacion"><span>Revisar la instalación</span><i class="gm-dot gap"></i></button><button type="button" data-branch="apertura"><span>Recorrer la apertura del año</span><i class="gm-dot core"></i></button><button type="button" data-select="matricula"><span>Entender matrícula e inscripciones</span><i class="gm-dot gap"></i></button><button type="button" data-select="acudientes"><span>Conectar a las familias</span><i class="gm-dot gap"></i></button></div><h3>Cómo leer las conexiones</h3><p>En el mapa actual, cada rama agrupa procesos. El detalle permite saltar a sus dependencias. En el flujo recomendado, las flechas describen la secuencia propuesta.</p><p class="gm-note">La ausencia de uso no está demostrada. Una herramienta complementaria puede seguir siendo necesaria para la institución.</p>`;
  }

  function renderDirectory() {
    const query=normalize($('search').value.trim());
    const items=data.modules.filter(m=>(!query||normalize(m.name+' '+m.route+' '+(byId[m.process]?.name||'')).includes(query)) && (state.filter==='all'||byId[m.process]?.kind===state.filter));
    $('breadcrumb').innerHTML='<span>Directorio de módulos</span><span class="gm-divider">/</span><span>Registro del respaldo</span>';
    $('count').textContent=items.length+' de '+data.modules.length+' entradas';
    $('directory').innerHTML=items.map(m=>`<article class="gm-directory-row"><div><h3>${escape(m.name)}</h3><p>${escape(m.route)}</p>${m.note?`<span class="gm-route-note">${escape(m.note)}</span>`:''}</div><div class="gm-directory-actions">${m.process?`<button class="gm-button" type="button" data-select="${m.process}">Ver proceso</button>`:''}${m.available?`<a class="gm-button" href="${escape(moduleUrl(m.route))}" target="_blank" rel="noopener noreferrer">Abrir</a>`:''}</div></article>`).join('')||'<div class="gm-directory-row"><p>No hay entradas para esta búsqueda y condición.</p></div>';
  }

  function updateSelection(){
    renderDetail();
    if(state.mode==='actual')renderBreadcrumb();
    root.querySelectorAll('[data-node]').forEach(b=>{b.classList.toggle('is-selected',b.dataset.node===state.selected);b.setAttribute('aria-pressed',String(b.dataset.node===state.selected));});
    root.querySelectorAll('.gm-edge').forEach(e=>e.classList.toggle('gm-highlight',e.dataset.to===state.selected));
  }
  function select(id,options={}) {
    const module=modules[id],p=module?byId[module.process]:byId[id];if(!p)return;
    let changed=false;
    if(module||state.mode==='modulos'||(state.mode==='propuesto'&&!data.flow.some(s=>s.id===id))){changed=state.mode!=='actual';state.mode='actual';}
    state.selected=id;state.branch=p.branch;
    if(state.filter!=='all'&&p.kind!==state.filter){state.filter='all';changed=true;}
    if(state.mode==='actual'){
      if(!state.openBranches.has(p.branch)){state.openBranches.add(p.branch);changed=true;}
      if(p.links.length&&!state.openProcesses.has(p.id)){state.openProcesses.add(p.id);changed=true;}
    }
    if(changed)render({focusId:id});else{updateSelection();if(options.focus)reveal(id);}
    $('detail').scrollTop=0;save();announce('Seleccionado: '+(module?.name||p.name));
    if(innerWidth<=850&&(!changed||module))requestAnimationFrame(()=>$('detail').scrollIntoView({block:'start',behavior:'smooth'}));
  }
  function openBranch(id) {
    if(!branches[id])return;
    const changed=state.mode!=='actual'||!state.openBranches.has(id);
    state.mode='actual';state.branch=id;state.selected=id;state.openBranches.add(id);
    if(changed)render({focusId:id});else{updateSelection();reveal(id);}
    save();announce('Rama desplegada: '+branches[id].name);
    if(innerWidth<=850)$('canvas').scrollIntoView({block:'start',behavior:'smooth'});
  }
  function toggle(id){
    const set=branches[id]?state.openBranches:state.openProcesses;
    if(!set.has(id)){if(branches[id])openBranch(id);else select(id);return;}
    set.delete(id);
    state.selected=id;state.branch=branches[id]?id:byId[id].branch;
    render({focusId:id});save();announce('Nodo contraído: '+(branches[id]?.name||byId[id]?.name));
  }
  function overview() {state.mode='actual';state.branch=null;state.selected='root';render({focusId:'root'});save();announce('Guagua. Se conservan las ramas desplegadas.');}

  function renderSearch() {
    const input=$('search').value.trim(), q=normalize(input);
    if(state.mode==='modulos'){renderDirectory();$('search-results').hidden=true;return;}
    if(!q){$('search-results').hidden=true;return;}
    const matches=data.processes.filter(p=>normalize([p.name,p.purpose,...p.links.map(l=>l[0]),...data.modules.filter(m=>m.process===p.id).map(m=>m.name)].join(' ')).includes(q)).slice(0,8);
    $('search-results').innerHTML=matches.map(p=>`<button type="button" data-search-select="${p.id}"><span>${escape(p.name)}</span><small>${escape(branches[p.branch].short)} · ${labels[p.kind]}</small></button>`).join('')||'<p>No se encontró un proceso con ese nombre.</p>';
    $('search-results').hidden=false;
  }

  root.addEventListener('click',event=>{
    const t=event.target.closest('button');
    cancelNodeClick();
    if(!t)return;
    if(t.hasAttribute('data-node')){
      if(event.detail>1)return;
      if(event.detail===0)activateNode(t.dataset.node);
      else nodeClickTimer=setTimeout(()=>{nodeClickTimer=null;activateNode(t.dataset.node);},500);
      return;
    }
    if(t.hasAttribute('data-search-select')){$('search-results').hidden=true;$('search').value='';select(t.dataset.searchSelect);return;}
    if(t.hasAttribute('data-back-map')){$('canvas').scrollIntoView({block:'start',behavior:'smooth'});return;}
    if(t.hasAttribute('data-toggle')){toggle(t.dataset.toggle);return;}
    if(t.hasAttribute('data-module')){select(t.dataset.module,{focus:true});return;}
    if(t.hasAttribute('data-select')){select(t.dataset.select,{focus:t.closest('#gm-breadcrumb')!==null});return;}
    if(t.hasAttribute('data-branch')){openBranch(t.dataset.branch);return;}
    if(t.hasAttribute('data-overview')){overview();return;}
    if(t.hasAttribute('data-mode')){state.mode=t.dataset.mode;state.selected='root';state.filter='all';state.branch=null;$('search').value='';$('search-results').hidden=true;render();save();announce(t.textContent);return;}
    if(t.hasAttribute('data-filter')){state.filter=t.dataset.filter;render();save();announce('Condición: '+t.textContent.trim());}
  });
  root.addEventListener('dblclick',event=>{
    const node=event.target.closest('[data-node]');
    if(!node||event.button!==0)return;
    event.preventDefault();
    openNodeWindow(node.dataset.node);
  });
  root.addEventListener('keydown',event=>{
    const node=event.target.closest('[data-node]');
    if(!node||event.key!=='Enter'||!event.shiftKey||event.repeat)return;
    event.preventDefault();
    openNodeWindow(node.dataset.node);
  });
  $('search').addEventListener('input',renderSearch);
  $('search').addEventListener('keydown',e=>{if(e.key==='Escape')$('search-results').hidden=true;});
  document.addEventListener('click',e=>{if(!e.target.closest('.gm-search'))$('search-results').hidden=true;});
  $('show-all').addEventListener('click',()=>{state.filter='all';render();save();});
  $('zoom-in').addEventListener('click',()=>zoom(.12));$('zoom-out').addEventListener('click',()=>zoom(-.12));$('fit').addEventListener('click',fit);
  $('sources').addEventListener('click',()=>$('source-dialog').showModal());$('close-sources').addEventListener('click',()=>$('source-dialog').close());
  $('source-dialog').addEventListener('click',e=>{if(e.target===$('source-dialog')){const r=e.target.getBoundingClientRect();if(e.clientX<r.left||e.clientX>r.right||e.clientY<r.top||e.clientY>r.bottom)e.target.close();}});
  $('canvas').addEventListener('pointerdown',e=>{if(e.target.closest('button,output')||e.button!==0)return;drag={x:e.clientX,y:e.clientY,originX:view.x,originY:view.y};$('canvas').setPointerCapture(e.pointerId);$('canvas').classList.add('is-dragging');});
  $('canvas').addEventListener('pointermove',e=>{if(!drag)return;view.x=drag.originX+e.clientX-drag.x;view.y=drag.originY+e.clientY-drag.y;transform();});
  const release=()=>{drag=null;$('canvas').classList.remove('is-dragging');};
  $('canvas').addEventListener('pointerup',release);$('canvas').addEventListener('pointercancel',release);
  $('canvas').addEventListener('wheel',e=>{if(!e.ctrlKey&&!e.metaKey)return;e.preventDefault();zoom(e.deltaY>0?-.08:.08);},{passive:false});
  $('home').href=moduleUrl('index.php');
  window.addEventListener('hashchange',()=>{cancelNodeClick();readLocation();render();});
  let resizeTimer;window.addEventListener('resize',()=>{clearTimeout(resizeTimer);resizeTimer=setTimeout(()=>{if(state.mode!=='modulos')renderGraph({fitAll:true});},100);});
  readLocation();render();
})();
