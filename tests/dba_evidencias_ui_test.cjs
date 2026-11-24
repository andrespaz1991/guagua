/* Prueba de DOM sin automatización del navegador. jsdom solo se usa para QA. */
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const { createRequire } = require('node:module');
const { JSDOM } = createRequire(path.resolve(process.argv[2] || '.', 'package.json'))('jsdom');
const root = path.resolve(__dirname, '../apps/dba');
let html = fs.readFileSync(path.join(root, 'index.php'), 'utf8');
html = html.replace("<?php require __DIR__ . '/views/evidencias.php'; ?>", fs.readFileSync(path.join(root, 'views/evidencias.php'), 'utf8'));
html = html.replace(/<\?php[\s\S]*?\?>/g, '').replace(/<script[^>]*src[^>]*><\/script>/g, '');
const dom = new JSDOM(html, { url: 'http://localhost/guagua/apps/dba/', runScripts: 'outside-only', pretendToBeVisual: true });
const w = dom.window, doc = w.document, $ = id => doc.getElementById(id);
const calls = [], errors = [];
const data = {
    grados:[{id_grado:'6',nombre:'Sexto'}], materias:[{id_materia:'1',nombre_materia:'Ciencias'}],
    estandares:[{id_estandar:'2',nombre_estandar:'Seres vivos',grado:'6',id_materia_oficial:'1',id_periodo:'1'}],
    dbas:[{id_dba:'3',id_estandar:'2',nombre_dba:'Explica la célula',descripcion_dba:'Estructuras celulares',palabras_clave:'célula, membrana'},
          {id_dba:'4',id_estandar:'2',nombre_dba:'Clasifica los seres vivos',descripcion_dba:'Taxonomía',palabras_clave:''}],
    ejes_tematicos:[],
    evidencias:[{id_evidencia_aprendizaje:'9',id_dba:'3',descripcion_evidencia:'<img id="injected" src=x> Explica un modelo celular.',origen:'ia_pendiente',modelo:'gemma-2-2b-it'}]
};
w.HTMLElement.prototype.scrollIntoView = () => {};
w.HTMLDialogElement.prototype.showModal = function(){this.open=true;};
w.HTMLDialogElement.prototype.close = function(){this.open=false;};
w.confirm = () => true;
w.addEventListener('error', e => errors.push(e.error || e.message));
w.fetch = async (url, options={}) => {
    let payload = options.body && typeof options.body==='string' ? JSON.parse(options.body) : null;
    calls.push({url,payload,options});
    let response = {status:'success',message:'Guardado de prueba'};
    if(String(url).includes('get_data')) response = {status:'success',data};
    else if(String(url).includes('models')) response = {status:'success',models:['gemma-2-2b-it','otro-modelo'],server:'LM Studio de prueba'};
    else if(payload?.action==='generate') response={status:'success',proposal:{evidencias:['El estudiante compara dos células mediante un esquema rotulado.'],palabras_clave:'célula, comparación'}};
    else if(options.body instanceof w.FormData) response={status:'success',rows:[{id_dba:4,nombre_dba:'Clasifica',evidencia:'Compara organismos.',palabras_clave:'taxonomía'}],errors:[],empty:1};
    return {ok:true,status:200,json:async()=>response};
};
const tick = () => new Promise(resolve=>setTimeout(resolve,10));
const change = (id,value) => {$(id).value=value;$(id).dispatchEvent(new w.Event('change',{bubbles:true}));};
const click = selector => {const el=doc.querySelector(selector);assert.ok(el,selector);el.click();};
for(const file of ['app.js','evidencias.js']) w.eval(fs.readFileSync(path.join(root,'assets/js',file),'utf8'));
(async()=>{
    await tick();
    assert.equal(errors.length,0,'Sin excepciones al iniciar');
    assert.equal(doc.querySelectorAll('.ev-card').length,2,'Biblioteca cargada');
    click('#tab-evidencias');assert.equal($('evidencias-view').classList.contains('hidden'),false);
    assert.equal($('editor-view').classList.contains('hidden'),true);
    $('tab-evidencias').dispatchEvent(new w.KeyboardEvent('keydown',{key:'ArrowRight',bubbles:true}));
    assert.equal($('importar-view').classList.contains('hidden'),false,'Navegación de pestañas por teclado');
    click('#tab-evidencias');
    assert.equal(doc.querySelector('#injected'),null,'La evidencia no inyecta HTML');
    assert.match($('ev-library').textContent,/<img id="injected"/,'Se muestra el texto de forma segura');
    change('ev-filter','missing');assert.equal(doc.querySelectorAll('.ev-card').length,1);
    assert.match($('ev-library').textContent,/DBA #4/);
    change('ev-filter','pending');assert.equal(doc.querySelectorAll('.ev-card').length,1);assert.match($('ev-library').textContent,/por revisar/);
    change('ev-filter','');
    click('[data-ev-select="4"]');assert.match($('ev-selection-count').textContent,/1 seleccionados/);
    click('#ev-generate-selection');await tick();assert.equal($('ev-ai-panel').classList.contains('hidden'),false);
    assert.equal($('ev-model').options.length,2,'Modelos consultados');
    click('#ev-generate');await tick();await tick();
    assert.equal(doc.querySelectorAll('[data-proposal-text]').length,1,'Propuesta generada');
    const generation=calls.find(c=>c.payload?.action==='generate');assert.equal(String(generation.payload.id_dba),'4','Solo se genera el DBA seleccionado');
    const textarea=doc.querySelector('[data-proposal-text]');textarea.value='Clasifica cinco organismos y justifica sus criterios.';textarea.dispatchEvent(new w.Event('input',{bubbles:true}));
    click('#ev-ai-save');await tick();
    const save=calls.find(c=>c.payload?.action==='save_batch');assert.equal(save.payload.rows[0].evidencia,textarea.value);assert.equal(save.payload.rows[0].modelo,'gemma-2-2b-it');assert.equal($('ev-ai-save').disabled,true);
    click('[data-ev-mode="library"]');click('[data-ev-edit="9"]');assert.equal($('ev-dialog').open,true);assert.equal($('ev-dialog-title').textContent,'Revisar propuesta de IA');click('#ev-dialog-cancel');
    click('[data-ev-keywords="3"]');assert.equal($('input-palabras-dba').value,'célula, membrana');
    $('input-palabras-dba').value='célula, organelo';click('#btn-save-dba');await tick();
    assert.equal(calls.find(c=>c.url==='api/crud.php').payload.palabras_clave,'célula, organelo');
    assert.equal($('modal-dba').classList.contains('hidden'),true);
    click('[data-ev-mode="import"]');
    Object.defineProperty($('ev-file'),'files',{value:[new w.File(['id_dba;evidencia\n4;Compara organismos'],'plantilla.csv',{type:'text/csv'})],configurable:true});
    $('ev-file').dispatchEvent(new w.Event('change'));click('#ev-import-preview');await tick();assert.equal($('ev-import-save').disabled,false);assert.match($('ev-import-summary').textContent,/1 evidencias válidas/);click('#ev-import-save');await tick();assert.equal($('ev-import-save').disabled,true);
    click('#tab-editor');change('select-grado','6');change('select-materia','1');
    assert.match($('periods-container').textContent,/Evidencias de aprendizaje/);assert.match($('periods-container').textContent,/célula/);
    assert.equal(errors.length,0,'Sin errores en los flujos de interfaz');
    console.log('OK: flujos DOM; pestañas, filtros, selección, escape HTML, modelos, generación, edición, guardado, importación y evidencias en el editor.');
    dom.window.close();
})().catch(error=>{console.error(error);dom.window.close();process.exitCode=1;});
