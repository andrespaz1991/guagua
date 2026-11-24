document.addEventListener('DOMContentLoaded', () => {
    'use strict';
    const $ = id => document.getElementById(id), ws = window.dbaWorkspace;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const selected = new Set();
    let page=1, generating=false, stopping=false, connected=false, connecting=false;
    let proposals=[], importRows=[], editing={}, returnFocus=null;
    const evidence = id => (ws.getData().evidencias || []).filter(e => String(e.id_dba)===String(id) && String(e.descripcion_evidencia || '').trim());
    const badge = e => `<span class="ev-badge ${e.origen==='ia_pendiente'?'pending':e.origen==='ia_revisada'?'reviewed':''}">${e.origen==='ia_pendiente'?'IA · por revisar':e.origen==='ia_revisada'?'IA · revisada':e.origen==='importada'?'Importada':'Registrada'}</span>`;
    const tags = words => `<div class="ev-tags">${String(words || '').split(',').filter(s=>s.trim()).map(s=>`<span>${esc(s.trim())}</span>`).join('')}</div>`;
    function context(dba) {
        const data=ws.getData(), standard=data.estandares.find(e=>String(e.id_estandar)===String(dba.id_estandar)) || {};
        const grade=data.grados.find(g=>String(g.id_grado)===String(standard.grado)), subject=data.materias.find(m=>String(m.id_materia)===String(standard.id_materia_oficial));
        return {standard,label:`${grade?`Grado ${grade.nombre}`:'Sin grado'} · ${subject?.nombre_materia || 'Sin materia'} · P${standard.id_periodo || '—'}`};
    }
    function filtered() {
        const {gradoId,materiaId}=ws.getFilters(), search=$('ev-search').value.trim().toLocaleLowerCase();
        return ws.getData().dbas.filter(d=>{
            const {standard}=context(d), ev=evidence(d.id_dba), state=$('ev-filter').value;
            return (!gradoId || String(standard.grado)===String(gradoId)) && (!materiaId || String(standard.id_materia_oficial)===String(materiaId))
                && (!$('ev-period').value || String(standard.id_periodo)===$('ev-period').value)
                && (!search || [d.nombre_dba,d.descripcion_dba,d.palabras_clave,...ev.map(e=>e.descripcion_evidencia)].join(' ').toLocaleLowerCase().includes(search))
                && (!state || state==='missing'&&!ev.length || state==='complete'&&ev.length || state==='pending'&&ev.some(e=>e.origen==='ia_pendiente'));
        });
    }
    function status(message,error=false){$('ev-status').textContent=message;$('ev-status').classList.toggle('error',error);}
    async function api(payload,form=null){
        const controller=new AbortController(),timer=setTimeout(()=>controller.abort(),170000);
        try{
            const r=await fetch('api/evidencias.php',{method:'POST',headers:form?{'X-CSRF-Token':csrf}:{'Content-Type':'application/json','X-CSRF-Token':csrf},body:form||JSON.stringify(payload),signal:controller.signal});
            const data=await r.json().catch(()=>null);
            if(!r.ok || data?.status!=='success')throw new Error(data?.message || 'La sesión expiró o el servidor no pudo responder. Actualice la página.');
            return data;
        }finally{clearTimeout(timer);}
    }
    function targets(){return $('ev-scope').value==='selected'?ws.getData().dbas.filter(d=>selected.has(String(d.id_dba))):filtered().filter(d=>$('ev-scope').value!=='missing'||!evidence(d.id_dba).length);}
    function updateTargets(){const list=targets();$('ev-ai-targets').textContent=`${list.length} DBA · hasta ${list.length*Number($('ev-quantity').value)} propuestas. Se procesan uno por uno.`;$('ev-generate').disabled=generating||!list.length;}
    function render(){
        const all=ws.getData().dbas,existing=new Set(all.map(d=>String(d.id_dba)));
        for(const id of selected)if(!existing.has(id))selected.delete(id);
        const complete=all.filter(d=>evidence(d.id_dba).length).length,pending=(ws.getData().evidencias||[]).filter(e=>e.origen==='ia_pendiente'&&String(e.descripcion_evidencia||'').trim()).length;
        $('ev-stats').innerHTML=[['DBA en la malla',all.length,'fa-layer-group'],['Con evidencia',complete,'fa-circle-check'],['Por completar',all.length-complete,'fa-flag'],['Propuestas por revisar',pending,'fa-wand-magic-sparkles']].map(([label,n,icon])=>`<div class="ev-stat"><span><i class="fa-solid ${icon}"></i> ${label}</span><strong>${n}</strong></div>`).join('');
        const list=filtered(),maxPage=Math.max(1,Math.ceil(list.length/12));page=Math.min(page,maxPage);
        $('ev-selection-count').textContent=`${selected.size} seleccionados · ${list.length} resultados`;
        $('ev-select-all').checked=!!list.length&&list.every(d=>selected.has(String(d.id_dba)));
        $('ev-select-all').indeterminate=list.some(d=>selected.has(String(d.id_dba)))&&!$('ev-select-all').checked;
        $('ev-generate-selection').disabled=!selected.size;
        $('ev-library').innerHTML=list.slice((page-1)*12,page*12).map(d=>{
            const ev=evidence(d.id_dba);
            return `<article class="ev-card ${selected.has(String(d.id_dba))?'selected':''}"><header><label class="ev-card-check"><input type="checkbox" data-ev-select="${d.id_dba}" ${selected.has(String(d.id_dba))?'checked':''} aria-label="Seleccionar DBA ${d.id_dba}"><span>DBA #${d.id_dba}</span></label><span class="ev-context">${esc(context(d).label)}</span></header><h3>${esc(d.nombre_dba)}</h3>${tags(d.palabras_clave)}<div class="ev-card-divider"><span>${ev.length} evidencia${ev.length===1?'':'s'}</span><button type="button" class="btn-icon" data-ev-keywords="${d.id_dba}" title="Editar DBA y palabras clave"><i class="fa-solid fa-tags"></i></button></div>${ev.length?ev.map(e=>`<div class="ev-evidence"><div>${badge(e)}${e.modelo?`<span class="ev-model-label">${esc(e.modelo)}</span>`:''}</div><p>${esc(e.descripcion_evidencia)}</p><div class="ev-evidence-actions"><button type="button" class="btn btn-secondary" data-ev-edit="${e.id_evidencia_aprendizaje}">${e.origen==='ia_pendiente'?'Revisar':'Editar'}</button><button type="button" class="btn-icon delete" data-ev-delete="${e.id_evidencia_aprendizaje}" title="Eliminar evidencia"><i class="fa-solid fa-trash-can"></i></button></div></div>`).join(''):'<div class="ev-missing"><i class="fa-regular fa-lightbulb"></i> Este DBA aún no tiene evidencias.</div>'}<footer><button type="button" class="btn btn-secondary" data-ev-add="${d.id_dba}"><i class="fa-solid fa-plus"></i> Añadir</button><button type="button" class="btn ev-ai-button" data-ev-one="${d.id_dba}"><i class="fa-solid fa-wand-magic-sparkles"></i> Proponer con IA</button></footer></article>`;
        }).join('') || '<div class="ev-empty"><i class="fa-solid fa-magnifying-glass"></i><h3>No hay DBA con estos filtros</h3><p>Cambie el grado, la materia o el estado para ampliar la búsqueda.</p></div>';
        $('ev-page').textContent=`Página ${page} de ${maxPage}`;$('ev-prev').disabled=page===1;$('ev-next').disabled=page>=maxPage;updateTargets();
    }
    function setMode(mode){for(const name of ['library','import','ai'])$(`ev-${name}-panel`).classList.toggle('hidden',name!==mode);document.querySelectorAll('[data-ev-mode]').forEach(b=>{b.classList.toggle('active',b.dataset.evMode===mode);b.setAttribute('aria-pressed',String(b.dataset.evMode===mode));});if(mode==='ai'&&!connected)connect();updateTargets();}
    document.querySelectorAll('[data-ev-mode]').forEach(b=>b.addEventListener('click',()=>setMode(b.dataset.evMode)));
    document.addEventListener('dba:data',render);document.addEventListener('dba:filters',()=>{page=1;render();});
    document.addEventListener('dba:message',event=>status(event.detail.message,event.detail.type==='error'));
    let searchTimer;$('ev-search').addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(()=>{page=1;render();},160);});
    for(const id of ['ev-period','ev-filter'])$(id).addEventListener('change',()=>{page=1;render();});
    $('ev-clear').addEventListener('click',()=>{for(const id of ['ev-search','ev-period','ev-filter'])$(id).value='';for(const id of ['select-grado','select-materia']){$(id).value='';$(id).dispatchEvent(new Event('change'));}page=1;render();});
    $('ev-prev').addEventListener('click',()=>{page--;render();$('ev-library').scrollIntoView({block:'start'});});$('ev-next').addEventListener('click',()=>{page++;render();$('ev-library').scrollIntoView({block:'start'});});
    $('ev-select-all').addEventListener('change',()=>{filtered().forEach(d=>$('ev-select-all').checked?selected.add(String(d.id_dba)):selected.delete(String(d.id_dba)));render();});
    $('ev-library').addEventListener('change',e=>{const id=e.target.dataset.evSelect;if(id){e.target.checked?selected.add(id):selected.delete(id);render();}});
    $('ev-refresh').addEventListener('click',async()=>{$('ev-refresh').disabled=true;try{const ok=await ws.refresh();status(ok?'Biblioteca actualizada.':'No se pudo actualizar la biblioteca.',!ok);}finally{$('ev-refresh').disabled=false;}});
    $('ev-generate-selection').addEventListener('click',()=>{$('ev-scope').value='selected';setMode('ai');});
    function openDialog(id,row=null){
        const dba=ws.getData().dbas.find(d=>String(d.id_dba)===String(id));if(!dba)return;
        editing={id_dba:dba.id_dba,id_evidencia:row?.id_evidencia_aprendizaje||0};$('ev-dialog-title').textContent=row?.origen==='ia_pendiente'?'Revisar propuesta de IA':row?'Editar evidencia':'Nueva evidencia';
        $('ev-dialog-context').textContent=`DBA #${dba.id_dba} · ${dba.nombre_dba}`;$('ev-text').value=row?.descripcion_evidencia||'';$('ev-dialog-error').textContent='';returnFocus=document.activeElement;$('ev-dialog').showModal();$('ev-text').focus();
    }
    function closeDialog(){$('ev-dialog').close();returnFocus?.focus();}
    $('ev-dialog-close').addEventListener('click',closeDialog);$('ev-dialog-cancel').addEventListener('click',closeDialog);
    $('ev-form').addEventListener('submit',async e=>{e.preventDefault();$('ev-dialog-save').disabled=true;try{const r=await api({action:'save',...editing,evidencia:$('ev-text').value});closeDialog();await ws.refresh();status(r.message);}catch(error){$('ev-dialog-error').textContent=error.message;}finally{$('ev-dialog-save').disabled=false;}});
    document.addEventListener('click',async e=>{
        const b=e.target.closest('button');if(!b)return;
        if(b.dataset.evAdd)openDialog(b.dataset.evAdd);
        if(b.dataset.evEdit){const row=(ws.getData().evidencias||[]).find(x=>String(x.id_evidencia_aprendizaje)===b.dataset.evEdit);if(row)openDialog(row.id_dba,row);}
        if(b.dataset.evKeywords)ws.editDba(b.dataset.evKeywords);
        if(b.dataset.evOne){selected.clear();selected.add(b.dataset.evOne);$('ev-scope').value='selected';ws.switchView('evidencias');setMode('ai');}
        if(b.dataset.evDelete&&confirm('¿Eliminar esta evidencia de aprendizaje? Esta acción no se puede deshacer.')){b.disabled=true;try{const r=await api({action:'delete',id_evidencia:b.dataset.evDelete});await ws.refresh();status(r.message);}catch(error){status(error.message,true);b.disabled=false;}}
    });
    async function connect(){
        if(connecting)return;connecting=true;$('ev-connect').disabled=true;$('ev-connection-state').textContent='Conectando…';
        try{const r=await fetch('api/evidencias.php?action=models'),data=await r.json();if(!r.ok||data.status!=='success')throw new Error(data.message||'No fue posible conectar.');const current=$('ev-model').value;$('ev-model').replaceChildren(...data.models.map(m=>new Option(m,m)));if(data.models.includes(current))$('ev-model').value=current;if(!data.models.length)throw new Error('Cargue un modelo de conversación en LM Studio.');connected=true;$('ev-connection-state').textContent='Conectado';$('ev-server').textContent=data.server;$('ev-connection-state').className='connected';}
        catch(error){connected=false;$('ev-connection-state').textContent='Sin conexión';$('ev-connection-state').className='error';status(error.message,true);}finally{connecting=false;$('ev-connect').disabled=false;}
    }
    $('ev-connect').addEventListener('click',connect);$('ev-scope').addEventListener('change',updateTargets);$('ev-quantity').addEventListener('change',updateTargets);
    function renderProposals(){
        $('ev-ai-proposals').innerHTML=proposals.map((p,i)=>`<article class="ev-proposal"><header><label><input type="checkbox" data-proposal-check="${i}" ${p.checked?'checked':''} ${p.saved?'disabled':''}> DBA #${p.id_dba}</label><span class="ev-badge ${p.saved?'reviewed':'pending'}">${p.saved?'Guardada':'Propuesta IA'}</span></header><p class="ev-context">${esc(p.nombre_dba)}</p><label class="ev-field">Evidencia<textarea class="modern-input" rows="4" maxlength="6000" data-proposal-text="${i}" ${p.saved?'disabled':''}>${esc(p.evidencia)}</textarea></label><label class="ev-field">Palabras clave<input class="modern-input" maxlength="1200" data-proposal-keywords="${i}" value="${esc(p.palabras_clave)}" ${p.saved?'disabled':''}></label><small>${esc(p.modelo)}</small></article>`).join('')||'<div class="ev-empty"><i class="fa-regular fa-lightbulb"></i><h3>Su próxima evidencia empieza aquí</h3><p>Genere propuestas a partir de los DBA seleccionados.</p></div>';
        $('ev-ai-save').disabled=generating||!proposals.some(p=>p.checked&&!p.saved);
    }
    $('ev-ai-proposals').addEventListener('input',e=>{const t=e.target;if(t.dataset.proposalText!==undefined)proposals[t.dataset.proposalText].evidencia=t.value;if(t.dataset.proposalKeywords!==undefined)proposals[t.dataset.proposalKeywords].palabras_clave=t.value;});
    $('ev-ai-proposals').addEventListener('change',e=>{if(e.target.dataset.proposalCheck!==undefined){proposals[e.target.dataset.proposalCheck].checked=e.target.checked;$('ev-ai-save').disabled=generating||!proposals.some(p=>p.checked&&!p.saved);}});
    $('ev-discard').addEventListener('click',()=>{if(!generating&&(!proposals.some(p=>!p.saved)||confirm('¿Descartar las propuestas que todavía no ha guardado?'))){proposals=[];renderProposals();}});
    $('ev-generate').addEventListener('click',async()=>{
        if(generating)return;const list=targets().slice(),model=$('ev-model').value,quantity=Number($('ev-quantity').value);if(!list.length)return status('Seleccione al menos un DBA.',true);
        generating=true;stopping=false;$('ev-ai-errors').textContent='';$('ev-stop').classList.remove('hidden');$('ev-stop').disabled=false;for(const id of ['ev-generate','ev-model','ev-scope','ev-quantity','ev-discard','ev-ai-save','ev-connect'])$(id).disabled=true;
        $('ev-progress').max=list.length;$('ev-progress').value=0;let done=0,failures=0;
        try{for(const dba of list){if(stopping)break;$('ev-progress-text').textContent=`Leyendo DBA #${dba.id_dba}… ${done} de ${list.length} procesados.`;
            try{const r=await api({action:'generate',id_dba:dba.id_dba,modelo:model,cantidad:quantity});for(const text of r.proposal.evidencias)proposals.push({id_dba:dba.id_dba,nombre_dba:dba.nombre_dba,evidencia:text,palabras_clave:r.proposal.palabras_clave,modelo:model,checked:true,saved:false});renderProposals();}
            catch(error){failures++;const p=document.createElement('p');p.textContent=`DBA #${dba.id_dba}: ${error.message}`;$('ev-ai-errors').append(p);}done++;$('ev-progress').value=done;}
        }finally{generating=false;$('ev-stop').classList.add('hidden');for(const id of ['ev-model','ev-scope','ev-quantity','ev-discard','ev-connect'])$(id).disabled=false;$('ev-progress-text').textContent=`${stopping?'Proceso detenido':'Generación terminada'}: ${done} DBA procesados, ${failures} errores. Revise las propuestas antes de guardarlas.`;updateTargets();renderProposals();}
    });
    $('ev-stop').addEventListener('click',()=>{stopping=true;$('ev-stop').disabled=true;$('ev-progress-text').textContent='Se detendrá al terminar el DBA actual; se conservan las propuestas obtenidas.';});
    $('ev-ai-save').addEventListener('click',async()=>{const rows=proposals.filter(p=>p.checked&&!p.saved);if(!rows.length)return;$('ev-ai-save').disabled=true;try{const r=await api({action:'save_batch',source:'ia',rows});rows.forEach(p=>{p.saved=true;p.checked=false;});renderProposals();await ws.refresh();status(r.message);}catch(error){status(error.message,true);$('ev-ai-save').disabled=false;}});
    $('ev-file').addEventListener('change',()=>{importRows=[];$('ev-import-preview').disabled=!$('ev-file').files.length;$('ev-import-save').disabled=true;$('ev-import-preview-list').textContent='';$('ev-import-errors').textContent='';$('ev-import-summary').textContent=$('ev-file').files[0]?.name||'Seleccione un archivo.';});
    $('ev-import-preview').addEventListener('click',async()=>{
        const file=$('ev-file').files[0];if(!file)return;$('ev-import-preview').disabled=true;$('ev-import-save').disabled=true;
        try{const form=new FormData();form.append('action','preview_import');form.append('archivo',file);const r=await api(null,form);importRows=r.rows;$('ev-import-summary').textContent=`${r.rows.length} evidencias válidas · ${r.empty} filas vacías omitidas · ${r.errors.length} errores.`;$('ev-import-errors').textContent=r.errors.join('\n');$('ev-import-preview-list').innerHTML=r.rows.map(row=>`<article class="ev-proposal"><span class="ev-badge">DBA #${row.id_dba}</span><h4>${esc(row.nombre_dba)}</h4><p>${esc(row.evidencia)}</p>${tags(row.palabras_clave)}</article>`).join('');$('ev-import-save').disabled=!r.rows.length||!!r.errors.length;if(r.errors.length)status('Corrija las filas señaladas y vuelva a validar. Todavía no se ha guardado ningún registro.',true);}
        catch(error){status(error.message,true);}finally{$('ev-import-preview').disabled=false;}
    });
    $('ev-import-save').addEventListener('click',async()=>{$('ev-import-save').disabled=true;try{const r=await api({action:'save_batch',source:'import',rows:importRows});importRows=[];await ws.refresh();$('ev-import-summary').textContent=r.message;status(r.message);}catch(error){status(error.message,true);$('ev-import-save').disabled=false;}});
    window.addEventListener('beforeunload',e=>{if(generating||proposals.some(p=>!p.saved)){e.preventDefault();e.returnValue='';}});
    render();
});
