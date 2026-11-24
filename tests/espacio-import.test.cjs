const assert=require('node:assert/strict');
require('../apps/espacio/ordering.js');require('../apps/espacio/report-data.js');require('../apps/espacio/import-data.js');
const importer=globalThis.EspacioImport;
const source={pages:[{id:'a',title:'Proyecto',parent:null,blocks:[{type:'todo',text:'Padre',checked:true,completedAt:'2026-10-18T05:00:00.000Z'},{type:'todo',text:'Subtarea',checked:false,indent:1,link:'https://example.com/documento'}]},
 {id:'b',title:'Sección',parent:'a',blocks:[{type:'text',text:'<script>alert(1)</script>'}]},
 {id:'c',title:'Otro proyecto',blocks:[]},
 {id:'deleted',title:'Papelera',deleted:true,blocks:[]},{id:'hidden',title:'Hija de papelera',parent:'deleted',blocks:[]}]};
const data=importer.read('\uFEFF'+JSON.stringify(source));
assert.equal(data.roots.length,2);assert.equal(data.skipped,2);
let serial=0;const copies=importer.prepare(data,['a'],[{id:'existing',title:'Existente',blocks:[]}],()=>`new-${++serial}`);
assert.equal(copies.length,2);assert.equal(copies[1].parent,copies[0].id);assert.equal(copies[0].parent,null);
assert.equal(copies[0].blocks[0].checked,true);assert.equal(copies[0].blocks[1].indent,1);assert.equal(copies[0].blocks[1].link,'https://example.com/documento');
assert.equal(copies[1].blocks[0].text,'<script>alert(1)</script>','El texto se conserva como texto, sin ejecutar HTML');
copies[0].blocks[0].text='Editado';assert.equal(data.pages[0].blocks[0].text,'Padre');
const roundtrip=importer.read(JSON.stringify({pages:copies}));assert.equal(roundtrip.roots.length,1);assert.equal(roundtrip.pages.length,2);
assert.throws(()=>importer.read('Not JSON'));assert.throws(()=>importer.read('{"pages":[]}'));
assert.throws(()=>importer.read(JSON.stringify({pages:[source.pages[0],source.pages[0]]})));
assert.throws(()=>importer.read(JSON.stringify({pages:[{id:'x',title:'X',parent:'y',blocks:[]},{id:'y',title:'Y',parent:'x',blocks:[]}]})));
const bad=structuredClone(source);bad.pages[0].blocks[1].link='javascript:alert(1)';assert.throws(()=>importer.read(JSON.stringify(bad)));
assert.throws(()=>importer.prepare(data,[],[],()=> 'new'));
assert.throws(()=>importer.prepare(data,['a'],[],()=> 'same'));
assert.throws(()=>importer.prepare(data,['c'],[{id:'existing'}],()=> 'existing'));
assert.throws(()=>importer.prepare(data,['a'],Array.from({length:499},(_,i)=>({id:`p-${i}`})),()=>`limit-${++serial}`));
assert.throws(()=>importer.prepare(data,['a'],[{id:'huge',text:'x'.repeat(2000000)}],()=>`size-${++serial}`));
const orphan=importer.read(JSON.stringify({pages:[{id:'x',title:'Sección',parent:'missing',blocks:[]}]}));assert.equal(orphan.orphans,1);assert.equal(orphan.roots[0].parent,null);
const polluted=importer.read('{"pages":[{"id":"__proto__","title":"Texto","blocks":[],"__proto__":{"polluted":true}}]}');assert.equal({}.polluted,undefined);assert.equal(polluted.pages[0].id,'__proto__');
console.log('Importación verificada: selección, jerarquía, copias independientes, exportación, enlaces, validación y límites.');
