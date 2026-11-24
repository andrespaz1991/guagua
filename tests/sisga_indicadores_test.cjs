// Pruebas aisladas de DOM; no abren SISGA ni modifican datos académicos.
// node tests/sisga_indicadores_test.cjs <directorio con node_modules/jsdom>
const fs=require('node:fs'),path=require('node:path'),vm=require('node:vm'),assert=require('node:assert/strict');
const {createRequire}=require('node:module');
const {JSDOM}=createRequire(path.resolve(process.argv[2]||'.','package.json'))('jsdom');
const root=path.resolve(__dirname,'../sisga'),read=f=>fs.readFileSync(path.join(root,f),'utf8');
const url='https://www.sisga.com.co/Saga/calificacionNumerica/NuevoCalificaciones/Calificar?sAsi=Emprendimiento&sGru=SEPTIMO%201&sPer=3';
const base={schema:'guagua.sisga.indicadores',version:1,name:'Prueba',metadata:{subject:'Emprendimiento',group:'SEPTIMO 1',period:'3',year:2026},students:{mode:'all',names:[]},targets:{strengths:['Identifica oportunidades de negocio'],difficulties:['Presenta dificultad para calcular costos']},matching:{mode:'similar',threshold:75},selectors:{},catalog:{}};
const fixture=`<label><input id="all" type="checkbox">Todos</label><fieldset id="estudiantes"><legend>Estudiantes</legend><label><input id="s1" type="checkbox" value="101">Ana Prueba</label><label><input id="s2" type="checkbox" value="102">Luis Ejemplo</label><label hidden><input id="s3" type="checkbox">Oculto</label><label><input id="s4" type="checkbox" disabled>Retirado</label></fieldset><fieldset id="fortalezas"><legend>Fortalezas</legend><label><input id="f1" type="checkbox" value="f1">Identifica oportunidades de negocio</label><label><input id="f2" type="checkbox" value="f2">Trabaja en equipo</label></fieldset><fieldset id="dificultades"><legend>Dificultades</legend><select id="d1"><option value="">Seleccione</option><option value="d1">Presenta dificultad para calcular costos</option><option value="d2">Necesita mejorar su participación</option></select></fieldset><label><input id="unrelated" type="checkbox">Recibir noticias</label><button id="official-save">Guardar SISGA</button>`;
function setup(html=fixture,profile=base,pageUrl=url){
 const dom=new JSDOM(html,{url:pageUrl,runScripts:'outside-only'}),w=dom.window;
 w.HTMLElement.prototype.getClientRects=function(){return this.closest('[hidden]')?[]:[{width:10,height:10}];};
 w.HTMLElement.prototype.scrollIntoView=()=>{};
 w.eval(read('indicadores-core.js'));w.eval(read('indicadores-runtime.js'));
 const downloads=[];w.URL.createObjectURL=blob=>{downloads.push(blob);return 'blob:mock';};w.URL.revokeObjectURL=()=>{};w.HTMLAnchorElement.prototype.click=()=>{};
 w.SisgaIndicatorRuntime(structuredClone(profile),w.SisgaIndicatorCoreFactory);
 const sh=w.document.getElementById('guagua-sisga-indicadores').shadowRoot;
 return {dom,w,sh,downloads,$:id=>w.document.getElementById(id),scan:()=>sh.getElementById('scan').click(),apply:()=>{sh.getElementById('review').click();sh.getElementById('apply').click();}};
}
async function main(){
 const t=setup(),c=t.w.SisgaIndicatorCore;
 assert.equal(c.similarity('CÉLULA.','celula'),1);
 assert.equal(c.similarity('Comprende el concepto','No comprende el concepto'),0);
 assert.ok(c.similarity('Identifica oportunidades de negocio','Identifica las oportunidades de negocio')>.9);
 assert.equal(c.rank('abc',[{text:'abc'},{text:'abc'}]).status,'ambiguous');
 assert.equal(c.rank('Celula',[{text:'Una célula'}],'exact').selected,-1);
 assert.throws(()=>c.validate({...base,version:2}));assert.throws(()=>c.validate({...base,matching:{mode:'similar',threshold:101}}));
 assert.equal(c.fromUrl(url).group,'SEPTIMO 1');assert.throws(()=>c.fromUrl('https://sisga.com.co.evil.test/'));
 let official=0;t.$('official-save').onclick=()=>official++;
 t.scan();assert.match(t.sh.getElementById('status').textContent,/2 de 2 estudiantes/);assert.equal(t.$('s1').checked,false);assert.equal(t.sh.getElementById('apply').disabled,true);
 t.apply();assert.equal(t.$('all').checked,true);assert.equal(t.$('s1').checked,true);assert.equal(t.$('s2').checked,true);assert.equal(t.$('s3').checked,false);assert.equal(t.$('s4').checked,false);assert.equal(t.$('f1').checked,true);assert.equal(t.$('f2').checked,false);assert.equal(t.$('d1').value,'d1');assert.equal(t.$('unrelated').checked,false);assert.equal(official,0);
 let clicks=0;t.$('s1').addEventListener('click',()=>clicks++);t.scan();t.apply();assert.equal(clicks,0,'Aplicar dos veces no desmarca casillas');
 t.sh.getElementById('export').click();assert.equal(t.downloads.length,1);
 const exportText=await new Promise(resolve=>{const reader=new t.w.FileReader();reader.onload=()=>resolve(reader.result);reader.readAsText(t.downloads[0]);});
 const exported=c.validate(JSON.parse(exportText));assert.equal(exported.targets.strengths[0],base.targets.strengths[0]);assert.equal(exported.students.names.length,2);assert.equal(exported.catalog.strengths[0].value,'f1');assert.equal(exported.sourceUrl,url);
 const subset=setup(fixture,{...base,students:{mode:'list',names:['101']}});subset.$('s2').checked=true;subset.$('all').checked=true;subset.scan();subset.apply();assert.equal(subset.$('all').checked,false);assert.equal(subset.$('s1').checked,true);assert.equal(subset.$('s2').checked,false);
 const missing=setup(fixture,{...base,students:{mode:'list',names:['Ausente']}});missing.scan();assert.match(missing.sh.getElementById('status').textContent,/no encontrado/);assert.equal(missing.sh.getElementById('apply').disabled,true);
 const amb=setup(fixture.replace('Trabaja en equipo','Identifica oportunidades de negocio'));amb.scan();assert.equal(amb.sh.querySelector('select').value,'');amb.sh.getElementById('review').click();assert.equal(amb.sh.getElementById('apply').disabled,true);amb.sh.querySelector('select').value='0';amb.sh.querySelector('select').dispatchEvent(new amb.w.Event('change'));amb.apply();assert.equal(amb.$('f1').checked,true);
 const stale=setup();stale.scan();stale.$('s1').remove();stale.apply();assert.match(stale.sh.getElementById('status').textContent,/cambi/);assert.equal(stale.$('s2').checked,false);
 const wrong=setup(fixture,base,url.replace('Emprendimiento','Matematicas'));wrong.scan();assert.match(wrong.sh.getElementById('status').textContent,/no coincide/);assert.equal(wrong.$('s1').checked,false);
 const conflict=setup(fixture,{...base,targets:{strengths:[],difficulties:['Presenta dificultad para calcular costos','Necesita mejorar su participación']}});conflict.scan();conflict.apply();assert.match(conflict.sh.getElementById('status').textContent,/solo permite un indicador/);assert.equal(conflict.$('s1').checked,false);
 const badSelector=setup(fixture,{...base,selectors:{students:'['}});badSelector.scan();assert.match(badSelector.sh.getElementById('status').textContent,/Selector CSS inválido/);
 const ui=new JSDOM(read('index.html'),{url:'http://localhost/guagua/sisga/#indicadores',runScripts:'outside-only'}),w=ui.window,$=id=>w.document.getElementById('sg-'+id);
 w.HTMLElement.prototype.scrollIntoView=()=>{};w.confirm=()=>true;
 for(const f of ['indicadores-core.js','indicadores-runtime.js','indicadores-ui.js'])w.eval(read(f));
 assert.equal($('notas').hidden,true);assert.equal($('indicadores').hidden,false);
 $('tab-notas').click();assert.equal($('notas').hidden,false);$('tab-notas').dispatchEvent(new w.KeyboardEvent('keydown',{key:'ArrowRight',bubbles:true}));assert.equal($('indicadores').hidden,false);
 $('source-url').value=url;$('from-url').click();assert.equal($('subject').value,'Emprendimiento');assert.equal($('group').value,'SEPTIMO 1');assert.equal($('period').value,'3');
 $('strengths').value='<img src=x onerror=alert(1)> Identifica oportunidades de negocio';$('catalog-strengths').value=$('strengths').value;$('preview').click();assert.equal($('matches').querySelector('img'),null,'Texto importado nunca se interpreta como HTML');
 $('strengths').value=base.targets.strengths[0];$('form').dispatchEvent(new w.Event('submit',{bubbles:true,cancelable:true}));assert.equal($('result').hidden,false);new vm.Script($('script').value);
 const fresh=setup();fresh.w.eval($('script').value);assert.equal(fresh.w.document.querySelectorAll('#guagua-sisga-indicadores').length,1,'Script autónomo y repetible');
 $('save').click();const stored=JSON.parse(w.localStorage.getItem('guagua.sisga.indicadores.profiles.v1'));assert.equal(stored.length,1);assert.equal(stored[0].sourceUrl,url);
 $('strengths').dispatchEvent(new w.Event('input',{bubbles:true}));assert.equal($('result').hidden,true,'No se copia un script desactualizado');
 $('new').click();$('profiles').value=stored[0].id;$('load').click();assert.equal($('strengths').value,base.targets.strengths[0]);
 w.File.prototype.text=async()=>JSON.stringify(exported);Object.defineProperty($('file'),'files',{configurable:true,value:[new w.File(['{}'],'perfil.json',{type:'application/json'})]});$('file').dispatchEvent(new w.Event('change'));await new Promise(r=>setTimeout(r,5));assert.equal($('catalog-strengths').value.split('\n').length,2,'Importación de catálogo y selección');
 for(const el of w.document.querySelectorAll('script:not([src])'))new vm.Script(el.textContent);
 console.log('OK: similitud, JSON, todos/subconjunto, ambigüedad, controles deshabilitados, idempotencia, contexto, cambios de DOM, exclusión de guardado, pestañas y script autónomo.');
 for(const entry of [t,subset,missing,amb,stale,wrong,conflict,badSelector,fresh])entry.dom.window.close();ui.window.close();
}
main().catch(error=>{console.error(error);process.exitCode=1;});
