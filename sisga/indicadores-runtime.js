/* Esta función se serializa completa para ejecutarse en la consola de SISGA. */
window.SisgaIndicatorRuntime = function (input, createCore) {
    'use strict';
    const core=createCore(), profile=core.validate(input), hostId='guagua-sisga-indicadores';
    document.getElementById(hostId)?.remove();
    const host=document.createElement('div');host.id=hostId;
    Object.assign(host.style,{position:'fixed',right:'16px',bottom:'16px',width:'min(480px, calc(100vw - 32px))',zIndex:'2147483647'});
    document.body.append(host);
    const shadow=host.attachShadow({mode:'open'});
    shadow.innerHTML=`<style>
        :host{font:14px/1.5 system-ui,sans-serif;color:#172b49}*{box-sizing:border-box}section{background:white;border:1px solid #ccd8ea;border-radius:16px;box-shadow:0 12px 60px #0f234450;overflow:hidden}header{background:#172c51;color:white;padding:16px;display:flex;gap:12px;align-items:center}h2{font-size:17px;margin:0}header p{font-size:12px;margin:3px 0 0;color:#cbdcf8}.body{padding:16px;max-height:65vh;overflow:auto}button,select{font:inherit;border:1px solid #c8d6e8;border-radius:8px;padding:9px;background:#fff;color:#172b49;cursor:pointer}button:focus-visible,select:focus-visible{outline:3px solid #85adff}button:disabled{opacity:.45;cursor:default}.primary{background:#2455db;color:white;border-color:#2455db}.actions{display:flex;gap:8px;flex-wrap:wrap}#close{margin-left:auto;color:white;background:transparent;border-color:#677b9c}#status{white-space:pre-line;font-size:13px;padding:12px;background:#edf3ff;border-radius:9px}#status[data-error=true]{background:#fff1f2;color:#9f1239}.row{padding:12px 0;border-top:1px solid #e0e7f1}.row strong{display:block;font-size:13px}.row small{display:block;color:#516681;margin:5px 0}select{width:100%;margin-top:8px}p{margin:10px 0}.hint{font-size:12px;color:#61758f}summary{cursor:pointer;font-weight:600}#context{font-size:13px;color:#475f82}label{display:block}input[type=checkbox]{accent-color:#2455db}
    </style><section aria-label="Asistente de indicadores SISGA"><header><div><h2>Fortalezas y dificultades</h2><p>Guagua · Asistente SISGA</p></div><button id="close" aria-label="Cerrar asistente">✕</button></header><div class="body"><div id="context"></div><p class="hint">Abre los indicadores de la materia y analiza la pantalla. Solo se procesan los controles cargados en este marco.</p><div class="actions"><button id="scan" class="primary">Analizar pantalla</button><button id="export">Exportar selección JSON</button></div><p id="status" role="status" aria-live="polite">Listo para analizar. No se han modificado selecciones.</p><div id="results"></div><p><label><input id="review" type="checkbox"> Revisé la materia, el grupo y las coincidencias.</label></p><div class="actions"><button id="apply" class="primary" disabled>Aplicar selección</button></div><p class="hint">Después de aplicar, revisa y guarda con el botón de SISGA. Exportar JSON guarda un archivo en tu equipo.</p></div></section>`;
    const $=id=>shadow.getElementById(id), norm=core.normalize;
    $('context').textContent=[profile.name,profile.metadata.subject,profile.metadata.group,profile.metadata.period?'Período '+profile.metadata.period:'',profile.metadata.year].filter(Boolean).join(' · ');
    const report=(text,error=false)=>{$('status').textContent=text;$('status').dataset.error=String(error);};
    let snapshot=null, choices=[];
    function visible(el) {
        if(el.tagName==='OPTION') return visible(el.parentElement);
        const style=getComputedStyle(el);
        return el.isConnected&&!el.closest('[hidden],[aria-hidden="true"]')&&style.display!=='none'&&style.visibility!=='hidden'&&(el.getClientRects().length>0||!!el.closest('label')?.getClientRects().length);
    }
    function accessible(el) {
        const label=el.getAttribute('aria-label');if(label)return label.trim();
        const labelled=(el.getAttribute('aria-labelledby')||'').split(/\s+/).map(id=>document.getElementById(id)?.textContent||'').join(' ').trim();if(labelled)return labelled;
        const labels=Array.from(el.labels||[]).map(l=>l.textContent.trim()).join(' ');if(labels)return labels;
        if(el.tagName==='OPTION')return el.textContent.trim();
        const row=el.closest('tr');
        if(row)return Array.from(row.cells).map(td=>td.textContent.trim()).filter(Boolean).join(' ');
        const parent=el.parentElement;
        return (parent&&parent.childElementCount<=4&&parent.textContent.length<2000?parent.textContent:el.title||'').trim();
    }
    function context(el){
        const text=[el.id,el.name,el.getAttribute('aria-label')];
        let parent=el.parentElement;
        for(let i=0;parent&&i<4&&parent!==document.body;i++,parent=parent.parentElement){
            text.push(parent.id,parent.getAttribute('aria-label'));
            const heading=Array.from(parent.children).find(child=>child.matches('legend,h1,h2,h3,h4,label,caption,thead'));
            if(heading&&heading.textContent.length<300)text.push(heading.textContent);
        }
        return norm(text.join(' '));
    }
    function category(el){
        const text=context(el), strength=/fortaleza|fortalezas/.test(text), difficulty=/dificultad|dificultades/.test(text);
        return strength&&!difficulty?'strengths':difficulty&&!strength?'difficulties':null;
    }
    function controls(selector,kind){
        let roots;try{roots=Array.from(document.querySelectorAll(selector));}catch(_){throw new Error('Selector CSS inválido para '+kind+': '+selector);}
        const query=kind==='students'||kind==='all'?'input[type="checkbox"]':'select,input[type="checkbox"],input[type="radio"]';
        return [...new Set(roots.flatMap(el=>el.matches(query)?[el]:Array.from(el.querySelectorAll(query))))].filter(el=>visible(el)&&!el.disabled);
    }
    const isAll=el=>/^(todos|todas|seleccionar todos|seleccionar todos los estudiantes|todos los estudiantes|marcar todos)$/.test(norm(accessible(el)))||/^(chk|check|cb|seleccionar|marcar)?todos$/.test(norm(el.id).replace(/ /g,''));
    function readScreen(){
        const allControls=Array.from(document.querySelectorAll('select,input[type="checkbox"],input[type="radio"]')).filter(el=>!el.disabled&&visible(el));
        const checkboxes=allControls.filter(el=>el.matches('input[type="checkbox"]'));
        const all=profile.selectors.all?controls(profile.selectors.all,'all'):checkboxes.filter(isAll);
        if(all.length>1)throw new Error('Se detectó más de una opción «Todos». Configura su selector CSS en Guagua para elegir la de estudiantes.');
        const allTable=all[0]?.closest('table');
        const students=(profile.selectors.students?controls(profile.selectors.students,'students'):checkboxes.filter(el=>{
            if(isAll(el)||category(el))return false;
            const table=el.closest('table');
            return (table&&table===allTable)||/estudiante|alumno|matricula/.test(context(el));
        })).filter(el=>!all.includes(el)&&!isAll(el)).map(el=>({el,text:accessible(el),value:el.value}));
        const catalog={};
        for(const type of ['strengths','difficulties']){
            const source=profile.selectors[type]?controls(profile.selectors[type],type):allControls.filter(el=>category(el)===type);
            catalog[type]=source.flatMap(el=>el.tagName==='SELECT'?Array.from(el.options).filter(opt=>!opt.disabled&&opt.value!==''&& !/^(seleccione|seleccionar|ninguno|ninguna|escoja)(\s|$)/.test(norm(opt.textContent))).map(opt=>({el:opt,control:el,text:opt.textContent.trim(),value:opt.value,selected:opt.selected})):!all.includes(el)&&!students.some(s=>s.el===el)?[{el,control:el,text:accessible(el),value:el.value,selected:el.checked}]:[]).filter(item=>item.text);
        }
        const overlap=catalog.strengths.some(a=>catalog.difficulties.some(b=>a.el===b.el));
        if(overlap)throw new Error('Los selectores de fortalezas y dificultades apuntan al mismo control. Sepáralos en la configuración.');
        if([...catalog.strengths,...catalog.difficulties].some(item=>students.some(s=>s.el===item.el)||all.includes(item.el)))throw new Error('Los controles de indicadores y estudiantes se superponen. Revisa los selectores.');
        return {all:all[0]||null,students,catalog};
    }
    function desiredStudents(screen){
        if(!screen.students.length)throw new Error('No se detectaron casillas de estudiantes. Abre la lista o configura su selector en Guagua.');
        if(profile.students.mode==='all')return screen.students;
        const result=[];
        for(const name of profile.students.names){
            const n=norm(name);
            const matches=screen.students.filter(s=>norm(s.text)===n||norm(s.value)===n||norm(s.text).split(' ').sort().join(' ')===n.split(' ').sort().join(' '));
            if(matches.length!==1)throw new Error('Estudiante no encontrado o ambiguo: '+name+'. Usa el identificador de la casilla o el texto completo de su fila.');
            if(!result.includes(matches[0]))result.push(matches[0]);
        }
        if(!result.length)throw new Error('La lista de estudiantes está vacía.');
        return result;
    }
    function scan(){
        snapshot=null;choices=[];$('apply').disabled=true;$('review').checked=false;$('results').replaceChildren();
        try{
            checkContext();snapshot=readScreen();const desired=desiredStudents(snapshot);
            for(const type of ['strengths','difficulties']) for(const query of profile.targets[type]){
                const found=core.rank(query,snapshot.catalog[type],profile.matching.mode,profile.matching.threshold),row=document.createElement('div'),title=document.createElement('strong'),hint=document.createElement('small'),select=document.createElement('select');row.className='row';title.textContent=(type==='strengths'?'Fortaleza: ':'Dificultad: ')+query;
                hint.textContent=found.status==='ambiguous'?'Varias coincidencias cercanas. Elige una para continuar.':found.status==='missing'?'Sin coincidencia suficiente. Elige un indicador o exclúyelo.':found.status==='exact'?'Coincidencia exacta.':'Coincidencia similar: revisa el texto.';
                const empty=document.createElement('option');empty.value='';empty.textContent='Selecciona una coincidencia…';select.append(empty);
                const skip=document.createElement('option');skip.value='skip';skip.textContent='Excluir este indicador';select.append(skip);
                for(const candidate of found.candidates){const opt=document.createElement('option');opt.value=String(candidate.index);opt.textContent=Math.round(candidate.score*100)+'% · '+candidate.item.text;select.append(opt);}
                if(found.selected>=0)select.value=String(found.selected);select.setAttribute('aria-label',title.textContent);
                const choice={type,query,select};choices.push(choice);select.addEventListener('change',()=>{$('review').checked=false;updateApply();});row.append(title,hint,select);$('results').append(row);
            }
            report(desired.length+' de '+snapshot.students.length+' estudiantes cargados.\n'+snapshot.catalog.strengths.length+' fortalezas · '+snapshot.catalog.difficulties.length+' dificultades disponibles.\n'+(snapshot.all?'Opción «Todos» detectada.':'No se detectó «Todos»; se marcarán las casillas de estudiantes.')+'\nRevisa las coincidencias y confirma la materia y el grupo.');
        }catch(error){snapshot=null;report(error.message,true);}
    }
    function updateApply(){ $('apply').disabled=!snapshot||!$('review').checked||choices.some(c=>c.select.value===''); }
    function checkContext(){
        let actual;try{actual=core.fromUrl(location.href);}catch(_){return;}
        for(const [key,label] of [['subject','materia'],['group','grupo'],['period','período']]) if(actual[key]&&profile.metadata[key]&&norm(actual[key])!==norm(profile.metadata[key]))throw new Error('El '+label+' de la pantalla ('+actual[key]+') no coincide con el perfil ('+profile.metadata[key]+'). Abre la planilla correcta o actualiza el perfil.');
    }
    $('review').addEventListener('change',updateApply);
    function setChecked(el,value){if(el.checked!==value){el.click();if(el.checked!==value)throw new Error('SISGA no aceptó una selección. Revisa la pantalla.');}}
    function selectedItems(){return choices.filter(c=>c.select.value!=='skip').map(c=>{const item=snapshot.catalog[c.type][Number(c.select.value)];if(!item)throw new Error('Falta elegir una coincidencia.');return item;});}
    const selectionState=item=>item.el.tagName==='OPTION'?item.el.selected:item.el.checked;
    function preflight(items){
        for(const item of [...snapshot.students,...items]) if(!item.el.isConnected||!visible(item.el)||item.el.disabled||item.control?.disabled)throw new Error('La pantalla cambió. Vuelve a analizar antes de aplicar.');
        if(snapshot.all&&(!visible(snapshot.all)||snapshot.all.disabled))throw new Error('La opción Todos cambió. Vuelve a analizar.');
        const grouped=new Map();
        for(const item of items){const el=item.control;const key=el.tagName==='SELECT'&&!el.multiple?el:el.type==='radio'?el.form||el.getRootNode():null;if(!key)continue;const group=el.type==='radio'?el.name||el:el;const old=grouped.get(key)||new Map();const previous=old.get(group);if(previous&&previous.el!==item.el)throw new Error('La pantalla solo permite un indicador en uno de los campos. Elige uno y excluye los demás.');old.set(group,item);grouped.set(key,old);}
    }
    function unchanged(){
        const current=readScreen();
        if(current.all!==snapshot.all||current.students.length!==snapshot.students.length||current.students.some((s,i)=>s.el!==snapshot.students[i].el||s.text!==snapshot.students[i].text||s.value!==snapshot.students[i].value))throw new Error('La lista de estudiantes cambió. Vuelve a analizar.');
        for(const type of ['strengths','difficulties']) if(current.catalog[type].length!==snapshot.catalog[type].length||current.catalog[type].some((item,i)=>item.el!==snapshot.catalog[type][i].el||item.text!==snapshot.catalog[type][i].text||item.value!==snapshot.catalog[type][i].value))throw new Error('Los indicadores cambiaron. Vuelve a analizar.');
    }
    $('apply').addEventListener('click',()=>{
        $('apply').disabled=true;
        try{
            if(!snapshot||!$('review').checked||choices.some(c=>c.select.value===''))throw new Error('Analiza y revisa las coincidencias antes de aplicar.');
            checkContext();unchanged();const students=desiredStudents(snapshot),items=selectedItems();preflight(items);
            if(snapshot.all)setChecked(snapshot.all,profile.students.mode==='all');
            unchanged();
            for(const student of snapshot.students){if(!student.el.isConnected)throw new Error('SISGA recargó la lista. Vuelve a analizar.');setChecked(student.el,students.includes(student));}
            const selects=new Map();
            for(const item of items){if(!item.el.isConnected)throw new Error('SISGA recargó los indicadores. Vuelve a analizar.');if(item.el.tagName==='OPTION'){if(!selects.has(item.control))selects.set(item.control,[]);selects.get(item.control).push(item.el);}else setChecked(item.el,true);}
            for(const [select,options] of selects){if(!select.isConnected)throw new Error('El campo de indicadores cambió. Vuelve a analizar.');let changed=false;for(const opt of options){if(!opt.selected){opt.selected=true;changed=true;}}if(changed){select.dispatchEvent(new Event('input',{bubbles:true}));select.dispatchEvent(new Event('change',{bubbles:true}));}}
            if(snapshot.students.some(s=>!s.el.isConnected||s.el.checked!==students.includes(s))||items.some(item=>!item.el.isConnected||!selectionState(item)))throw new Error('SISGA no mantuvo todas las selecciones. Revisa y vuelve a analizar.');
            report('Selección aplicada: '+students.length+' estudiantes y '+new Set(items.map(i=>i.el)).size+' indicadores.\nRevisa la pantalla y guarda con el botón de SISGA.');
        }catch(error){report(error.message+' Si hubo cambios parciales, revísalos en SISGA.',true);}
        snapshot=null;$('review').checked=false;
    });
    $('export').addEventListener('click',()=>{
        try{
            const screen=readScreen(), data=core.validate(profile);
            try{const actual=core.fromUrl(location.href);data.sourceUrl=location.href;for(const key of ['subject','group','period'])if(actual[key])data.metadata[key]=actual[key];}catch(_){}
            data.catalog=Object.fromEntries(['strengths','difficulties'].map(type=>[type,screen.catalog[type].map(({text,value,selected})=>({text,value,selected}))]));
            data.targets=Object.fromEntries(['strengths','difficulties'].map(type=>[type,data.catalog[type].filter(item=>item.selected).map(item=>item.text)]));
            const selected=screen.students.filter(s=>s.el.checked);
            data.students={mode:selected.length===screen.students.length&&selected.length>0?'all':'list',names:selected.map(s=>s.value&&s.value!=='on'&&screen.students.filter(other=>other.value===s.value).length===1?s.value:s.text)};
            const blob=new Blob([JSON.stringify(data,null,2)],{type:'application/json'}),url=URL.createObjectURL(blob),link=document.createElement('a');link.href=url;link.download=(norm(data.name||data.metadata.subject||'sisga-indicadores').replace(/ /g,'-'))+'.json';link.click();setTimeout(()=>URL.revokeObjectURL(url),1000);report('JSON exportado con las opciones seleccionadas actualmente y el catálogo detectado. Impórtalo en Guagua para reutilizarlo.');
        }catch(error){report(error.message,true);}
    });
    $('scan').addEventListener('click',scan);$('close').addEventListener('click',()=>host.remove());
};
