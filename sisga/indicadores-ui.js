(function () {
    'use strict';
    const core=window.SisgaIndicatorCore, $=id=>document.getElementById('sg-'+id);
    const storageKey='guagua.sisga.indicadores.profiles.v1';
    let activeId='', profiles=[], catalog={strengths:[],difficulties:[]}, dirty=false;
    const status=(message,error=false)=>{ $('status').textContent=message; $('status').dataset.error=String(error); };
    const tabs=[$('tab-notas'),$('tab-indicadores'),$('tab-capturas'),$('tab-periodos')].filter(Boolean);
    function activate(index) { tabs.forEach((tab,i)=>{ tab.setAttribute('aria-selected',String(i===index));tab.tabIndex=i===index?0:-1;document.getElementById(tab.getAttribute('aria-controls')).hidden=i!==index; });document.dispatchEvent(new CustomEvent('sisga:tab-change',{detail:{panel:tabs[index].getAttribute('aria-controls')}})); }
    tabs.forEach((tab,index)=>{tab.addEventListener('click',()=>activate(index));tab.addEventListener('keydown',event=>{if(['ArrowLeft','ArrowRight','Home','End'].includes(event.key)){event.preventDefault();const next=event.key==='Home'?0:event.key==='End'?tabs.length-1:(index+(event.key==='ArrowRight'?1:-1)+tabs.length)%tabs.length;activate(next);tabs[next].focus();}});});
    if(location.hash==='#indicadores') activate(1);
    if(location.hash==='#indicadores-guardados') activate(2);
    if(location.hash==='#periodos') activate(3);
    const describe=p=>[p.metadata.subject,p.metadata.group,p.metadata.period?'Período '+p.metadata.period:'',p.metadata.year].filter(Boolean).join(' · ');
    const option=(text,value)=>{const el=document.createElement('option');el.textContent=text;el.value=value;return el;};
    function renderProfiles(){ $('profiles').replaceChildren(option('Nueva configuración',''),...profiles.map(p=>option(p.name||describe(p),p.id)));$('profiles').value=activeId; }
    try { const saved=JSON.parse(localStorage.getItem(storageKey)||'[]'); if(!Array.isArray(saved)) throw new Error(); profiles=saved.map(core.validate); }
    catch (_) { status('No se pudieron leer los perfiles locales. Puedes importar un JSON.',true); }
    function sync(){ $('student-list-wrap').hidden=document.querySelector('[name="sg-students"]:checked').value!=='list';$('threshold-label').textContent=$('threshold').value+'%';$('threshold').disabled=$('mode').value==='exact'; }
    function invalidate(){dirty=true;$('result').hidden=true;$('matches').replaceChildren();sync();}
    $('form').addEventListener('input',invalidate);$('form').addEventListener('change',invalidate);
    function collect(){
        const readCatalog=type=>core.lines($('catalog-'+type).value).map(text=>catalog[type].find(item=>item.text===text)||{text,value:'',selected:false});
        const profile=core.validate({schema:'guagua.sisga.indicadores',version:1,id:activeId,name:$('name').value,metadata:{subject:$('subject').value,group:$('group').value,period:$('period').value,year:Number($('year').value)},students:{mode:document.querySelector('[name="sg-students"]:checked').value,names:core.lines($('student-list').value)},targets:{strengths:core.lines($('strengths').value),difficulties:core.lines($('difficulties').value)},matching:{mode:$('mode').value,threshold:Number($('threshold').value)},selectors:Object.fromEntries(['all','students','strengths','difficulties'].map(key=>[key,$('selector-'+key).value])),catalog:{strengths:readCatalog('strengths'),difficulties:readCatalog('difficulties')}});
        for(const selector of Object.values(profile.selectors)) if(selector) { try{document.querySelector(selector);}catch(_){throw new Error('Hay un selector CSS inválido: '+selector);} }
        profile.sourceUrl=$('source-url').value.trim();if(profile.sourceUrl)core.fromUrl(profile.sourceUrl);
        return profile;
    }
    function fill(profile){
        const p=core.validate(profile);activeId=p.id;catalog=p.catalog;
        $('name').value=p.name;$('source-url').value=p.sourceUrl;$('subject').value=p.metadata.subject;$('group').value=p.metadata.group;$('period').value=p.metadata.period;$('year').value=p.metadata.year;
        document.querySelector('[name="sg-students"][value="'+p.students.mode+'"]').checked=true;$('student-list').value=p.students.names.join('\n');
        for(const type of ['strengths','difficulties']){$(type).value=p.targets[type].join('\n');$('catalog-'+type).value=p.catalog[type].map(item=>item.text).join('\n');}
        $('mode').value=p.matching.mode;$('threshold').value=p.matching.threshold;
        for(const key of ['all','students','strengths','difficulties']) $('selector-'+key).value=p.selectors[key];
        invalidate();dirty=false;renderProfiles();
    }
    const safely=fn=>async event=>{try{await fn(event);}catch(error){status(error.message,true);}};
    $('from-url').addEventListener('click',safely(()=>{const meta=core.fromUrl($('source-url').value);if(!meta.subject&&!meta.group&&!meta.period)throw new Error('El enlace no contiene datos de materia, grupo o período.');for(const key of ['subject','group','period'])if(meta[key])$(key).value=meta[key];if(!$('name').value)$('name').value=[meta.subject,meta.group,meta.period?'Período '+meta.period:''].filter(Boolean).join(' · ');invalidate();status('Materia, grupo y período tomados del enlace.');}));
    const download=(content,type,name)=>{const url=URL.createObjectURL(new Blob([content],{type}));const link=document.createElement('a');link.href=url;link.download=name;link.click();setTimeout(()=>URL.revokeObjectURL(url),1000);};
    const filename=()=>core.normalize($('name').value||'indicadores-sisga').replace(/ /g,'-');
    $('save').addEventListener('click',safely(()=>{if(!$('form').reportValidity())return;const p=collect();p.id=activeId||('perfil-'+Date.now()+'-'+Math.random().toString(36).slice(2,8));const next=[...profiles.filter(item=>item.id!==p.id),p];localStorage.setItem(storageKey,JSON.stringify(next));profiles=next;activeId=p.id;dirty=false;renderProfiles();status('Perfil guardado en este navegador. Exporta el JSON para conservar una copia o usarlo en otro equipo.');}));
    const mayReplace=()=>!dirty||confirm('Hay cambios sin guardar. ¿Quieres reemplazarlos?');
    $('load').addEventListener('click',()=>{const p=profiles.find(p=>p.id===$('profiles').value);if(!p){status('Elige un perfil guardado.',true);return;}if(mayReplace()){fill(p);status('Perfil cargado: '+p.name);}});
    $('new').addEventListener('click',()=>{if(!mayReplace())return;$('form').reset();$('year').value=new Date().getFullYear();activeId='';catalog={strengths:[],difficulties:[]};invalidate();dirty=false;renderProfiles();status('Nueva configuración lista.');});
    $('export').addEventListener('click',safely(()=>{const p=collect();download(JSON.stringify(p,null,2),'application/json',filename()+'.json');dirty=false;status('JSON exportado con materia, estudiantes, indicadores y parámetros.');}));
    $('import').addEventListener('click',()=>$('file').click());
    $('file').addEventListener('change',safely(async()=>{const file=$('file').files[0];$('file').value='';if(!file)return;if(file.size>3*1024*1024)throw new Error('El JSON supera el límite de 3 MB.');let p;try{p=core.validate(JSON.parse(await file.text()));}catch(error){throw new Error('No se importó el archivo. '+error.message);}if(mayReplace()){p.id='';fill(p);dirty=true;status('JSON importado. Revisa la configuración y guarda el perfil para conservarlo aquí.');}}));
    $('preview').addEventListener('click',safely(()=>{
        const p=collect();$('matches').replaceChildren();let total=0;
        for(const type of ['strengths','difficulties']) for(const query of p.targets[type]) {
            total++;const result=core.rank(query,p.catalog[type],p.matching.mode,p.matching.threshold), row=document.createElement('div'),title=document.createElement('strong'),desc=document.createElement('span');row.className='sg-match-row';title.textContent=(type==='strengths'?'Fortaleza: ':'Dificultad: ')+query;
            desc.textContent=result.selected>=0?Math.round(result.candidates[0].score*100)+'% · '+p.catalog[type][result.selected].text:result.status==='ambiguous'?'Hay varias coincidencias parecidas. Elige la correcta en SISGA.':'Sin coincidencia suficiente en el catálogo.';row.append(title,desc);$('matches').append(row);
        }
        if(!total)status('Escribe al menos un indicador para probar la búsqueda.',true);else status('Comparación terminada. El script buscará sobre los indicadores disponibles en SISGA.');
    }));
    $('form').addEventListener('submit',safely(event=>{
        event.preventDefault();const p=collect();if(p.students.mode==='list'&&!p.students.names.length)throw new Error('Escribe los estudiantes de la lista.');
        $('script').value='// Asistente de indicadores SISGA · Guagua\n// Abre la pantalla de indicadores antes de ejecutar.\n('+window.SisgaIndicatorRuntime.toString()+')('+JSON.stringify(p,null,2)+', '+window.SisgaIndicatorCoreFactory.toString()+');';
        $('script-summary').textContent=describe(p)+' · '+(p.students.mode==='all'?'Todos los estudiantes':p.students.names.length+' estudiantes')+' · '+(p.targets.strengths.length+p.targets.difficulties.length)+' indicadores. '+(!p.targets.strengths.length&&!p.targets.difficulties.length?'Sin indicadores solicitados: puedes seleccionar estudiantes o exportar el catálogo actual.':'');
        $('result').hidden=false;status('Script generado. Analiza y revisa la selección en SISGA antes de aplicarla.');$('result').scrollIntoView({behavior:'smooth',block:'start'});
    }));
    $('copy').addEventListener('click',safely(async()=>{try{await navigator.clipboard.writeText($('script').value);status('Script copiado. Pégalo en la consola de SISGA.');}catch(_){$('script').focus();$('script').select();status('Seleccioné el script. Usa Ctrl+C para copiarlo.');}}));
    $('download').addEventListener('click',()=>download($('script').value,'text/javascript',filename()+'.js'));
    $('year').value=new Date().getFullYear();sync();renderProfiles();
})();
